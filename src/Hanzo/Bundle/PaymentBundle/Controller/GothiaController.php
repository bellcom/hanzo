<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\PaymentBundle\Controller;

use Propel;
use Exception;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Timer;
use Hanzo\Core\Tools;

use Hanzo\Model\Orders;
use Hanzo\Model\OrdersPeer;
use Hanzo\Model\Customers;
use Hanzo\Model\CustomersPeer;
use Hanzo\Model\GothiaAccounts;
use Hanzo\Core\CoreController;
use Hanzo\Bundle\PaymentBundle\Methods\Gothia\GothiaApi;
use Hanzo\Bundle\PaymentBundle\Methods\Gothia\GothiaApiCallException;

use Hanzo\Bundle\CheckoutBundle\Event\FilterOrderEvent;

class GothiaController extends CoreController
{
    /**
     * blockAction
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function blockAction()
    {
        $api = $this->get('payment.gothiaapi');

        if (!$api->isActive()) {
            return new Response( '', 200, array('Content-Type' => 'text/html'));
        }

        return $this->render('PaymentBundle:Gothia:block.html.twig',array());
    }

    /**
     * paymentAction
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function paymentAction()
    {
        $order = OrdersPeer::getCurrent();

        if ($order->isNew()) {
            return $this->redirect($this->generateUrl('_checkout'));
        }

        // hf@bellcom.dk, 18-sep-2012: maybe a fix for orders contaning valid dibs info and then is overriden with gothia billingmethod -->>
        if ($order->getState() > Orders::STATE_PRE_PAYMENT) {
            $this->get('session')->setFlash('notice', 'order.state_pre_payment.locked');
            return $this->redirect($this->generateUrl('basket_view'));
        }
        // <<-- hf@bellcom.dk, 18-sep-2012: maybe a fix for orders contaning valid dibs info and then is overriden with gothia billingmethod
        //
        $gothiaAccount = $order
            ->getCustomers(Propel::getConnection(null, Propel::CONNECTION_WRITE))
            ->getGothiaAccounts(Propel::getConnection(null, Propel::CONNECTION_WRITE))
        ;

        // No gothia account has been created and associated with the customer, so lets do that
        $step = 2;
        if (is_null($gothiaAccount)) {
            $step = 1;
            $gothiaAccount = new GothiaAccounts();
        }

        // Build the form where the customer can enter his/hers information
        $form = $this->createFormBuilder( $gothiaAccount )
            ->add( 'social_security_num', 'text', array(
                'label' => 'social_security_num',
                'required' => true,
                'translation_domain' => 'gothia' ) )
            ->getForm();

        return $this->render('PaymentBundle:Gothia:payment.html.twig',array('page_type' => 'gothia','step' => $step, 'form' => $form->createView()));
    }

    /**
     * checkCustomerAction
     * The form has been submitted via ajax -> process it
     * @param Request $request
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function checkCustomerAction(Request $request)
    {
        $form       = $request->request->get('form');
        $SSN        = $form['social_security_num'];
        $translator = $this->get('translator');

        $hanzo = Hanzo::getInstance();
        $domainKey = $hanzo->get('core.domain_key');

        // Use form validation?

        if ('FI' == str_replace('Sales', '', $domainKey)) {
            /**
             * Finland uses social security numbers with dash DDMMYY-CCCC
             */
            if(!strpos($SSN, '-')){ // FI has to have dash. If it isnt there, add it. Could be made better?
                $SSN = substr($SSN, 0, 6).'-'.substr($SSN, 6);
            }

