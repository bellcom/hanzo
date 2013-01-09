<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\PaymentBundle\Controller;

use Exception;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hanzo\Core\Hanzo;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersPeer;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;
use Hanzo\Bundle\PaymentBundle\Methods\PayByBill\PayByBillApi;

use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;

class PayByBillController extends CoreController
{
    /**
     * callbackAction
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function callbackAction()
    {
        $api = $this->get('payment.paybybillapi');
        $request = $this->get('request');
        $order = OrdersPeer::getCurrent();

        if ( !($order instanceof Orders) ) {
            throw new Exception( 'PayByBill callback found no valid order to proccess.' );
        }

        try {
            $api->updateOrderSuccess( $request, $order );
            $this->get('event_dispatcher')->dispatch('order.payment.collected', new FilterOrderEvent($order));
        } catch (Exception $e) {
            Tools::log($e->getMessage());
        }

        return $this->redirect($this->generateUrl('_checkout_success'));
    }


    /**
     * blockAction
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function blockAction()
    {
        $api = $this->get('payment.paybybillapi');

        if (!$api->isActive()) {
            return new Response( '', 200, array('Content-Type' => 'text/html'));
        }

        return $this->render('PaymentBundle:PayByBill:block.html.twig');
    }
}
