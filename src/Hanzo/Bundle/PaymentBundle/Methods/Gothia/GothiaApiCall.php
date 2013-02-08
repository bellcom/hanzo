<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\PaymentBundle\Methods\Gothia;

use Exception;

use Hanzo\Core\Hanzo,
    Hanzo\Core\Tools,
    Hanzo\Model\Orders,
    Hanzo\Model\Customers,
    Hanzo\Model\GothiaAccounts,
    Hanzo\Bundle\PaymentBundle\PaymentMethodApiCallInterface,
    Hanzo\Bundle\PaymentBundle\Methods\Gothia\GothiaApi,
    Hanzo\Bundle\PaymentBundle\Methods\Gothia\GothiaApiCallResponse;

// Great... fucking oldschool crap code:
require 'AFWS.php';

class GothiaApiCall implements PaymentMethodApiCallInterface
{
    /**
     * undocumented class variable
     *
     * @var GothiaApiCall instance
     **/
    private static $instance = null;

    /**
     * undocumented class variable
     *
     * @var GothiaApi
     **/
    protected $api = null;

    /**
     * __construct
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    private function __construct() {}

    /**
     * someFunc
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public static function getInstance( Array $settings, GothiaApi $api)
    {
        if ( self::$instance === null )
        {
            self::$instance = new self;
        }

        self::$instance->settings = $settings;
        self::$instance->api = $api;

        return self::$instance;
    }

    /**
     * call
     * @return GothiaApiCallResponse
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    protected function call( $function, $request )
    {
        $errorReporting = error_reporting(0);

        if ( $this->api->getTest() )
        {
            $client = AFSWS_Init( 'test' );
        }
        else
        {
            $client = AFSWS_Init( 'live' );
        }

        try
        {
            $response = $client->call( $function, $request );
        }
        catch (Exception $e)
        {
            #Tools::debug( $e->getMessage(), __METHOD__, array( 'Function' => $function, 'Callstring' => $request));
            throw new GothiaApiCallException( $e->getMessage() );
        }

        error_reporting($errorReporting);

        // If there is a problem with the connection or something like that a GothiaApiCallException is thrown
        // Else a GothiaApiCallResponse is returned, which might still be an "error" but the call went through fine

        if ( $response === false || $client->fault )
        {
            $msg = 'Kunne ikke forbinde til Gothia Faktura service, prøv igen senere';

            $err = $client->getError();

            if ( $err )
            {
                $msg .= ' '.$err;
            }

            $errors[] = $msg;

            $debugErrors = $errors;
            $debugErrors['Function'] = $function;
            $debugErrors['Callstring'] = $request;
            #Tools::debug( 'Call failed', __METHOD__, $debugErrors );

            throw new GothiaApiCallException( implode('<br>', $errors) );
        }

        if ( $this->api->getTest() )
        {
          Tools::debug( 'Gothia debug call', __METHOD__, array( 'Function' => $function, 'Callstring' => $request));
          Tools::debug( 'Gothia debug response', __METHOD__, array( 'Response' => $response ));
        }

        $gothiaApiCallResponse = new GothiaApiCallResponse( $response, $function );

        return $gothiaApiCallResponse;
    }

    /**
     * callAcquirersStatus
     * @param Customers $customer
     * @return GothiaApiCallResponse
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function checkCustomer( Customers $customer )
    {
        $hanzo = Hanzo::getInstance();
        $domain_key = str_replace('Sales', '', $hanzo->get('core.domain_key'));
        // Used default currency from domain_settings instead
        // $currency_map = array(
        //     'SE' => 'SEK',
        //     'FI' => 'EUR',
        //     'NL' => 'EUR',
        //     'NO' => 'NOK',
        //     'DK' => 'DKK'
        // );

        $addresses     = $customer->getAddressess();

        if ( !isset($addresses[0]) )
        {
            #Tools::debug( 'Customer is missing an address', __METHOD__ );
            throw new GothiaApiCallException( 'Missing address' );
        }

        $address       = $addresses[0];
        $gothiaAccount = $customer->getGothiaAccounts();
        $customerId    = $customer->getId();

        if ( $this->api->getTest() )
        {
            $customerId = $this->getTestCustomerId($gothiaAccount->getSocialSecurityNum());
        }

        if ( empty($customerId) )
        {
            #Tools::debug( 'Missing customer id', __METHOD__ );
            throw new GothiaApiCallException( 'Missing customer id' );
        }

        $callString = AFSWS_CheckCustomer(
	        $this->userString(),
            AFSWS_Customer(
                $address->getAddressLine1().' '.$address->getAddressLine2(),
                $domain_key,
                $hanzo->get('core.currency'), // $currency_map[$domain_key], ab@bellcom.dk 070213
                $customerId,
                'Person',
                null,
                $gothiaAccount->getDistributionBy(),
                $gothiaAccount->getDistributionType(),
                $customer->getEmail(),
                null,
                $customer->getFirstName(),
                $customer->getLastName(),
                null,
                $gothiaAccount->getSocialSecurityNum(),
                $customer->getPhone(),
                $address->getPostalCode(),
                $address->getCity(),
                null
            )
        );

        $response = $this->call('CheckCustomer', $callString);

        return $response;
    }

    /**
     * getTestCustomerId
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function getTestCustomerId( $ssn )
    {
        $customerId = false;

        switch ($ssn)
        {
          case 4409291111:
              $customerId = 100010;
            break;
          case 4402181111:
              $customerId = '00100000';
              break;
          case 12053400068:
              $customerId = 100001; // .no test
              break;
          case 18106500076:
              $customerId = 100002; // .no test
              break;
          case 18106500157:
              $customerId = 100003; // .no test
              break;
          case 18126500137:
              $customerId = 100004; // .no test bad rating
              break;
          case "090260-052K": // .FI test users
              $customerId = 100106;
              break;
          case "090648-458T":
              $customerId = 100107;
              break;
          case "020185-3134":
              $customerId = 100108;
              break;
          case "010771-255U":
              $customerId = 100109;
              break;
          case "300976-787L": // Payment defaults
              $customerId = 100110;
              break;
          case "301076-0676": // -||-
              $customerId = 100111;
              break;
          case "26101945": // Danish cases
              $customerId = 100003;
              break;
          case "21111925": // Danish cases
              $customerId = 100004;
              break;
          case "21121974": // Danish cases
              $customerId = 100005;
              break;
          case "12071992": // Danish cases
              $customerId = 100006;
              break;
          case "30031997": // Danish cases
              $customerId = 100007;
              break;
          case "25031969": // Danish cases
              $customerId = 100002;
              break;
          case "01051982": // Netherland cases
              $customerId = 'T1234';
              break;
        }

        return $customerId;
    }

    /**
     * placeReservation
     * @param Customers $customer
     * @param Orders $order
     * @return GothiaApiCallResponse
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function placeReservation( Customers $customer, Orders $order )
    {
        $amount         = number_format( $order->getTotalPrice(), 2, '.', '' );
        $customerId     = $customer->getId();
        $currency_code  = $order->getCurrencyCode();

        if ( $this->api->getTest() )
        {
            $gothiaAccount = $customer->getGothiaAccounts();
            $customerId = $this->getTestCustomerId($gothiaAccount->getSocialSecurityNum());
            #Tools::debug( 'Test Gothia', __METHOD__, array('Amount' => $amount, 'customerId' => $customerId, 'currency_code' => $currency_code));
        }

        // hf@bellcom.dk, 29-aug-2011: remove last param to Reservation, @see comment in cancelReservation function -->>
        $callString = AFSWS_PlaceReservation(
	        $this->userString(),
            AFSWS_Reservation('NoAccountOffer', $amount, $currency_code, $customerId, null)
        );
        // <<-- hf@bellcom.dk, 29-aug-2011: remove last param to Reservation, @see comment in cancelReservation function

        $response = $this->call('PlaceReservation', $callString);

        return $response;
    }

    /**
     * cancel
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function cancel( Customers $customer, Orders $order )
    {
        return $this->cancelReservation( $customer, $order );
    }

    /**
     * cancelReservation
     * @param Customers $customer
     * @param Orders $order
     * @return GothiaApiCallResponse
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function cancelReservation( Customers $customer, Orders $order )
    {
        $total      = $order->getTotalPrice();

        if ( empty($total) )
        {
            Tools::debug( 'Empty total', __METHOD__ );
            throw new GothiaApiCallException( 'Empty total' );
        }

        $amount     = number_format( $total, 2, '.', '' );
        $customerId = $customer->getId();

        if ( $this->api->getTest() )
        {
            $gothiaAccount = $customer->getGothiaAccounts();
            $customerId = $this->getTestCustomerId($gothiaAccount->getSocialSecurityNum());
        }

        if ( empty($customerId) )
        {
            #Tools::debug( 'Missing customer id', __METHOD__ );
            throw new GothiaApiCallException( 'Missing customer id' );
        }

        if ( empty($amount) )
        {
            #Tools::debug( 'Empty amount', __METHOD__ );
            throw new GothiaApiCallException( 'Empty amount' );
        }

        // Gothia uses tns:CancelReservation which contains a tns:cancelReservation, therefore the 2 functions with almost the same name
        // hf@bellcom.dk, 29-aug-2011: remove 2.nd param to CancelReservationObj, pr request of Gothia... don't know why, don't care why :) -->>
        // hf@bellcom.dk, 21-jan-2012: 2.nd param was order no.
        $callString = AFSWS_CancelReservation(
	        $this->userString(),
            AFSWS_CancelReservationObj( $customerId, null, $amount)
        );
        // <<-- hf@bellcom.dk, 29-aug-2011: remove 2.nd param to CancelReservationObj, pr request of Gothia... don't know why, don't care why :)

        $response = $this->call('CancelReservation', $callString);

        return $response;
    }

    /**
     * userString
     * @return string
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    private function userString()
    {
        return AFSWS_User($this->settings['username'], $this->settings['password'], $this->settings['clientId']);
    }
}