            if (strlen($SSN) < 11) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_short', array(), 'gothia'),
                ));
            }

            if (strlen($SSN) > 11) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_long', array(), 'gothia')
                ));
            }
        } elseif ('NO' == str_replace('Sales', '', $domainKey)) {
            /**
             * Norway uses social security numbers without dash but with 5 digits DDMMYY-CCCCC
             */
            $SSN = strtr( $SSN, array( '-' => '', ' ' => '' ) );

            if (strlen($SSN) < 11) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_short', array(), 'gothia'),
                ));
            }

            if (strlen($SSN) > 11) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_long', array(), 'gothia')
                ));
            }
        } elseif (('DK' == str_replace('Sales', '', $domainKey)) || ('NL' == str_replace('Sales', '', $domainKey))) {
            /**
             * Denmark uses birthdate DDMMYYYY
             * Netherland uses birthdate DDMMYYYY
             */

            $SSN = strtr( $SSN, array( '-' => '', ' ' => '' ) );

            if (strlen($SSN) < 8) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_short', array(), 'gothia'),
                ));
            }

            if (strlen($SSN) > 8) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_long', array(), 'gothia')
                ));
            }
        } else {
            /**
             * All others uses social security number without dash DDMMYYCCCC
             */

            $SSN = strtr( $SSN, array( '-' => '', ' ' => '' ) );

            //Every other domain
            if (!is_numeric($SSN)) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.not_numeric', array(), 'gothia'),
                ));
            }
            if (strlen($SSN) < 10) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_short', array(), 'gothia'),
                ));
            }

            if (strlen($SSN) > 10) {
                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.ssn.to_long', array(), 'gothia')
                ));
            }
        }

        $order         = OrdersPeer::getCurrent();
        $customer      = $order->getCustomers(Propel::getConnection(null, Propel::CONNECTION_WRITE));

        if (!$customer instanceof Customers) {
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.checkcustomer.failed', ['%msg%' => 'no customer'], 'gothia'),
            ));
        }

        $gothiaAccount = $customer->getGothiaAccounts(Propel::getConnection(null, Propel::CONNECTION_WRITE));
        if (is_null($gothiaAccount)) {
            $gothiaAccount = new GothiaAccounts();
        }

        $gothiaAccount->setDistributionBy( 'NotSet' )
            ->setDistributionType( 'NotSet' )
            ->setSocialSecurityNum( $SSN );

        $customer->setGothiaAccounts( $gothiaAccount );

        $timer = new Timer('gothia', true);

        try
        {
            // Validate information @ gothia
            $api = $this->get('payment.gothiaapi');
            $response = $api->call()->checkCustomer( $customer );
        }
        catch( GothiaApiCallException $g )
        {
            if (Tools::isBellcomRequest()) {
                Tools::debug('Check Customer Failed', __METHOD__, array('Message' => $e->getMessage()));
            }
            $timer->logOne('checkCustomer call failed orderId #'.$order->getId());
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.checkcustomer.failed', array('%msg%' => $g->getMessage()), 'gothia'),
            ));
        }

        $timer->logOne('checkCustomer call, orderId #'.$order->getId());

        if ( !$response->isError() )
        {
            $gothiaAccount = $customer->getGothiaAccounts(Propel::getConnection(null, Propel::CONNECTION_WRITE));
            $gothiaAccount->setDistributionBy( $response->data['DistributionBy'] )
                ->setDistributionType( $response->data['DistributionType'] );

            $customer->setGothiaAccounts( $gothiaAccount );
            $customer->save();

            return $this->json_response(array(
                'status' => true,
                'message' => '',
            ));
        }
        else
        {
            if ( $response->data['PurchaseStop'] === 'true')
            {
                #Tools::debug( 'PurchaseStop', __METHOD__, array( 'Transaction id' => $response->transactionId ));

                return $this->json_response(array(
                    'status' => FALSE,
                    'message' => $translator->trans('json.checkcustomer.purchasestop', array(), 'gothia'),
                ));
            }

            #Tools::debug( 'Check customer error', __METHOD__, array( 'Transaction id' => $response->transactionId, 'Data' => $response->data ));

            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.checkcustomer.error', array(), 'gothia'),
            ));
        }
    }

    /**
     * confirmAction
     * @param Request $request
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function confirmAction(Request $request)
    {
        $order      = OrdersPeer::getCurrent();
        $customer   = $order->getCustomers(Propel::getConnection(null, Propel::CONNECTION_WRITE));
        $api        = $this->get('payment.gothiaapi');
        $translator = $this->get('translator');

        if ( $order->getState() > Orders::STATE_PRE_PAYMENT )
        {
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.order.state_pre_payment.locked', array(), 'gothia'),
            ));
        }

        // Handle reservations in Gothia when editing the order
        // A customer can max reserve 7.000 SEK currently, so if they edit an order to 3.500+ SEK
        // it will fail because we have not removed the old reservation first, this should fix it

        if ( $order->getInEdit() )
        {
            $currentVersion = $order->getVersionId();

            // If the version number is less than 2 there is no previous version
            if ( !( $currentVersion < 2 ) )
            {
                $oldOrderVersion = ( $currentVersion - 1);
                $oldOrder = $order->getOrderAtVersion($oldOrderVersion);

                $paytype = strtolower( $oldOrder->getBillingMethod() );

                // The new order amount is different from the old order amount
                // We will remove the old reservation, and create a new one
                // but only if the old paytype was gothia
                if ( $paytype == 'gothia' && $order->getTotalPrice() != $oldOrder->getTotalPrice() )
                {
                    $timer = new Timer('gothia', true);
                    try
                    {
                        $response = $api->call()->cancelReservation( $customer, $oldOrder );
                    }
                    catch( GothiaApiCallException $g )
                    {
                        $timer->logOne('cancelReservation call failed, orderId #'.$oldOrder->getId());
                        Tools::debug('Cancel reservation failed', __METHOD__, array('Message' => $e->getMessage()));
                        return $this->json_response(array(
                            'status' => FALSE,
                            'message' => $translator->trans('json.cancelreservation.failed', array('%msg%' => $g->getMessage()), 'gothia'),
                        ));
                    }

                    $timer->logOne('cancelReservation, orderId #'.$oldOrder->getId());

                    if ( $response->isError() )
                    {
                        return $this->json_response(array(
                            'status' => FALSE,
                            'message' => $translator->trans('json.cancelreservation.error', array(), 'gothia'),
                        ));
                    }
                }
            }
        }

        try
        {
            $timer = new Timer('gothia', true);
            $response = $api->call()->placeReservation( $customer, $order );
            $timer->logOne('placeReservation orderId #'.$order->getId());
        }
        catch( GothiaApiCallException $g )
        {
            if (Tools::isBellcomRequest()) {
                Tools::debug('Place Reservation Exception', __METHOD__, array('Message' => $g->getMessage()));
            }
            $api->updateOrderFailed( $request, $order );
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.placereservation.failed', array('%msg%' => $g->getMessage()), 'gothia'),
            ));
        }

        if ( $response->isError() )
        {
            Tools::debug( 'Confirm action error', __METHOD__, array( 'Transaction id' => $response->transactionId, 'Data' => $response->data ));

            $api->updateOrderFailed( $request, $order );
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.placereservation.error', array(), 'gothia'),
            ));
        }

        // NICETO: priority: low, refacture gothia to look more like DibsController

        try
        {
            $api->updateOrderSuccess( $request, $order );
            $this->get('event_dispatcher')->dispatch('order.payment.collected', new FilterOrderEvent($order));

            return $this->json_response(array(
                'status' => TRUE,
                'message' => '',
            ));
        }
        catch (Exception $e)
        {
            if (Tools::isBellcomRequest()) {
                Tools::debug('Place Reservation Exception', __METHOD__, array('Message' => $g->getMessage()));
            }
            #Tools::debug( $e->getMessage(), __METHOD__);
            $api->updateOrderFailed( $request, $order );

            Tools::debug('Place reservation failed', __METHOD__, array('Message' => $e->getMessage()));
            return $this->json_response(array(
                'status' => FALSE,
                'message' => $translator->trans('json.placereservation.error', array(), 'gothia'),
            ));
        }
    }

    /**
     * testAction
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function testAction()
    {
        $customer  = CustomersPeer::getCurrent();

        $api = $this->get('payment.gothiaapi');
        $response = $api->call()->checkCustomer( $customer );

        error_log(__LINE__.':'.__FILE__.' '.print_r($response,1)); // hf@bellcom.dk debugging

        return new Response( 'Test completed', 200, array('Content-Type' => 'text/html'));
    }

    /**
     * processAction
     *
     * @return object Response
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function processAction()
    {
        $order = OrdersPeer::getCurrent();

        if ( $order->getState() < Orders::STATE_PAYMENT_OK ) {
            return $this->redirect($this->generateUrl('_checkout_failed'));
        }

        return $this->redirect($this->generateUrl('_checkout_success'));
    }
}
