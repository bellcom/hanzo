<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\AccountBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Hanzo\Core\CoreController;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\CustomersPeer;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;

use \Criteria;
use Exception;

use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;

class HistoryController extends CoreController
{
    public function indexAction()
    {
        return $this->render('AccountBundle:History:index.html.twig', [
            'page_type' => 'account-history',
        ]);
    }


    public function viewAction(Request $request, $order_id)
    {
        $order = OrdersQuery::create()
            ->joinWithOrdersLines()
            ->findPk($order_id)
        ;

        if (!$order instanceof Orders) {
           return $this->redirect($request->headers->get('referer'));
        }

        $order_lines = $order->getOrdersLiness();

        $addresses = [];
        foreach ($order->toArray() as $key => $value) {
            if (substr($key, 0, 7) == 'Billing') {
                $key = strtolower(substr($key, 7));
                $addresses['billing'][$key] = $value;
                $addresses['billing']['type'] = 'billing';
            } elseif (substr($key, 0, 8) == 'Delivery') {
                $key = strtolower(substr($key, 8));
                $addresses['delivery'][$key] = $value;
                $addresses['delivery']['type'] = 'delivery';
            }
        }

        return $this->render('AccountBundle:History:view.html.twig', [
            'page_type' => 'account-history-view',
            'order' => $order,
            'order_lines' => $order_lines,
            'addresses' => $addresses,
        ]);
    }


    public function editAction($order_id)
    {
        $order = OrdersQuery::create()
            ->filterByCustomersId(CustomersPeer::getCurrent()->getId())
            ->findOneById($order_id)
        ;

        if (!$order instanceof Orders || $order->isNew()) {
            return $this->redirect($this->generateUrl('_account'));
        }

        $event = new FilterOrderEvent($order);
        $this->get('event_dispatcher')->dispatch('order.edit.start', $event);

        $status = $event->getStatus();

        if (false === $status->code) {
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans($status->message, ['%order_id%' => $order_id], 'account'));
            return $this->redirect($this->generateUrl('_account'));
        }

