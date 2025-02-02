<?php
/*
 * This file is part of the hanzo package.
 *
 * (c) Ulrik Nielsen <un@bellcom.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hanzo\Bundle\DiscountBundle\Handlers;

use Hanzo\Core\Hanzo;
use Hanzo\Model\Customers;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersLinesPeer;
use Hanzo\Model\Products;
use Hanzo\Model\ProductsDomainsPricesPeer;
use Hanzo\Model\ProductsQuantityDiscountQuery;
use Hanzo\Model\ProductsQuery;
use Psr\Log\LoggerInterface;

class QuantityDiscountHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Products
     */
    private $product;

    /**
     * @var Orders
     */
    private $order;


    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param Orders $order
     */
    public function setOrder(Orders $order)
    {
        $this->order = $order;
    }


    /**
     * @param  Orders   $order
     * @param  Products $product
     * @return self
     */
    public function initialize(Orders $order, Products $product = null)
    {
        $this->order   = $order;
        $this->product = $product;

        return $this;
    }


    /**
     * @return Orders
     */
    public function handle()
    {
        $time   = time();
        $hanzo  = Hanzo::getInstance();

        $master = $this->product->getMaster();
        $breaks = ProductsQuantityDiscountQuery::create()
            ->orderBySpan(\Criteria::DESC)
            ->filterByDomainsId($hanzo->get('core.domain_id'))
            ->filterByValidFrom($time, \Criteria::LESS_EQUAL)
            ->_or()
            ->filterByValidFrom(null, \Criteria::ISNULL)
            ->filterByValidTo($time, \Criteria::GREATER_EQUAL)
            ->_or()
            ->filterByValidTo(null, \Criteria::ISNULL)
            ->findByProductsMaster($master)
        ;

        if (0 === $breaks->count()) {
            return $this->order;
        }

        // disable quantity discount for shopping advisors and employees - if there personal discounts is in effect.
        if (strpos(strtolower(Hanzo::getInstance()->get('core.domain_key')), 'sales') === false) {
            $customer = $this->order->getCustomers();
            if (($customer instanceof Customers) &&
                (1 < $customer->getGroupsId()) &&
                (0 == Hanzo::getInstance()->get('webshop.disable_discounts'))
            ) {
                return $this->order;
            }

            // Gift and friend purchases does not trigger quantity discount.
            $attributes = $this->order->getAttributes();
            if (isset($attributes->purchase->type) && in_array($attributes->purchase->type, ['gift', 'friend'])) {
                return $this->order;
            }
        }

        $ids = ProductsQuery::create()->select('Id')->findByMaster($master)->toArray();

        $c = new \Criteria();
        $c->add(OrdersLinesPeer::PRODUCTS_ID, $ids, \Criteria::IN);

        $total = 0;
        foreach ($this->order->getOrdersLiness($c) as $line) {
            $total += $line->getQuantity();
        }

        $discount = 0;
        foreach ($breaks as $break) {
            if ($total >= $break->getSpan()) {
                $discount = $break->getDiscount();
                break;
            }
        }

        foreach ($this->order->getOrdersLiness() as $line) {
            if (!in_array($line->getProductsId(), $ids)) {
                continue;
            }

            $line->setPrice($line->getOriginalPrice() - $discount);
            $line->save();
        }

        return $this->order;
    }

    /**
     * In the checkout flow we need to recalculate.
     *
     * @return Orders
     */
    public function reCalculate()
    {
        $attributes = $this->order->getAttributes();
        if (isset($attributes->purchase, $attributes->purchase->type) &&
            in_array($attributes->purchase->type, ['gift', 'friend'])
        ) {
            $lines = $this->order->getOrdersLiness();

            $productIds = [];
            foreach ($lines as $line) {
                if ('product' == $line->getType()) {
                    $productIds[] = $line->getProductsId();
                }
            }

            $prices = ProductsDomainsPricesPeer::getProductsPrices($productIds);

            foreach ($lines as $line) {
                if ('product' == $line->getType()) {
                    $price = $prices[$line->getProductsId()];
                    $original = $price['normal']['price'];

                    if (isset($price['sales'], $price['sales']['price'])) {
                        $vat      = $price['sales']['vat'];
                        $price    = $price['sales']['price'];
                    } else {
                        $vat      = $price['normal']['vat'];
                        $price    = $price['normal']['price'];
                    }

                    $line->setPrice($price);
                    $line->setVat($vat);
                    $line->setOriginalPrice($original);
                }
            }
        }

        return $this->order;
    }
}
