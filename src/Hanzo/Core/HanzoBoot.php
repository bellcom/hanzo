<?php

namespace Hanzo\Core;

use AppKernel;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Core\RedisCache;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class HanzoBoot
{
    protected $router;
    protected $kernel;

    /**
     * __construct
     *
     * @param Router $router
     */
    public function __construct(Router $router, AppKernel $kernel)
    {
        $this->router = $router;
        $this->kernel = $kernel;
    }


    /**
     * onKernelRequest events
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->sslHandeling($event);
        $this->deviceCheck($event);
        $this->webshopAccessRestrictionCheck($event);
    }


    protected function sslHandeling($event)
    {
        // only scan MASTER_REQUESTS
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        // skip ssl check for these routes
        if (in_array($request->get('_route'), [
            '_account_create',
            '_account_lost_password',
            '_account_phone_lookup',
            '_internal',
            '_wdt',
            'bazinga_exposetranslation_js',
            'login',
            'login_check',
            'muneris_nno_lookup',
        ])) {
            return;
        }

        if (($request->isSecure()) &&
            (!$this->kernel->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        ) {
            $request->server->set('HTTPS', false);
            $request->server->set('SERVER_PORT', 80);
            return $event->setResponse(new RedirectResponse($request->getUri()));
        }
    }


    /**
     * device check is used to allow us to toggle layout based on the device type
     *
     * @param GetResponseEvent $event
     */
    protected function deviceCheck(GetResponseEvent $event)
    {
        $container = $this->kernel->getContainer();
        $request   = $event->getRequest();
        $attr      = $request->attributes;

        $attr->set('_request_type', $event->getRequestType());

        if ($request->headers->has('x-ua-device')) {
            $device = $request->headers->get('x-ua-device');
        } else {
            $device = 'pc';
            if ($container->hasParameter('x_ua_device') &&
                $container->getParameter('x_ua_device')
            ) {
                $device = $container->getParameter('x_ua_device');
            }
        }

        $attr->set('_x_device', $device);

        $theme = $container->get('liip_theme.active_theme');
        // set theme to active name + '_mobile' ex: '2013s1_mobile'
        if (preg_match('/^mobile/i', $device) && !preg_match('/mobile/', $theme->getName())) {
            $theme->setName($theme->getName().'_mobile');
        }
    }


    /**
     * webshop access restriction check
     *
     * @param GetResponseEvent $event
     */
    protected function webshopAccessRestrictionCheck(GetResponseEvent $event)
    {
        $hanzo = Hanzo::getInstance();

        if (1 == $hanzo->get('webshop.closed', 0)) {
            $request = $event->getRequest();

            // allow edits of event orders.
            // TODO: must be more generic
            if (isset($_COOKIE['__ice'])) {
                return;
            }

            list($uri,) = explode('?', $request->getRequestUri());
            $clean = str_replace('//', '/', str_replace('app_dev.php', '', $uri));
            $params = $this->router->match($clean);

            $blacklist = [
                'basket_view',
                'ws_stock',
                'basket_add',
                '_checkout',
            ];

            if ($params && isset($params['_route']) && in_array($params['_route'], $blacklist)) {
                $params['ip_restricted'] = 1;
            }

            // if the route is ip restricted, redirect if not from an approved ip
            if (isset($params['ip_restricted']) && $params['ip_restricted'] == 1 ) {
                $ips = explode("\n", str_replace("\r", '', $hanzo->get('webshop.closed.allowed_ips', '')));

                if (!in_array($request->getClientIp(), $ips)) {
                    $goto = '';
                    $env = explode('_', $hanzo->get('core.env'));

                    if (!in_array($env[0], array('prod'))) {
                        $goto = '/app_'.$env[0].'.php';
                    }

                    $goto .= '/'.$params['_locale'].'/';

                    $hanzo->container->get('session')->setFlash('notice', 'access.denied');
                    header('Location: '.$goto); exit;
                }
            }
        }
    }


    /**
     * handles device cookie setting
     *
     * @param string $device name of the device type
     */
    protected static function setDeviceCookie($device)
    {
        if (isset($_COOKIE['_x_device']) && $_COOKIE['_x_device'] == $device) {
            return;
        }

        Tools::setCookie("_x_device", $device, 0, true);
    }
}