        // update/set basket cookie
        Tools::setCookie('basket', '('.$order->getTotalQuantity(true).') '.Tools::moneyFormat($order->getTotalPrice(true)), 0, false);
        return $this->redirect($this->generateUrl('basket_view'));
    }


    public function blockAction($limit = 6, $link = true, $route = false, $pager = 1)
    {
        $hanzo = Hanzo::getInstance();
        $domainKey  = $hanzo->get('core.domain_key');
        $customer = CustomersPeer::getCurrent();

        if (empty($route)) {
            $route = $this->get('request')->get('_route');
        }

        $router = $this->get('router');

        $result = OrdersQuery::create()
            ->filterByState(Orders::STATE_PENDING, Criteria::GREATER_EQUAL)
            ->_or()
            ->filterByInEdit(true)
            ->orderByCreatedAt(Criteria::DESC)
            ->limit($limit)
            ->filterByCustomersId($customer->getId())
            ->paginate($pager, $limit)
        ;

        $paginate = false;

        if (!$link) {
            $pages = [];
            if ($result->haveToPaginate()) {
                foreach ($result->getLinks(20) as $page) {
                    $pages[$page] = $router->generate($route, ['pager' => $page], true);
                }

                $paginate = [
                    'next' => ($result->getNextPage() == $pager ? '' : $router->generate($route, ['pager' => $result->getNextPage()], true)),
                    'prew' => ($result->getPreviousPage() == $pager ? '' : $router->generate($route, ['pager' => $result->getPreviousPage()], true)),
                    'pages' => $pages,
                    'index' => $pager
                ];
            }
        }

        // track 'n trace integration - the url is only available if both actor_id and installation_id is set for the country.
        $trackntrace_url = '';
        if ($this->container->hasParameter('account.consignor.trackntrace_url')) {
            $trackntrace_url = $this->container->getParameter('account.consignor.trackntrace_url');
        }

        $orders = [];
        foreach ($result as $record) {
            $folder = $this->mapLanguageToPdfDir($record->getLanguagesId()).'_'.$record->getCreatedAt('Y');

            $attachments = [];
            foreach ($record->getAttachments() as $key => $attachment) {
                $attachments[] = $hanzo->get('core.cdn') . 'pdf.php?' . http_build_query([
                    'folder' => $folder,
                    'file'   => $attachment,
                    'key'    => $this->get('session')->getId()
                ]);
            }

            $track_n_trace = '';
            $return_label_url = '';

            if (Orders::STATE_SHIPPED === $record->getState()) {
                if ($trackntrace_url) {
                    $track_n_trace = strtr($trackntrace_url, [':order_id:' => $record->getId()]);
                }

                if ('DE' == substr($domainKey, -2)) {
                    $return_label_url = 'https://globalmaileurope.dhl.com/web/portal-europe/generate_label?location=1705543140';
                } else {
                    $return_label_url = $this->getReturnLabelUrl($record->getId());
                }
            }

            $orders[] = [
                'id'               => $record->getId(),
                'in_edit'          => $record->getInEdit(),
                'can_modify'       => (($record->getState() <= Orders::STATE_PENDING) ? true : false),
                'status'           => str_replace('-', 'neg.', $record->getState()),
                'created_at'       => $record->getCreatedAt(),
                'total'            => $record->getTotalPrice(),
                'attachments'      => $attachments,
                'track_n_trace'    => $track_n_trace,
                'return_label_url' => $return_label_url,
            ];
        }

        return $this->render('AccountBundle:History:block.html.twig', [
            'page_type' => 'account-history',
            'orders'    => (count($orders) ? $orders : null),
            'link'      => $link,
            'paginate'  => $paginate
        ]);
    }


    /**
     * delete an order, but only if in a allowed state
     *
     * @param  int $order_id
     * @return Response
     */
    public function deleteAction($order_id)
    {
        $order = OrdersQuery::create()
            ->filterByCustomersId(CustomersPeer::getCurrent()->getId())
            ->filterByState(Orders::STATE_PENDING, Criteria::LESS_EQUAL)
            ->findOneById($order_id)
        ;

        if ((!$order instanceof Orders) || $order->getInEdit()) {
            $this->get('session')->getFlashBag()->add('notice', 'unable.to.delete.order.in.current.state');
        } else {
            $msg = $this->get('translator')->trans('order.deleted', ['%id%' => $order_id]);
            $bcc = Tools::getBccEmailAddress('order', $order);

            // nuke order
            try {
                $firstName = $order->getFirstName();
                $lastName  = $order->getLastName();
                $id        = $order->getId();
                $email     = $order->getEmail();
                $amount    = Tools::moneyFormat($order->getTotalPrice());

                $this->container->get('hanzo.core.orders_service')->deleteOrder($order);

                // send delete notification
                $mailer = $this->get('mail_manager');
                $mailer->setMessage('order.deleted', [
                    'amount'   => $amount,
                    'name'     => $firstName,
                    'order_id' => $id,
                    'date'     => date('d-m-Y'),
                    'time'     => date('H:i'),
                ]);

                $mailer->setBcc($bcc);
                $mailer->setTo($email, $firstName.' '.$lastName);
                $mailer->send();
            } catch (Exception $e) {
                $msg = $e->getMessage();
            }

            $this->get('session')->getFlashBag()->add('notice', $msg);
        }

        return $this->redirect($this->generateUrl('_account'));
    }

    /**
     * getReturnLabelUrl
     * @param int $orderId
     *
     * @return mixed
     * @author Henrik Farre <hf@bellcom.dk>
     */
    public function getReturnLabelUrl($orderId)
    {
        $router                    = $this->get('router');
        $url                       = '';
        static $return_label_route = NULL;
        $hanzo                     = Hanzo::getInstance();
        $domainKey                 = str_replace('Sales', '', $hanzo->get('core.domain_key'));

        // Only load service once as function is called in loop
        if (is_null($return_label_route)) {
            if ($submit_shipment = $this->container->get('consignor.service.submit_shipment')) {
                $return_label_route = $submit_shipment->getRoute();
            }
            else {
                $return_label_route = false;
            }
        }

        if ($return_label_route) {
            $url = $router->generate($return_label_route, ['id' => $orderId]);
        }

        // Use direct link for some domains, scrumdo:#917
        if (empty($url)) {
            switch ($domainKey)
            {
              case 'AT':
              case 'NL':
                $url = 'https://globalmaileurope.dhl.com/web/portal-europe/generate_label?location=1705543140';
                break;
            }
        }

        return $url;
    }
}
