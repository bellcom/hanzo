<?php

namespace Hanzo\Bundle\EventsBundle\Controller;

use Criteria;

use Symfony\Component\Form\FormError;

use Hanzo\Core\CoreController;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\Customers;
use Hanzo\Model\Addresses;
use Hanzo\Model\AddressesPeer;
use Hanzo\Model\CountriesPeer;
use Hanzo\Model\CustomersQuery;
use Hanzo\Model\EventsQuery;
use Hanzo\Model\CustomersPeer;
use Hanzo\Model\OrdersPeer;

use Hanzo\Bundle\AccountBundle\Form\Type\CustomersType;
use Hanzo\Bundle\AccountBundle\Form\Type\AddressesType;

use Hanzo\Bundle\AccountBundle\NNO\NNO;
use Hanzo\Bundle\AccountBundle\NNO\SearchQuestion;
use Hanzo\Bundle\AccountBundle\NNO\nnoSubscriber;
use Hanzo\Bundle\AccountBundle\NNO\nnoSubscriberResult;

class DefaultController extends CoreController
{

    public function indexAction($name)
    {
        return $this->render('EventsBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * createCustomerAction
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function createCustomerAction()
    {
        $request = $this->getRequest();
        $consultant = CustomersPeer::getCurrent();
        $order = OrdersPeer::getCurrent();

        // if the customer has been adding stuff to the basket, use that information here.
        $customer_id = $request->get('id');

        // Always perfer the post id
        // if no customer id has been posted
        if ( !$customer_id ) {
          if ($consultant->getId() != $order->getCustomersId()) {
              $customer_id = $order->getCustomersId();
          }
        }

        $hanzo = Hanzo::getInstance();
        $domainKey = $hanzo->get('core.domain_key');
        $errors = '';

        $countries = CountriesPeer::getAvailableDomainCountries();

        // If order is for the hostess, find her and use the Customer
        $attributes = $order->getOrdersAttributess()->toArray();
        $is_hostess = $order->isHostessOrder();

        if ($is_hostess === true) {
            $event = EventsQuery::create()
                ->filterById($order->getEventsId())
                ->findOne()
            ;

            if ($event->getCustomersId()) {
                $customer_id = $event->getCustomersId();
            } else {
                $is_hostess = false;
            }
        }

        if ('POST' == $request->getMethod() || $is_hostess) {
            if ($customer_id) {
                $customer = CustomersQuery::create()
                    ->joinWithAddresses()
                    ->useAddressesQuery()
                        ->filterByType('payment')
                    ->endUse()
                    ->findOneById($customer_id)
                ;

                if ($customer instanceof Customers) {
                    $pwd = $customer->getPassword();
                    $address = $customer->getAddresses()->getFirst();
                    $validation_groups = 'customer_edit';
                }
            }
        }

        if (empty($address)) {
            $customer = new Customers();
            $address = new Addresses();

            if (count($countries) == 1) {
                $address->setCountry($countries[0]->getLocalName());
                $address->setCountriesId($countries[0]->getId());
            }

            $customer->addAddresses($address);
            $validation_groups = 'customer';

        }

        $email = $customer->getEmail();

        $form = $this->createForm(new CustomersType(true, new AddressesType($countries)), $customer, array('validation_groups' => $validation_groups));
        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            $data = $form->getData();

            // verify that the email is not already in use.
            if (!$customer->isNew() && $email) {
                $form_email = $data->getEmail();

                if ($email != $form_email) {
                    $c = CustomersQuery::create()
                        ->filterById($customer->getId(), Criteria::NOT_EQUAL)
                        ->findOneByEmail($form_email)
                    ;
                    if ($c instanceof Customers) {
                        $form->addError(new FormError('email.exists'));
                    }
                }
            }

            // extra phone and zipcode constrints for .fi
            // TODO: figure out how to make this part of the validation process.
            if ('FI' == substr($domainKey, -2)) {
                // zip codes are always 5 digits in finland.
                if (!preg_match('/^[0-9]{5}$/', $address->getPostalCode())) {
                    $form->addError(new FormError('postal_code.required'));
                }

                // phonenumber must start with a 0 (zero)
                if (!preg_match('/^0[0-9]+$/', $customer->getPhone())) {
                    $form->addError(new FormError('phone.required'));
                }
            }

            if ($form->isValid()) {
                if (!$customer->getPassword()) {
                    $customer->setPassword($pwd);
                } elseif ($customer->isNew()) {
                    $pwd = $customer->getPassword();
                    $customer->setPassword(sha1($pwd));
                    $customer->setPasswordClear($pwd);
                }

                $address->setFirstName( $customer->getFirstName() );
                $address->setLastName( $customer->getLastName() );

                $customer->save();
                $address->save();

                $formData = $request->request->get('customers');
                if ( isset($formData['newsletter']) && $formData['newsletter']) {
                    $api = $this->get('newsletterapi');
                    $api->subscribe($customer->getEmail(), $api->getListIdAvaliableForDomain());
                }

                $order->setCustomersId($customer->getId());
                $order->setFirstName($customer->getFirstName());
                $order->setLastName($customer->getLastName());
                $order->setEmail($customer->getEmail());
                $order->setPhone($customer->getPhone());

                $order->setBillingAddressLine1($address->getAddressLine1());
                $order->setBillingAddressLine2($address->getAddressLine2());
                $order->setBillingPostalCode($address->getPostalCode());
                $order->setBillingCity($address->getCity());
                $order->setBillingCountry($address->getCountry());
                $order->setBillingCountriesId($address->getCountriesId());
                $order->setBillingStateProvince($address->getStateProvince());
                $order->save();

                return $this->redirect($this->generateUrl('_checkout'));
            }
        }

        return $this->render('EventsBundle:Default:create_customer.html.twig', array(
            'page_type' => 'events-create-customer',
            'is_hostess' => $is_hostess,
            'form' => $form->createView(),
            'errors' => $errors,
            'domain_key' => $domainKey
            ));
    }

    /**
     * fetchCustomerAction
     * @return void
     * @author Henrik Farre <hf@bellcom.dk>
     **/
    public function fetchCustomerAction()
    {
        $request = $this->getRequest();
        $value = $request->get('value');
        $type = strpos($value, '@') ? 'email' : 'phone';

        // hf@bellcom.dk: get phplist ids so the customer can be subscribed
        $api      = $this->get('newsletterapi');
        $listId   = $api->getListIdAvaliableForDomain();

        $error = true;
        $data = array();

        switch ($type) {
          case 'email':
              $customer = CustomersQuery::create()
                  ->findOneByEmail($value);

                if ($customer instanceof Customers) {
                    $c = new Criteria();
                    $c->addAscendingOrderByColumn(sprintf(
                        "FIELD(%s, '%s', '%s')",
                        AddressesPeer::TYPE,
                        'payment',
                        'shipping'
                    ));
                    $c->add(AddressesPeer::TYPE, 'payment');
                    $c->addOr(AddressesPeer::TYPE, 'shipping');
                    $c->setLimit(1);

                    $address = $customer->getAddressess($c);
                    $address = $address->getFirst();

                    if ($address instanceof Addresses) {
                        $data = array(
                            'id' => $customer->getId(),
                            'first_name' => $customer->getFirstName(),
                            'last_name'  => $customer->getLastName(),
                            'phone' => $customer->getPhone(),
                            'email_address' => $customer->getEmail(),
                            'address_line_1' => $address->getAddressLine1(),
                            'postal_code' => $address->getPostalCode(),
                            'city' => $address->getCity(),
                            'countries_id' => $address->getCountriesId(),
                            'country' => $address->getCountry(),
                            'newsletter' => $api->getSubscriptionStateByEmail($customer->getEmail(), $listId),
                        );
                    }
                }
                break;

            case 'phone':
                $domain_key = Hanzo::getInstance()->get('core.domain_key');

                // phone number lookyup only in denmark
                if (!in_array($domain_key, array('DK', 'SalesDK'))) {
                    break;
                }

                $result = $this->get('nno')->findOne($value);

                if ($result) {
                    $data = array(
                        'first_name' => $result['christianname'],
                        'last_name'  => $result['surname'],
                        'phone' => $result['phone'],
                        'address_line_1' => $result['address'],
                        'postal_code' => $result['zipcode'],
                        'city' => $result['district'],
                        'countries_id' => 58,
                        'country' => 'Denmark',
                    );
                }

                break;
        }

        return $this->json_response(array(
            'status' => $error,
            'message'   => '',
            'data'  => $data,
        ));
    }
}
