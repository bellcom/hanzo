<?php

namespace Hanzo\Bundle\WebServicesBundle\Controller;

use Exception;
use Monolog;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;

/**
 * @see
 *  http://miller.limethinking.co.uk/2011/04/15/symfony2-controller-as-service/
 *  http://speakerdeck.com/u/hhamon/p/silex-meets-soap-rest
 *
 */
class RestController extends CoreController
{
    protected $request;

    public function indexAction($version, $service_name)
    {
        $service_name = explode('-', $service_name.'-'.'service');
        array_walk($service_name, function($name, $index) use(&$service_name) {
            $service_name[$index] = ucfirst($name);
        });
        $service_name = implode('', $service_name);

        $service_class = str_replace('Controller', 'Services\Rest', __NAMESPACE__) . "\\{$service_name}\\$service_name";
        $handler = new $service_class($this);


        $response = array();
        return new Response(json_encode($response), 200, array(‘Content-type’ => ‘application/json’));
    }


    public function checkStockAction(){}

    public function videoAction()
    {
        //bc_log($this->get('request'))
        $request = $this->get('request');
        $data = array(
            'video'  => $request->get('src', false),
            'width'  => $request->get('width', false),
            'height' => $request->get('height', false),
            'banner' => $request->get('banner', 'video_bg'),
            'embed'  => (boolean) $request->get('embed', 0),
        );
    }


    public function jaiksAction(Request $request)
    {
        $router  = $this->get('router');
        $kernel  = $this->get('kernel');
        $payload = json_decode($request->get('payload'), true);

        foreach($payload as $index => $call) {
            try {
                $uri = '/'.$request->getLocale().$call['action'];
                $route = $router->match($uri);
                $sub_request = $this->get('request')->duplicate([], $call['data'], $route);

                $response = $kernel->handle($sub_request, HttpKernelInterface::SUB_REQUEST)->getContent();
                if ('{' == substr($response, 0, 1)) {
                    $response = json_decode($response);
                }

                $payload[$index]['response'] = $response;
            } catch(Exception $e) {
                $message = $e->getMessage() ?: 'unknown route: ' . $uri;

                $payload[$index]['response'] = [
                    'status' => false,
                    'message' => $message
                ];
            }
        }

        return $this->json_response($payload);
    }
}
