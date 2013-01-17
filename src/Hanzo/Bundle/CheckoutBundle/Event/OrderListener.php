<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\CheckoutBundle\Event;

use Hanzo\Core\Tools;
use Hanzo\Model\Orders;
use Hanzo\Bundle\ServiceBundle\Services\AxService;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class OrderListener
{
    protected $session;
    protected $ax;
    protected $cookie_path;

    /**
     * OrderListener constructor
     *
     * @param Session   $session Session object
     * @param AxService $ax      AxService object
     */
    public function __construct(Session $session, AxService $ax)
    {
        $this->session = $session;
        $this->ax = $ax;
    }


    /**
     * onEditStart event handeling
     *
     * @param  FilterOrderEvent $event [description]
     */
    public function onEditStart(FilterOrderEvent $event)
    {
        $order = $event->getOrder();

        // if we are unable to lock the order, we should not allow edits to start.
        if (!$this->ax->lockUnlockSalesOrder($order, true)) {
            $event->setStatus(false, 'unable.to.lock.order');
            return;
        }

        // first we create the edit version.
        $order->createNewVersion();

        $order->setSessionId(session_id());
        $order->setState( Orders::STATE_BUILDING ); // Old order state is probably payment ok
        $order->clearFees();
        $order->clearPaymentAttributes();
        $order->setInEdit(true);
        $order->setBillingMethod(null);
        $order->setPaymentGatewayId(Tools::getPaymentGatewayId());
        $order->setUpdatedAt(time());
        $order->save();

        $this->session->set('in_edit', true);
        $this->session->set('order_id', $order->getId());
        $this->session->save();

        // note, cookies must be set after session stuff is done
        $this->setEditCookie(true, substr($order->getAttributes()->global->domain_key, 0, 5));

        $event->setStatus(true);
    }


    /**
     * onEditCancel event handeling
     *
     * @param  FilterOrderEvent $event
     */
    public function onEditCancel(FilterOrderEvent $event)
    {
        $order = $event->getOrder();
        // reset order object
        $order->toPreviousVersion();

        // unset session vars.
        $this->session->remove('in_edit');
        $this->session->remove('order_id');
        $this->session->save();
        $this->session->migrate();

        // note, cookies must be set after session stuff is done
        $this->setEditCookie(false);

        $this->ax->lockUnlockSalesOrder($order, false);
    }


    /**
     * onEditDone event handler
     *
     * @param  FilterOrderEvent $event
     */
    public function onEditDone(FilterOrderEvent $event)
    {
        $order = $event->getOrder();
        $order->setSessionId($order->getId());

        // unset session vars.
        $this->session->remove('in_edit');
        $this->session->remove('order_id');

        // note, cookies must be set after session stuff is done
        $this->setEditCookie(false);

        $this->ax->lockUnlockSalesOrder($order, false);
    }


    /**
     * cookie helper - it could be stored in a session, but ....
     *
     * @param boolean $set    to set or delete
     * @param mixed   $domain domain string
     */
    protected function setEditCookie($set = true, $domain = null)
    {
        if ((false == $set) && empty($_COOKIE['__ice'])) {
            return;
        }

        $content  = $set ? $domain : '';
        $notice   = $set ? Tools::getInEditWarning(true) : '';
        $lifetime = $set ? 0 : -3600;

        Tools::setCookie('__ice', $content, $lifetime, true);
        Tools::setCookie('__ice_n', $notice, $lifetime, false);
    }
}
