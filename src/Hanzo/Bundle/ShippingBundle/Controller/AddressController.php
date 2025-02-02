<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;
use Hanzo\Model\Addresses;
use Hanzo\Model\AddressesQuery;
use Hanzo\Model\CustomersPeer;
use Hanzo\Model\CountriesPeer;
use Hanzo\Model\CountriesQuery;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersPeer;
use Hanzo\Model\ShippingMethods;

/**
 * Class AddressController
 *
 * @package Hanzo\Bundle\ShippingBundle\Controller
 */
class AddressController extends CoreController
{
    /**
     * Builds the address form based on address type.
     *
     * @param string  $type
     * @param integer $customer_id
     *
     * @throws \Exception
     * @internal param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction($type = 'payment', $customer_id = null)
    {
        $shortDomainKey = substr(Hanzo::getInstance()->get('core.domain_key'), -2);
        $order = OrdersPeer::getCurrent(false);

        if (null === $customer_id) {
            if ($order->getCustomersId()) {
                $customer_id = $order->getCustomersId();
            } else {
                $id = CustomersPeer::getCurrent()->getId();
                if ($id) {
                    $customer_id = $id;
                }
            }
        }

        $countries        = CountriesPeer::getAvailableDomainCountries(true);
        $deliveryMethodId = $order->getDeliveryMethod();

        if ($type == 'CURRENT-SHIPPING-ADDRESS') {
            $type = 'shipping';
            $form = '<div class="block"><form action="" method="post" class="address"></form></div>';

            if ('json' === $this->getFormat()) {
                return $this->json_response([
                    'status'  => true,
                    'message' => '',
                    'data'    => ['html' => $form],
                ]);
            }

            return $this->response($form);
        } elseif ('payment' == $type) {
            $address = AddressesQuery::create()
              ->filterByCustomersId($customer_id)
              ->filterByType($type)
              ->findOne();
        }

        // to enable address locator or not.
        // 12  = Bring (DK)
        // 15  = PostNord (DK)
        // 71  = hmmm ...
        // 30  = Bring (SE)
        // 31  = PostNord (SE)
        // 500 = Bring (FI)
        // 700 = Bring (NO)
        $enableLocator = (($type !== 'payment') && in_array($deliveryMethodId, [12, 15, 71, 30, 31, 500, 700]));

        if (empty($address)) {
            $address = new Addresses();
            $address->setType($type);
            $address->setCustomersId($customer_id);

            if ($order->getFirstName()) {
                $address->setFirstName($order->getFirstName());
                $address->setLastName($order->getLastName());
            }
        } elseif ('overnightbox' === $type) {
            $address->setFirstName($order->getFirstName());
            $address->setLastName($order->getLastName());
        }

        $builder = $this->createFormBuilder($address, [
            'validation_groups' => 'shipping_bundle_'.$type
        ]);

        if (in_array($type, ['company_shipping', 'overnightbox'])) {
            $label = 'company.name';
            if ($type === 'overnightbox') {
                $label = 'overnightbox.label';
            }

            $builder->add('company_name', null, [
                'label'              => $label,
                'required'           => true,
                'translation_domain' => 'account'
            ]);
        }

        if (in_array($shortDomainKey, ['DE'])) {
            $builder->add('title', 'choice', [
                'choices' => [
                    'female' => 'title.female',
                    'male'   => 'title.male',
                ],
                'label'              => 'title',
                'required'           => true,
                'trim'               => true,
                'translation_domain' => 'account',
            ]);
        }

        $builder->add('first_name', null, [
            'required'           => true,
            'translation_domain' => 'account'
        ]);

        $builder->add('last_name', null, [
            'required'           => true,
            'translation_domain' => 'account'
        ]);

        if ($type === 'payment') {
            $builder->add('phone', null, [
                'required'           => true,
                'translation_domain' => 'account'
            ]);
        }

        $builder->add('address_line_1', null, [
            'translation_domain' => 'account',
            'max_length'         => 35
        ]);

        $attr = [];
        if (in_array($shortDomainKey, ['AT', 'CH', 'DE', 'DK', 'FI', 'NL', 'NO', 'SE'])) {
            $attr = ['class' => 'auto-city'];
        }

        $builder->add('postal_code', null, [
            'required'           => true,
            'translation_domain' => 'account',
            'attr'               => $attr,
        ]);

        $builder->add('city', null, [
            'required'           => true,
            'translation_domain' => 'account',
            'read_only'          => (count($attr) ? true : false),
            'attr'               => ['class' => 'js-auto-city-'.$type]
        ]);

        if ('overnightbox' === $type || $enableLocator) {
            list($countryId, $countryName) = each($countries);

            $address->setCountriesId($countryId);
            $address->setCountry($countryName);

            $builder->add('countries_id', 'hidden', ['data' => $countryId]);
            $builder->add('external_address_id', 'hidden', ['data' => $address->getExternalAddressId()]);
        } else {
            if (count($countries) > 1) {
                $builder->add('countries_id', 'choice', [
                    'empty_value'        => 'choose.country',
                    'choices'            => $countries,
                    'required'           => true,
                    'translation_domain' => 'account'
                ]);
            } else {
                list($countryId, $countryName) = each($countries);

                $address->setCountriesId($countryId);
                $address->setCountry($countryName);

                $builder->add('countries_id', 'hidden', ['data' => $countryId]);
                $builder->add('country', null, [
                    'read_only'          => true,
                    'translation_domain' => 'account'
                ]);
            }
        }

        // if the locator is enables, set all elements to read-only to prevent customers from editing the found address.
        if ($enableLocator) {
            foreach ($builder->all() as $element) {
                $element->setDisabled(true);
            }
        }

        $builder->add('customers_id', 'hidden', ['data' => $customer_id]);

        $form = $builder->getForm();

        $baseType = 'is-shipping';
        if ('payment' === $type) {
            $baseType = 'is-payment';
        }

        $response = $this->render('ShippingBundle:Address:form.html.twig', [
            'type'           => $type,
            'base_type'      => $baseType,
            'enable_locator' => $enableLocator,
            'method_id'      => $deliveryMethodId,
            'form'           => $form->createView(),
        ]);


        if ('json' === $this->getFormat()) {
            $html = $response->getContent();

            return $this->json_response([
                'status'  => true,
                'message' => '',
                'data'    => ['html' => $html],
            ]);
        }

        return $response;
    }


    /**
     * Processes the address of a given type
     *
     * @param Request $request
     * @param string  $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function processAction(Request $request, $type)
    {
        $status = false;

        if ('POST' === $request->getMethod()) {
            // TODO: not hardcoded
            $typeMap = [
                'company_shipping' => 'shipping',
                'overnightbox'     => 'shipping',
            ];

            if (isset($typeMap[$type])) {
                $type = $typeMap[$type];
            }

            $order = OrdersPeer::getCurrent();
            $data  = $request->request->get('form');

            if ($type === 'shipping') {
                switch ($order->getDeliveryMethod()) {
                    case 11: // bring dk
                    case 17: // postnord dk
                        $method = 'company_shipping';
                        break;
                    case 12: // bring dk (drop point)
                    case 15: // postnord dk (drop point)
                    case 30: // bring se (drop point)
                    case 31: // postnord se (drop point)
                    case 71:
                        $method = 'overnightbox';
                        $validationFields[] = 'company_name';
                        break;
                    default:
                        $method = 'shipping';
                        break;
                }
            } else {
                $method = 'payment';
            }

            $address = AddressesQuery::create()
                ->filterByCustomersId($order->getCustomersId())
                ->filterByType($method)
                ->findOne();

            if (!$address instanceof Addresses) {
                $address = new Addresses();
                $address->setCustomersId($data['customers_id']);
                $address->setType($method);
            }

            if (!empty($data['title'])) {
                $address->setTitle($data['title']);
            }

            $address->setFirstName($data['first_name']);
            if (!empty($data['last_name'])) {
                $address->setLastName($data['last_name']);
            }

            if (!empty($data['address_line_1'])) {
              $address->setAddressLine1($data['address_line_1']);
            } else {
              $address->setAddressLine1(null);
            }

            if (!empty($data['address_line_2'])) {
              $address->setAddressLine2($data['address_line_2']);
            } else {
              $address->setAddressLine2(null);
            }

            $address->setPostalCode($data['postal_code']);
            $address->setCity($data['city']);
            $address->setStateProvince(null);

            $country = CountriesQuery::create()->findOneById($data['countries_id']);
            $address->setCountry($country->getName());
            $address->setCountriesId($data['countries_id']);

            // locator addresses needs external address id's to work.
            // 12  = Bring (DK)
            // 15  = PostNord (DK)
            // 71  = hmmm ...
            // 30  = Bring (SE)
            // 31  = PostNord (SE)
            // 500 = Bring (FI)
            // 700 = Bring (NO)

            $enableLocator = (in_array($order->getDeliveryMethod(), [12, 15, 71, 30, 31, 500, 700]));

            // special rules apply for overnightbox
            if ($method === 'overnightbox' || (isset($data['external_address_id']) && $enableLocator)) {
                $address->setExternalAddressId($data['external_address_id']);
                $address->setAddressLine2(null);
            }

            // remember to save the company name.
            if (in_array($method, ['company_shipping', 'overnightbox'])) {
                if (empty($data['company_name'])) {
                    $data['company_name'] = trim($data['first_name'].' '.$data['last_name']);
                }
                $address->setCompanyName($data['company_name']);
            }

            if ('payment' === $method) {
                $customer = $address->setPhone($data['phone']);
            }


            // validate the address
            $validator = $this->get('validator');
            $translator = $this->get('translator');

            // fi uses different validation group to support different rules

            // In Norway delivery addresses differ a bit...
            $rule = $method;
            if (('shipping' === $method) && (700 == $order->getDeliveryMethod())) {
                $rule .= '_no';
            }

            $validationGroup = 'shipping_bundle_'.$rule;

            $objectErrors = $validator->validate($address, [$validationGroup]);

            $errors = [];
            foreach ($objectErrors->getIterator() as $error) {
                if (null === $error->getMessagePluralization()) {
                    $errors[] = $translator->trans(
                        $error->getMessageTemplate(),
                        $error->getMessageParameters(),
                        'validators'
                    );
                } else {
                    $errors[] = $translator->transChoice(
                        $error->getMessageTemplate(),
                        $error->getMessagePluralization(),
                        $error->getMessageParameters(),
                        'validators'
                    );
                }
            }

            if (count($errors)) {
                // needed or we cannot continue in the checkout
                $order->setAttribute('not_valid', 'global', 1);
                $order->save();

                $message = '<ul class="error"><li>'.implode('</li><li>', $errors).'</li></ul>';

                return $this->json_response([
                    'status'  => false,
                    'message' => $message,
                ]);
            }

            $address->save();

            // change phone number
            if (isset($customer) && ('payment' === $method)) {
                $customer->save();
            }

            if ($type === 'payment') {
                $order->setBillingAddress($address);
            } elseif ($type === 'shipping') {
                $order->setDeliveryAddress($address);
            }

            $order->clearAttributesByKey('not_valid');
            $order->save();
            $status = true;
        }

        if ('json' === $this->getFormat()) {
            return $this->json_response([
                'status'  => $status,
                'message' => '',
            ]);
        }
    }
}
