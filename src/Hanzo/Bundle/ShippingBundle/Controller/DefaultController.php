<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\ShippingBundle\Controller;

use Hanzo\Model\CountriesPeer;
use Hanzo\Model\CountriesQuery;
use Symfony\Component\HttpFoundation\Request;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;

use Hanzo\Model\OrdersPeer;
use Hanzo\Model\OrdersLinesQuery;
use Hanzo\Model\ShippingMethods;

class DefaultController extends CoreController
{
    /**
     * blockAction
     * @return Response
     **/
    public function blockAction()
    {
        $api     = $this->get('shipping.shippingapi');
        $methods = $api->getMethods();
        $order   = OrdersPeer::getCurrent(true);

        $method = '';
        if ($order->getDeliveryMethod() && $order->getDeliveryFirstName()) {
            $method = $order->getDeliveryMethod();
        }

        return $this->render('ShippingBundle:Default:block.html.twig', array(
            'methods' => $methods,
            'selected_method' => $method
        ));
    }


    /**
     * set shipping method on order
     *
     * @param Request $request
     * @return Response Returns JSON encoded response
     */
    public function setMethodAction(Request $request)
    {
        $api     = $this->get('shipping.shippingapi');
        $methods = $api->getMethods();

        if (isset($methods[$request->request->get('method')])) {
            /** @var \Hanzo\Model\ShippingMethods $method */
            $method = $methods[$request->request->get('method')];

            $order = OrdersPeer::getCurrent(true);
            $order->setDeliveryMethod($request->request->get('method'));

            $vat = 0;
            // Only calculate vat on delivery in DK
            if ('da_DK' === $request->getLocale()) {
                $vat = 25;
            }

            $order->setShipping($method, ShippingMethods::TYPE_NORMAL, $vat);

            if ($method->getFee()) {
                // No vat calculated on fees ... For now ...
                $order->setShipping($method, ShippingMethods::TYPE_FEE);
            }

            $order->setUpdatedAt(time());
            $order->save();

            $response = array(
                'status' => true,
                'message' => '',
            );
        } else {
            $response = array(
                'status' => false,
                'message' => $this->get('translator')->trans('err.unknown_shipping_method', [], 'checkout'),
            );

        }

        return $this->json_response($response);
    }
}
