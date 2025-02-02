<?php /* vim: set sw=4: */

/**
 * Username.: un-facilitator_api1.bellcom.dk
 * Password.: 1364979347
 * Signature: ABXF9ETaMLWYCEmZokD.mXSrk88hA63P3kKKUzqMvoUft615M4awyDCb
 */

namespace Hanzo\Bundle\PaymentBundle\Controller;

use Exception;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;
use Hanzo\Bundle\PaymentBundle\Methods\PayPal\PayPalApi;
use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;
use Propel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PayPalController
 *
 * @package Hanzo\Bundle\PaymentBundle
 */
class PayPalController extends CoreController
{
    /**
     * callbackAction
     * post params [
     *     status
     *     error_message
     *     merchant_error_message
     *     shop_orderid
     *     transaction_id
     *     type
     *     payment_status
     *     masked_credit_card
     *     blacklist_token
     *     credit_card_token
     *     nature
     *     require_capture
     *     xml
     *     checksum
     * ]
     *
     * @param Request $request
     * @param string  $status
     *
     * @throws \PropelException
     * @return Response
     */
    public function callbackAction(Request $request, $status = 'failed')
    {
        $order = OrdersQuery::create()
            ->findOneByPaymentGatewayId(
                $request->query->get('payment_gateway_id'),
                Propel::getConnection(null, Propel::CONNECTION_WRITE));

        /**
         * used in google analytics to generate stats on order order edits.
         */
        $queryParameters = [];
        if ($order->getInEdit()) {
            $queryParameters = ['is-edit' => 1];
        }

        $flashBag = $this->get('session')->getFlashBag();

        if ($order instanceof Orders) {
            $order->reload(true);
            $api = $this->get('payment.paypalapi');

            try {
                if ($response = $api->verifyCallback($request, $order)) {
                    $api->processPayment($response, $order);
                }

                $api->updateOrderSuccess($request, $order);

                /**
                 * Listeners includes:
                 *  - stopping order edit flows
                 *  - cansellation of "old" payments (for edits)
                 *  - adding the order to beanstalk for processing
                 *  - ..
                 */
                $this->get('event_dispatcher')->dispatch('order.payment.collected', new FilterOrderEvent($order));
                $status = 'ok';
            } catch (Exception $e) {
                $status = 'failed';
                $message = $e->getMessage();
                Tools::log($e->getMessage());
            }

            if ('ok' === $status) {
                return $this->redirect($this->generateUrl('_checkout_success', $queryParameters));
            }

            $api->updateOrderFailed($request, $order);

            $flashBag->add('notice', $this->get('translator')->trans($message, [], 'paypal'));
        } else {
            $flashBag->add('notice', $this->get('translator')->trans('order.not.found', [], 'paypal'));
        }

        return $this->redirect($this->generateUrl('_checkout'));
    }

    /**
     * @param Request $request
     */
    public function processAction(Request $request)
    {
    }

    /**
     * cancelAction
     *
     * @return Response
     */
    public function cancelAction()
    {
        return new Response('Ok', 200, ['Content-Type' => 'text/plain']);
    }
}
