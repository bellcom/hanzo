<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\AccountBundle\Controller;

use Hanzo\Core\CoreController;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\CustomersPeer;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;

use \Criteria;

use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;

class HistoryController extends CoreController
{
    public function indexAction()
    {
        return $this->render('AccountBundle:History:index.html.twig', array(
            'page_type' => 'account-history',
        ));
    }

    public function viewAction($order_id)
    {
        $order = OrdersQuery::create()
            ->joinWithOrdersLines()
            ->findPk($order_id)
        ;
        $order_lines = $order->getOrdersLiness();
        $order_attributes = $order->getOrdersAttributess();

        $addresses = array();
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

        return $this->render('AccountBundle:History:view.html.twig', array(
            'page_type' => 'account-history-view',
            'order' => $order,
            'order_lines' => $order_lines,
            'addresses' => $addresses,
        ));
    }


    public function editAction($order_id)
    {
        $order = OrdersQuery::create()
            ->filterByCustomersId(CustomersPeer::getCurrent()->getId())
            ->findOneById($order_id)
        ;
        $this->get('event_dispatcher')->dispatch('order.edit.start', new FilterOrderEvent($order));
        return $this->redirect($this->generateUrl('basket_view'));
    }


    public function blockAction($limit = 6, $link = TRUE, $route = FALSE)
    {
        $hanzo = Hanzo::getInstance();
        $customer = CustomersPeer::getCurrent();

        if (empty($route)) {
            $route = $this->get('request')->get('_route');
        }

        $router = $this->get('router');
        $pager = $this->get('request')->get('pager', 1);


        $offset = 6;
        if (($limit > 6) || ($limit == 0)) {
            $offset = 20;
        }

        $result = OrdersQuery::create()
            ->filterByState(Orders::STATE_PENDING, Criteria::GREATER_EQUAL)
            ->orderByCreatedAt(Criteria::DESC)
            ->limit($limit)
            ->filterByCustomersId($customer->getId())
            ->paginate($pager, $offset)
        ;

        $paginate = FALSE;

        if (!$link) {
            $pages = array();
            if ($result->haveToPaginate()) {
                foreach ($result->getLinks(20) as $page) {
                    $pages[$page] = $router->generate($route, array('pager' => $page), TRUE);
                }

                $paginate = array(
                    'next' => ($result->getNextPage() == $pager ? '' : $router->generate($route, array('pager' => $result->getNextPage()), TRUE)),
                    'prew' => ($result->getPreviousPage() == $pager ? '' : $router->generate($route, array('pager' => $result->getPreviousPage()), TRUE)),
                    'pages' => $pages,
                    'index' => $pager
                );
            }
        }

        $orders = array();
        foreach ($result as $record) {
            $attachments = array();
            foreach ($record->getAttachments() as $key => $attachment) {
                $attachments[] = $hanzo->get('core.cdn') . 'dl.php?' . http_build_query(array(
                    'file' => $attachment,
                    'key' => $this->get('session')->getId()
                ));
            }

            $orders[] = array(
                'id' => $record->getId(),
                'status' => str_replace('-', 'neg.', $record->getState()),
                'created_at' => $record->getCreatedAt(),
                'total' => $record->getTotalPrice(),
                'attachments' => $attachments,
            );
        }

        return $this->render('AccountBundle:History:block.html.twig', array(
            'page_type' => 'account-history',
            'orders' => (count($orders) ? $orders : NULL),
            'link' => $link,
            'paginate' => $paginate
        ));
    }
}
