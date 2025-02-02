<?php

namespace Hanzo\Bundle\RetargetingBundle\Controller;

use Hanzo\Core\Tools;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderFeedController extends Controller
{
    private $response_format = 'xml';

    private $connections = [];

    private $connection_map = [
        'default'  => 'DK',
        'pdldbde1' => 'DE',
        'pdldbfi1' => 'FI',
        'pdldbnl1' => 'NL',
        'pdldbno1' => 'NO',
        'pdldbse1' => 'SE',
        'pdldbat1' => 'AT',
        'pdldbch1' => 'CH',
    ];

    /**
     * Fetches all orders since: $since.
     *
     * @Route("/retarteging/order-feed/{from}/{to}", defaults={"from"="-1 month", "to"="now"})
     *
     * @param  Request $request The current Request object
     * @param  string  $from    DateTime compatible string
     * @param  string  $to      DateTime compatible string
     * @throws AccessDeniedException
     * @return Response
     */
    public function orderFeedAction(Request $request, $from, $to)
    {
        $this->response_format = $request->query->get('format', 'xml');

        $ts = microtime(1);
// disabled for now - we rely on "basic auth" - maybe this will change in the future ?
//        if (!in_array($request->getClientIp(), ['185.14.184.152', '95.166.153.185'])) {
//            Tools::log('Access denied for '.$request->getClientIp().' to '.__METHOD__);
//            throw new AccessDeniedException('You do not have access to this area.');
//        }

        try {
            $from_date = new \DateTime($from);
            $to_date   = new \DateTime($to);
        } catch (\Exception $e) {
            return new Response("'since' not in a valid format.", 500);
        }

        ob_flush();
        set_time_limit(0);
        $that = $this;

        $response = new StreamedResponse();
        $response->setCallback(function() use ($from_date, $to_date, $that) {
            $that->streamFeed($from_date, $to_date);
        });


        switch ($this->response_format) {
            case 'xml':
                $content_type = 'application/xml';
                break;
            case 'csv':
                $content_type = 'application/csv';
                break;
        }

        $response->headers->add([
            'Content-type' => $content_type,
            'X-hanzo-m'    => Tools::humanReadableSize(memory_get_peak_usage()),
            'X-hanzo-t'    => (microtime(1) - $ts)

        ]);
        return $response->send();
    }

    private function streamFeed($from_date, $to_date)
    {
        mb_substitute_character(0xFFFD);

        $from_date = $from_date->format('Y-m-d H:i:s');
        $to_date   = $to_date->format('Y-m-d H:i:s');

        $this->exportHeader($from_date, $to_date);

        $sql = "
            SELECT
                o.id AS order_id, o.customers_id, o.first_name, o.last_name, o.email, o.phone, o.currency_code, o.billing_title, o.billing_first_name, o.billing_last_name, o.billing_address_line_1, o.billing_address_line_2, o.billing_postal_code, o.billing_city, o.billing_country, o.billing_state_province, o.billing_company_name, o.delivery_title, o.delivery_first_name, o.delivery_last_name, o.delivery_address_line_1, o.delivery_address_line_2, o.delivery_postal_code, o.delivery_city, o.delivery_country, o.delivery_state_province, o.delivery_company_name, o.created_at,
                e.code AS event_code,
                orders_id, ol.type, ol.products_id, ol.products_sku, ol.products_name, ol.products_color, ol.products_size, ol.expected_at, ol.original_price, ol.price, ol.vat, ol.quantity, ol.unit
            FROM
                orders AS o
            JOIN
                orders_lines AS ol
                ON (
                    ol.orders_id = o.id
                )
            LEFT JOIN
                events AS e
                ON (
                    e.id = o.events_id
                )
            WHERE
                o.state > 30
                AND
                    (o.created_at BETWEEN :from_date AND :to_date)
        ";

        foreach ($this->getConnections() as $name => $x) {
            $connection = $this->getConnection($name);

            $stmt = $connection->prepare($sql);
            $stmt->bindValue(':from_date', $from_date, \PDO::PARAM_STR);
            $stmt->bindValue(':to_date', $to_date, \PDO::PARAM_STR);
            $stmt->execute();

            switch ($this->response_format) {
                case 'xml':
                    $this->toXml($stmt, $name);
                    break;
                case 'csv':
                    $this->toCsv($stmt, $name);
                    break;
            }

            // try to free some memory
            gc_collect_cycles();
            $stmt->closeCursor();
            unset($order);
        }

        $this->exportFooter();
    }


    /**
     * Find active propel connections.
     *
     * @return array
     */
    protected function getConnections()
    {
        if (!$this->connections) {
            $this->findConnections();
        }

        return $this->connections;
    }


    /**
     * Get named Propel connection
     *
     * @param  string $name Name of connection to retrieve
     * @return PropelPDO    Propel connection object
     */
    protected function getConnection($name = 'default')
    {
        if (!$this->connections) {
            $this->findConnections();
        }

        if (empty($this->connections[$name])) {
            $this->connections[$name] = \Propel::getConnection($name);
        }

        return isset($this->connections[$name])
            ? $this->connections[$name]
            : null
        ;
    }


    /**
     * Parse Propel configuration and find connections.
     */
    private function findConnections()
    {
        foreach ($this->container->get('propel.configuration')->getFlattenedParameters() as $key => $value) {
            list($namespace, $name, $rest) = explode('.', $key, 3);

            // only add one connection, and only if the user is set
            if (($rest      == 'connection.user') &&
                ($namespace == 'datasources')
            ) {
                $value = trim($value);

                if (!empty($value) && empty($this->connections[$name])) {
                    $this->connections[$name] = null;
                    continue;
                }
            }
        }
    }


    private function toXml($stmt, $name)
    {
        $current_id = 0;
        while ($order = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($order['order_id'] != $current_id) {
                if ($current_id != 0) {
                    echo '</order_lines></order>';
                    flush();
                } else {
                    echo '<segment name="'.$this->connection_map[$name].'">';
                    flush();
                }
                $current_id = $order['order_id'];

                // cleanup ... *sigh*
                if ('DKK' === $order['currency_code']) {
                    $order['billing_postal_code']  = preg_replace('/[^0-9]/', '', $order['billing_postal_code']);
                    $order['delivery_postal_code'] = preg_replace('/[^0-9]/', '', $order['delivery_postal_code']);
                }

                $order['billing_company_name']  = mb_convert_encoding($order['billing_company_name'], 'UTF-8', 'UTF-8');
                $order['delivery_company_name'] = mb_convert_encoding($order['delivery_company_name'], 'UTF-8', 'UTF-8');

                echo '<order id="'.$order['order_id'].'"><customers_id>'.$order['customers_id'].'</customers_id><first_name><![CDATA['.$order['first_name'].']]></first_name><last_name><![CDATA['.$order['last_name'].']]></last_name><email>'.$order['email'].'</email><phone>'.$order['phone'].'</phone><currency_code>'.$order['currency_code'].'</currency_code><billing_title>'.$order['billing_title'].'</billing_title><billing_first_name><![CDATA['.$order['billing_first_name'].']]></billing_first_name><billing_last_name><![CDATA['.$order['billing_last_name'].']]></billing_last_name><billing_address_line_1><![CDATA['.$order['billing_address_line_1'].']]></billing_address_line_1><billing_address_line_2><![CDATA['.$order['billing_address_line_2'].']]></billing_address_line_2><billing_postal_code><![CDATA['.$order['billing_postal_code'].']]></billing_postal_code><billing_city><![CDATA['.$order['billing_city'].']]></billing_city><billing_country><![CDATA['.$order['billing_country'].']]></billing_country><billing_state_province><![CDATA['.$order['billing_state_province'].']]></billing_state_province><billing_company_name><![CDATA['.$order['billing_company_name'].']]></billing_company_name><delivery_title>'.$order['delivery_title'].'</delivery_title><delivery_first_name><![CDATA['.$order['delivery_first_name'].']]></delivery_first_name><delivery_last_name><![CDATA['.$order['delivery_last_name'].']]></delivery_last_name><delivery_address_line_1><![CDATA['.$order['delivery_address_line_1'].']]></delivery_address_line_1><delivery_address_line_2><![CDATA['.$order['delivery_address_line_2'].']]></delivery_address_line_2><delivery_postal_code><![CDATA['.$order['delivery_postal_code'].']]></delivery_postal_code><delivery_city><![CDATA['.$order['delivery_city'].']]></delivery_city><delivery_country><![CDATA['.$order['delivery_country'].']]></delivery_country><delivery_state_province><![CDATA['.$order['delivery_state_province'].']]></delivery_state_province><delivery_company_name><![CDATA['.$order['delivery_company_name'].']]></delivery_company_name><created_at>'.$order['created_at'].'</created_at></event_code>'.$order['event_code'].'</event_code><order_lines>';
                flush();
            }

            echo '<line><type>'.$order['type'].'</type><products_sku><![CDATA['.$order['products_sku'].']]></products_sku><products_name><![CDATA['.$order['products_name'].']]></products_name><products_color><![CDATA['.$order['products_color'].']]></products_color><products_size>'.$order['products_size'].'</products_size><original_price>'.$order['original_price'].'</original_price><price>'.$order['price'].'</price><vat>'.$order['vat'].'</vat><quantity>'.$order['quantity'].'</quantity><unit>'.$order['unit'].'</unit></line>';
            flush();

            // unset memory
            unset($order);
        }

        if (0 != $current_id) {
            echo '</order_lines></order></segment>';
            flush();
        }
    }


    private function toCsv($stmt, $name)
    {
        $current_id = 0;
        $fp = fopen('php://output', 'w');

        while ($order = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($order['order_id'] != $current_id) {
                $current_id = $order['order_id'];
            }

            // cleanup ... *sigh*
            if ('DKK' === $order['currency_code']) {
                $order['billing_postal_code']  = preg_replace('/[^0-9]/', '', $order['billing_postal_code']);
                $order['delivery_postal_code'] = preg_replace('/[^0-9]/', '', $order['delivery_postal_code']);
            }

            $order['billing_company_name']  = mb_convert_encoding($order['billing_company_name'], 'UTF-8', 'UTF-8');
            $order['delivery_company_name'] = mb_convert_encoding($order['delivery_company_name'], 'UTF-8', 'UTF-8');

            array_unshift($order, $this->connection_map[$name]);

            fputcsv($fp, $order, ';', '"');
            flush();

            // unset memory
            unset($order);
        }

        fclose($fp);
    }


    private function exportHeader($from_date, $to_date)
    {
        switch ($this->response_format) {
            case 'xml':
                echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<orders><since>'.$from_date.'</since><to>'.$to_date.'</to>';
                break;
            case 'csv':
                echo "country;order_id;customers_id;first_name;last_name;email;phone;currency_code;billing_title;billing_first_name;billing_last_name;billing_address_line_1;billing_address_line_2;billing_postal_code;billing_city;billing_country;billing_state_province;billing_company_name;delivery_title;delivery_first_name;delivery_last_name;delivery_address_line_1;delivery_address_line_2;delivery_postal_code;delivery_city;delivery_country;delivery_state_province;delivery_company_name;created_at;event_code;orders_id;type;products_id;products_sku;products_name;products_color;products_size;expected_at;original_price;price;vat;quantity;unit".PHP_EOL;
                break;
        }
        flush();
    }

    private function exportFooter()
    {
        if ('xml' == $this->response_format) {
            echo '</orders>';
            flush();
        }
    }
}
