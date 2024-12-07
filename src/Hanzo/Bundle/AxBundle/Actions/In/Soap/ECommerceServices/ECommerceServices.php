<?php

namespace Hanzo\Bundle\AxBundle\Actions\In\Soap\ECommerceServices;

use Hanzo\Core\Tools;
use Hanzo\Core\Timer;

use Hanzo\Model\Addresses;
use Hanzo\Model\AddressesQuery;
use Hanzo\Model\CategoriesQuery;
use Hanzo\Model\Customers;
use Hanzo\Model\CustomersQuery;
use Hanzo\Model\Consultants;
use Hanzo\Model\Countries;
use Hanzo\Model\CountriesQuery;
use Hanzo\Model\DomainsQuery;
use Hanzo\Model\LanguagesQuery;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;
use Hanzo\Model\OrdersAttributes;
use Hanzo\Model\OrdersAttributesQuery;
use Hanzo\Model\Products;
use Hanzo\Model\ProductsDomainsPrices;
use Hanzo\Model\ProductsDomainsPricesQuery;
use Hanzo\Model\ProductsI18n;
use Hanzo\Model\ProductsToCategories;
use Hanzo\Model\ProductsToCategoriesQuery;
use Hanzo\Model\ProductsQuery;

use Hanzo\Bundle\AdminBundle\Event\FilterCategoryEvent;
use Hanzo\Bundle\AxBundle\Actions\In\Soap\SoapService;
use Hanzo\Bundle\NewsletterBundle\NewsletterApi;
use Hanzo\Bundle\PaymentBundle\PaymentApiCallException;

use Exception;
use Propel;
use PropelCollection;

/**
 * Class ECommerceServices
 *
 * @package Hanzo\Bundle\AxBundle\Actions
 */
class ECommerceServices extends SoapService
{
    /**
     * syncronize an item
     * @param object $data xmlformat:
     *    <item xmlns="http://schemas.pompdelux.dk/webintegration/item">
     *      <InventTable>
     *        <ItemGroupId>G_Access,LG_Access</ItemGroupId>
     *        <ItemGroupName>Girl Accessories</ItemGroupName>
     *        <ItemId>Ada HAT</ItemId>
     *        <WebEnabled>true</WebEnabled>
     *        <ItemName>Ada HAT</ItemName>
     *        <ItemType>Item</ItemType>
     *        <NetWeight>0.00</NetWeight>
     *        <BlockedDate>1990-01-01</BlockedDate>
     *        <InventDim>
     *          <InventColorId>Dark Grey Melange</InventColorId>
     *          <InventSizeId>50</InventSizeId>
     *        </InventDim>
     *        <InventDim>
     *          <InventColorId>Violet</InventColorId>
     *          <InventSizeId>50</InventSizeId>
     *        </InventDim>
     *        <InventDim>
     *          ...
     *        </InventDim>
     *        <WebDomain>COM</WebDomain>
     *        <WebDomain>DK</WebDomain>
     *        <WebDomain>SalesDK</WebDomain>
     *        <Sales>
     *          <Price>0.00</Price>
     *          <UnitId>Stk.</UnitId>
     *        </Sales>
     *        <WashInstruction/>
     *        <IsVoucher/>
     *      </InventTable>
     *    </item>
     *
     * @return object SyncItemResult
     */
    public function SyncItem($data)
    {
        // note, this file is generated through admin, and is essential when we need to ensure that all products have
        // the same id across all databases - without having to make a db-lookup for each one.
        //
        // also note that if the product is _not_ found, we let Propel handle autoincrement on the product id.
        require $this->productMapping;

        $errors = [];
        $item = $data->item->InventTable;

        if (!$item->ItemGroupId OR !$item->ItemId OR !$item->ItemName) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no ItemGroupId OR ItemId OR ItemName given');

            return self::responseStatus('Error', 'SyncItemResult', [
                'no ItemGroupId OR ItemId OR ItemName given'
            ]);
        }

        // only allow web enabled products to go into the database.
        if ($item->WebEnabled != 1) {
            $errors = [
                'InventId: ' . $item->ItemId,
                'WebEnabled set to "' . $item->WebEnabled . '" it should be set to "1"'
            ];
            $this->logger->info(__METHOD__.' '.__LINE__.': WebEnabled set to "' . $item->WebEnabled . '", it should be set to "1"', $errors);

            return self::responseStatus('Error', 'SyncItemResult', $errors);
        }

        // domain check
        if (!$item->WebDomain) {
            $errors = [
                'InventId: ' . $item->ItemId,
                'WebDomain empty, we need at lest one domain to create/update the product.'
            ];
            $this->logger->info(__METHOD__.' '.__LINE__.': No WebDomain parameters', $errors);

            return self::responseStatus('Error', 'SyncItemResult', $errors);
        }

        if (!is_array($item->WebDomain)) {
            $item->WebDomain = [$item->WebDomain];
        }

        if (!is_array($item->InventDim)) {
            $item->InventDim = [$item->InventDim];
        }

        // ....................
        // .....<ze code>......
        // ....................

        if ('da_DK' !== $this->request->getLocale()) {
            $master = strtolower(trim($item->ItemId));

            // Check master
            $missing = [];
            if (!array_key_exists($master, $products_id_map)) {
                $missing[] = $master;
            }

            // Run through variants
            foreach ($item->InventDim as $entry) {
                $variant = strtolower(trim($item->ItemId . ' ' . $entry->InventColorId . ' ' . $entry->InventSizeId));

                if (! array_key_exists($variant, $products_id_map)) {
                    $missing[] = $variant;
                }
            }

            if (count($missing)) {
                $mailBody = 'Der opstod en fejl under produktsynkroniseringen og synkroniseringen stoppede! Følgende produkter er ikke uploadet i den danske version.\n\r\n\r';

                // Mail credentials
                $mailer = $this->hanzo->container->get('mail_manager');
                $mailer->setTo('it-drift@pompdelux.dk', 'POMPdeLUX IT afdeling');

                // Run through
                foreach($missing AS $missingItem) {

                    $errors = [
                      'InventId: ' . $missingItem,
                    ];

                    $mailBody .= 'InventId: ' . $missingItem . '\n\r';
                }

                // Send mail
                $mailer->setBody($mailBody);
                $mailer->send();

                // Log
                $this->logger->info(__METHOD__.' '.__LINE__.': No WebDomain parameters', $errors);
                return self::responseStatus('Error', 'SyncItemResult', $errors);
            }
        }

        try {
            // loop over all items
            $index = 0;
            foreach ($item->InventDim as $entry) {
                /**
                 * create master product
                 */
                if ($index == 0) {
                    $sku = trim($item->ItemId);
                    $product = ProductsQuery::create()->findOneBySku($sku);

                    // products 2 category
                    $categories = explode(',', $item->ItemGroupId);

                    // cleanup elements
                    array_walk($categories, function(&$value, $key) use (&$categories) {
                        $value = trim($value);
                        if (empty($value)) {
                            unset ($categories[$key]);
                        }
                    });

                    $categories = CategoriesQuery::create()
                        ->filterByContext($categories)
                        ->find()
                    ;

                    if (0 == $categories->count()) {
                        $errors = [
                            'InventId: ' . $item->ItemId,
                            'No category/categories found for ItemGroupId: '.$item->ItemGroupId.'.',
                        ];
                        break;
                    }

                    $primary_category_id = $categories->getFirst()->getId();

                    if (!$product instanceof Products) {
                        $product = new Products();
                        if (isset($products_id_map) && isset($products_id_map[strtolower($sku)])) {
                            $product->setId($products_id_map[strtolower($sku)]);
                        }
                        $product->setSku($sku);
                        $product->setHasVideo(false);

                        if (isset($item->IsVoucher) && $item->IsVoucher) {
                            $product->setIsVoucher(true);
                        }

                        // products i18n
                        $languages = LanguagesQuery::create()->find();
                        foreach ($languages as $language) {
                            $i18n = new ProductsI18n();
                            $i18n->setLocale($language->getLocale());
                            $i18n->setTitle($item->ItemName);
                            $i18n->setContent($item->ItemName);
                            $product->addProductsI18n($i18n);
                        }
                    } else {
                        foreach ($product->getProductsI18ns() as $translation) {
                            $translation->setTitle($item->ItemName);
                        }

                        // We have to clear old category bindings before attaching new ones.
                        ProductsToCategoriesQuery::create()
                            ->findByProductsId($product->getId(), Propel::getConnection(null, Propel::CONNECTION_WRITE))
                            ->delete()
                        ;
                        $product->clearProductsToCategoriess();
                    }

                    // Bind product to categories
                    foreach ($categories as $category) {
                        $p2c = new ProductsToCategories();
                        $p2c->setCategoriesId($category->getId());
                        $product->addProductsToCategories($p2c);
                    }

                    $product->setRange(substr($item->ItemId, -4));
                    $product->setUnit(trim('1 ' .$item->Sales->UnitId));
                    $product->setPrimaryCategoriesId($primary_category_id);

                    $washing = $item->WashInstruction;
                    if (is_scalar($washing) && !empty($washing)) {
                        $product->setWashing($washing);
                    }

                    // save ze product
                    $product->save();
                }

                // create product variations
                $sku = trim($item->ItemId . ' ' . $entry->InventColorId . ' ' . $entry->InventSizeId);
                $product = ProductsQuery::create()->findOneBySku($sku);

                if (!$product instanceof Products) {
                    $product = new Products();
                    if (isset($products_id_map) && isset($products_id_map[strtolower($sku)])) {
                        $product->setId($products_id_map[strtolower($sku)]);
                    }
                    $product->setSku($sku);
                    $product->setMaster($item->ItemId);
                    $product->setColor($entry->InventColorId);
                    $product->setSize($entry->InventSizeId);
                }

                $product->setRange(substr($item->ItemId, -4));
                $product->setUnit(trim('1 ' .$item->Sales->UnitId));
                $product->setHasVideo(false);
                $product->setIsOutOfStock(false);
                $product->setIsActive(true);

                $washing = $item->WashInstruction;
                if (is_scalar($washing) && !empty($washing)) {
                    $product->setWashing($washing);
                }

                if (isset($item->IsVoucher) && $item->IsVoucher) {
                    $product->setIsVoucher(true);
                }

                $product->save();
                $index++;
            }

        } catch(Exception $e) {
            $errors = [
                //'InventId: ' . $item->ItemId,
                $e->getMessage(),
            ];
        }


        // ....................
        // .....</ze code>.....
        // ....................

        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': SyncItem failed with the following error(s)', $errors);

            return self::responseStatus('Error', 'SyncItemResult', $errors);
        }

        return self::responseStatus('Ok', 'SyncItemResult');
    }

    /**
     * syncronize a products price(s)
     *
     * @param object $data xmlformat:
     *    <priceList xmlns="http://schemas.pompdelux.dk/webintegration/pricelist">
     *      <ItemId>Ada HAT</ItemId>
     *      <SalesPrice>
     *        <AmountCur>20.00</AmountCur>
     *        <Currency>DKK</Currency>
     *        <CustAccount>DKK</CustAccount>
     *        <InventColorId>Dark Grey Melange</InventColorId>
     *        <InventSizeId>50</InventSizeId>
     *        <PriceUnit>1.00</PriceUnit>
     *        <Quantity>1.00</Quantity>
     *        <UnitId>Stk.</UnitId>
     *      </SalesPrice>
     *      <SalesPrice>
     *      ...
     *      </SalesPrice>
     *    </priceList>
     * @return object SyncPriceListResult
     */
    public function SyncPriceList($data)
    {
        $errors = [];
        $prices = $data->priceList;

        if (!$prices->ItemId) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no ItemId given');
            return self::responseStatus('Error', 'SyncPriceListResult', ['no ItemId given']);
        }

        if (empty($prices->SalesPrice)) {
            Tools::log($prices);
            die();
        }

        if (!is_array($prices->SalesPrice)) {
            $prices->SalesPrice = [$prices->SalesPrice];
        }

        // ....................
        // .....<ze code>......
        // ....................

        $domains = [];
        foreach(DomainsQuery::create()->find() as $domain) {
            $domains[$domain->getDomainKey()] = $domain->getId();
        }

        $products = [
            "{$prices->ItemId}" => [
                'product' => ProductsQuery::create()->findOneBySku($prices->ItemId),
                'prices' => []
            ]
        ];

        foreach ($prices->SalesPrice as $entry)
        {
            $key = $prices->ItemId . ' ' . $entry->InventColorId . ' ' . $entry->InventSizeId;

            $domain = $this->getDomainKeyFromCurrencyKey($entry);

            if (empty($domain)) {
                $errors[] = sprintf("No domain setup for '%s'", $key);
                continue;
            }

            // always have a from date on prices
            if (empty($entry->PriceDate)) {
                $entry->PriceDate = strtotime('-1 year');
            }

            if (empty($products[$key])) {
                $product = ProductsQuery::create()
                    ->filterByMaster($prices->ItemId)
                    ->filterByColor($entry->InventColorId)
                    ->filterBySize($entry->InventSizeId)
                    ->findOne()
                ;

                // catch unknown products
                if (!$product instanceof Products) {
                    $errors[] = sprintf("'%s' does not exist in products", $key);
                    continue;
                }

                $products[$key]['product'] = $product;
            }

            $thePrice = $entry->AmountCur;
            $vat = 0;
            if ($domain['vat'] > 0) {
                // 100/(1+(25/100) = 80
                $thePrice = $thePrice / (1 + ($domain['vat'] / 100));
                $vat      = $entry->AmountCur - $thePrice;
            }


            // fix decimals in db...
            $vat      = number_format($vat, 4, '.', '');
            $thePrice = number_format($thePrice, 4, '.', '');

            // perhaps we could skip this, pompdelux does not use alternative prices pr. variant
            $products[$key]['prices'][] = [
                'domain'    => $domain['domain'],
                'currency'  => $domain['currency'],
                'amount'    => $thePrice,
                'vat'       => $vat,
                'from_date' => $entry->PriceDate,
                'to_date'   => (isset($entry->PriceDateTo) ? $entry->PriceDateTo : null),
            ];

            // this is here to maintain price info on the master product also
            $products[$prices->ItemId]['prices'][$domain['domain'].$entry->PriceDate] = [
                'domain'    => $domain['domain'],
                'currency'  => $domain['currency'],
                'amount'    => $thePrice,
                'vat'       => $vat,
                'from_date' => $entry->PriceDate,
                'to_date'   => (isset($entry->PriceDateTo) ? $entry->PriceDateTo : null),
            ];
        }

        foreach ($products as $item) {
            $product = $item['product'];
            $prices = $item['prices'];

            if (!$product instanceof Products) {
                continue;
            }

            ProductsDomainsPricesQuery::create()
                ->findByProductsId($product->getId(), Propel::getConnection(null, Propel::CONNECTION_WRITE))
                ->delete();

            $product->clearProductsDomainsPricess();

            // products 2 domain
            $collection = new PropelCollection();
            foreach ($prices as $price) {
                $data = new ProductsDomainsPrices();
                $data->setDomainsId($domains[$price['domain']]);
                $data->setPrice($price['amount']);
                $data->setVat($price['vat']);
                $data->setFromDate($price['from_date']);
                if ($price['to_date']) {
                    $data->setToDate($price['to_date']);
                }
                $data->setCurrencyId($price['currency']);
                $collection->prepend($data);
            }

            $product->setProductsDomainsPricess($collection);
            $product->save();
        }

        // ....................
        // .....</ze code>.....
        // ....................


        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': SyncPriceList failed with the following error(s)', $errors);
            return self::responseStatus('Error', 'SyncPriceListResult', $errors);
        }

        return self::responseStatus('Ok', 'SyncPriceListResult');
    }

    /**
     * inventory syncronization
     *
     * @param object $data xmlformat:
     *    <inventoryOnHand xmlns="http://schemas.pompdelux.dk/webintegration/inventoryOnhand">
     *      <InventSum>
     *        <ItemId>Ada HAT</ItemId>
     *        <InventDim>
     *          <InventColorId>Dark Grey Melange</InventColorId>
     *          <InventSizeId>50</InventSizeId>
     *          <InventQtyAvailOrdered>0.00</InventQtyAvailOrdered>
     *          <InventQtyAvailOrderedDate>2012-08-07</InventQtyAvailOrderedDate>
     *          <InventQtyAvailPhysical>0.00</InventQtyAvailPhysical>
     *          <InventQtyPhysicalOnhand>0.00</InventQtyPhysicalOnhand>
     *        </InventDim>
     *        <InventDim>
     *          ...
     *        </InventDim>
     *     </InventSum>
     *    </inventoryOnHand>
     * @return object
     */
    public function SyncInventoryOnHand($data)
    {
        $errors = [];
        $stock = $data->inventoryOnHand->InventSum;

        if (!$stock->ItemId) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no ItemId given');

            return self::responseStatus('Error', 'SyncInventoryOnHandResult', ['no ItemId given']);
        }

        if (empty($stock->InventDim)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': No InventDim found on ItemId: ' . $stock->ItemId);

            return self::responseStatus('Error', 'SyncInventoryOnHandResult', ['No InventDim found on ItemId: ' . $stock->ItemId]);
        }

        $master = ProductsQuery::create()->findOneBySku($stock->ItemId);
        if (!$master instanceof Products) {
            $this->logger->info(__METHOD__.' '.__LINE__.': Unknown product, ItemId: ' . $stock->ItemId);

            return self::responseStatus('Error', 'SyncInventoryOnHandResult', ['Unknown ItemId: ' . $stock->ItemId]);
        }

        // ....................
        // .....<ze code>......
        // ....................

        // if there is only one item it is not send as an array
        if (!is_array($stock->InventDim)) {
            $stock->InventDim = [$stock->InventDim];
        }

        // ----------------------------------------------------------------
        // 1. calculate reservations to be subtracted from the stock update
        // ----------------------------------------------------------------

        /** @var \Hanzo\Bundle\StockBundle\Stock $stock_service */
        $stock_service = $this->hanzo->container->get('stock');

        $products = [];
        foreach($stock->InventDim as $item) {
            $key = $stock->ItemId . ' ' . $item->InventColorId . ' ' . $item->InventSizeId;

            if (empty($products[$key])) {
                $product = ProductsQuery::create()
                    ->filterByMaster($stock->ItemId)
                    ->filterByColor($item->InventColorId)
                    ->filterBySize($item->InventSizeId)
                    ->findOne()
                ;

                if (!$product instanceof Products) {
                    $errors[] = "{$key} not a known product";
                    continue;
                }

                $products[$key]['product'] = $product;
                $products[$key]['qty_in_use'] = $stock_service->getProductReservations($product->getId());
            }

            $item->InventQtyAvailOrderedDate = $item->InventQtyAvailOrderedDate
                ? $item->InventQtyAvailOrderedDate
                : 0
            ;

            // If the ordered date is today, or less than,
            // we do not add "ordered" inventory records.
            if ((0 < (int) $item->InventQtyAvailOrdered) &&
                $item->InventQtyAvailOrderedDate &&
                (str_replace('-', '', $item->InventQtyAvailOrderedDate) <= date('Ymd'))
            ) {
                $item->InventQtyAvailOrdered = 0;
                $item->InventQtyAvailOrderedDate = 0;
            }

            // never allow negative stock
            $physical = (int) $item->InventQtyAvailPhysical;
            if ($physical < 0) {
                $physical = 0;
            }

            $stock_data = [
                'onhand'  => $physical,
                'ordered' => (int) $item->InventQtyAvailOrdered,
            ];

            foreach ($stock_data as $k => $value) {
                // subtract "reservations"
                if ($products[$key]['qty_in_use']) {
                    if ($products[$key]['qty_in_use'] >= $value) {
                        $products[$key]['qty_in_use'] = $products[$key]['qty_in_use'] - $value;
                        unset($stock_data[$k]);
                        continue;
                    }

                   $stock_data[$k] = $value - $products[$key]['qty_in_use'];
                   $products[$key]['qty_in_use'] = 0;
                }
            }

            if (empty($products[$key]['inventory'])) {
                $products[$key]['inventory'] = [];
                $products[$key]['total']     = 0;
            }

            // handle any delayed stock
            if (!empty($stock_data['ordered'])) {
                if (empty($products[$key]['inventory'][$item->InventQtyAvailOrderedDate])) {
                    $products[$key]['inventory'][$item->InventQtyAvailOrderedDate] = [
                        'date'     => $item->InventQtyAvailOrderedDate,
                        'quantity' => 0,
                    ];
                }

                $products[$key]['total'] += $stock_data['ordered'];
                $products[$key]['inventory'][$item->InventQtyAvailOrderedDate]['quantity'] += $stock_data['ordered'];
            }

            // handle stock on-hand
            if (isset($stock_data['onhand'])) {
                if (empty($products[$key]['inventory']['onhand'])) {
                    $products[$key]['inventory']['onhand'] = [
                        'date'     => '2000-01-01',
                        'quantity' => 0,
                    ];
                }

                $products[$key]['total'] += $stock_data['onhand'];
                $products[$key]['inventory']['onhand']['quantity'] += $stock_data['onhand'];
            }
        }

        // ----------------------------------------------------------------
        // 2. update the stock levels for the entire style
        // ----------------------------------------------------------------

        $allout = true;
        foreach ($products as $item) {
            $product = $item['product'];

            if (isset($item['inventory'])) {
                $isOut = ($item['total'] > 0)
                    ? false
                    : true;

                // inventory to products
                $stock_service->setLevels($product->getId(), $item['inventory']);
                $stock_service->setStockStatus($isOut, $product);

                if (false === $isOut) {
                    $allout = false;
                }
            } else {
                $stock_service->setStockStatus(true, $product);
            }
        }

        $stock_service->setStockStatus($allout, $master);

        // purge varnish
        $this->event_dispatcher->dispatch('product.stock.zero', new FilterCategoryEvent($master, null, Propel::getConnection(null, Propel::CONNECTION_WRITE)));

        // set sync flag
        if (isset($stock->LastInCycle) && $stock->LastInCycle) {
            $c = $this->hanzo->container;
            $c->get('pdl.phpredis.permanent')->hset(
                'stock.sync.time',
                strtoupper($c->get('kernel')->getSetting('domain_key')),
                time()
            );
        }

        // ....................
        // .....</ze code>.....
        // ....................

        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': SyncInventoryOnHand failed with the following error(s)', $errors);
            return self::responseStatus('Error', 'SyncInventoryOnHandResult', $errors);
        }

        return self::responseStatus('Ok', 'SyncInventoryOnHandResult');
    }


    /**
     * customer syncronizer
     *
     * @param object $data xmlformat:
     * <customer>
     *   <CustTable>
     *     <AccountNum>109381</AccountNum>
     *     <InitialsId></InitialsId>
     *     <CustName>Mamarie Karlsson</CustName>
     *     <AddressStreet>marknadsvøgen 7</AddressStreet>
     *     <AddressCity>bjærketorp</AddressCity>
     *     <AddressZipCode>51994</AddressZipCode>
     *     <AddressCountryRegionId>DK</AddressCountryRegionId>
     *     <CustCurrencyCode>DKK</CustCurrencyCode>
     *     <Email>mariedelice@hotmail.com</Email>
     *     <Phone>004632060004</Phone>
     *     <PhoneLocal></PhoneLocal>
     *     <PhoneMobile></PhoneMobile>
     *     <TeleFax></TeleFax>
     *     <SalesDiscountPercent></SalesDiscountPercent>
     *   </CustTable>
     * </customer>
     * @return object
     */
    public function SyncCustomer($data)
    {
        /**
         * customer groups is as following:
         *  id: >= 10,001 < 15,000 = consultants = 2
         *  id: >= 15.000 < 20,000 = employees   = 3
         *  id: >= 20,000          = customers   = 1
         */

        $errors = [];

        if (!$data instanceof \stdClass || empty($data->customer->CustTable)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': not a customer object');

            return self::responseStatus('Error', 'SyncCustomerResult', ['not a customer object']);
        }

        $data = $data->customer->CustTable;

        // ....................
        // .....<ze code>......
        // ....................

        $country = CountriesQuery::create()->findOneByIso2($data->AddressCountryRegionId);

        if (!$country instanceof Countries) {
            $this->logger->info(__METHOD__.' '.__LINE__.': unknown country reference: ' . $data->AddressCountryRegionId . ' for account: #' . $data->AccountNum);

            return self::responseStatus('Error', 'SyncCustomerResult', ['unknown country reference: ' . $data->AddressCountryRegionId . ' for account: #' . $data->AccountNum]);
        }

        $group_id = ($data->AccountNum < 15000 ? 2 : ($data->AccountNum < 20000 ? 3 : 1));

        $customer = CustomersQuery::create()->findOneById($data->AccountNum);
        if (!$customer instanceof Customers) {
            $customer = new Customers();
            $customer->setId($data->AccountNum);
            // we never update passwords
            $customer->setPassword(sha1($data->Phone));
            $customer->setPasswordClear($data->Phone);
        }

        if ($group_id > 1) {
            if ($customer->isNew()) {
                $consultant = new Consultants();
            } else {
                $consultant = $customer->getConsultants();
                if (!$consultant instanceof Consultants) {
                    $consultant = new Consultants();
                }
            }
            $consultant->setInitials($data->InitialsId);
            $customer->setConsultants($consultant);
        }

        $customer->setGroupsId($group_id);

        $names = explode(' ', $data->CustName);
        $first_name = array_shift($names);
        $last_names = implode(' ', $names);

        $customer->setFirstName($first_name);
        $customer->setLastName($last_names);

        $customer->setEmail($data->Email);
        $customer->setPhone($data->Phone);

        if ($data->SalesDiscountPercent) {
            $customer->setDiscount((float) $data->SalesDiscountPercent * -1);
        }

        // create or update primary (payment) address
        if ($customer->isNew()) {
            $address = new Addresses();
            $address->setType('payment');
        } else {
            $address = AddressesQuery::create()
                ->filterByType('payment')
                ->findOneByCustomersId($customer->getId())
            ;
        }

        $address->setFirstName($first_name);
        $address->setLastName($last_names);
        $address->setAddressLine1($data->AddressStreet);
        $address->setPostalCode($data->AddressZipCode);
        $address->setCity($data->AddressCity);
        $address->setCountry($country->getName());
        $address->setCountriesId($country->getId());

        try {
            $address->geocode();
        } catch (Exception $e) { /* ignore this, we can live with addresses without geo locations */ }

        if ($customer->isNew()) {
            $customer->addAddresses($address);
        } else {
            $address->save();
        }

        try {
            $customer->save();

            // user created, add him/her to phplist
            if ($group_id > 1) {
                $domain_key = $this->hanzo->get('core.domain_key');
                $newsletter_lists = $this->hanzo->get('phplist.lists');
                if (isset($newsletter_lists[$domain_key])) {
                    $newsletter = new NewsletterApi();
                    $result = $newsletter->subscribe($customer->getEmail(), $newsletter_lists[$domain_key]);
                }
            }

        } catch (\Exception $e) {
            $errors[] = 'Cound not create or update account.';
            $errors[] = $e->getMessage();
        }


        // ....................
        // .....</ze code>.....
        // ....................

        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': SyncCustomerResult failed with the following error(s)', $errors);

            return self::responseStatus('Error', 'SyncCustomerResult', $errors);
        }

        return self::responseStatus('Ok', 'SyncCustomerResult');
    }


    /**
     * set status to invoiced on a specific order.
     *
     * @todo
     * @param object $data
     * @return object SyncSalesOrderResult
     */
    public function SyncSalesOrder($data){}


    /**
     * delete a specific sales order.
     *
     * (17:05:32) Enrique: virker det fra ax?
     * (17:05:41) Heinrich: kommer det aldrig til
     * (17:05:46) Enrique: ok?
     * (17:05:53) Enrique: men der er bare en DeleteSalesOrder
     * (17:05:54) Heinrich: det bruger vi ikke
     *
     * @param object $data
     * @return object DeleteSalesOrderResult
     */
    public function DeleteSalesOrder($data)
    {
        $errors = [];
        if (empty($data->eOrderNumber)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no eOrderNumber given.');
            return self::responseStatus('Error', 'DeleteSalesOrderResult', ['no eOrderNumber given.']);
        }

        // ....................
        // .....<ze code>......
        // ....................

        $order = OrdersQuery::create()->findOneById($data->eOrderNumber);

        if (!$order instanceof Orders) {
            $msg = 'no order found with eOrderNumber "' . $data->eOrderNumber . '".';
            $this->logger->info($msg);
            return self::responseStatus('Error', 'DeleteSalesOrderResult', [$msg]);
        }

        if ($order->getPaymentGatewayId()) {
            try {
                $this->ordersService->deleteOrder($order);
            } catch (\Exception $e) {
                $this->logger->info(__METHOD__.' '.__LINE__.': Could not cancel payment: "'.$e->getMessage().'"');
                return self::responseStatus('Error', 'DeleteSalesOrderResult', ['Could not cancel payment: "'.$e->getMessage().'"']);
            }
        }


        // ....................
        // .....</ze code>.....
        // ....................

        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': DeleteSalesOrderResult failed with the following error(s)', $errors);
            return self::responseStatus('Error', 'DeleteSalesOrderResult', $errors);
        }

        return self::responseStatus('Ok', 'DeleteSalesOrderResult');
    }


    /**
     * capture or refund an amount on an order
     * on success we update the order status to 4, and sends an email
     *
     * @param stdClass $data
     *   $data->eOrderNumber; // string
     *   $data->amount;       // decimal
     *   $data->initials;     // string
     *
     * @return object SalesOrderCaptureOrRefundResult
     *
     * @see ECommerceServices::SalesOrderLockUnlock
     */
    public function SalesOrderCaptureOrRefund ($data)
    {
        $errors = [];

        if (empty($data->eOrderNumber)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no eOrderNumber given.');

            return self::responseStatus('Error', 'SalesOrderCaptureOrRefundResult', ['no eOrderNumber given.']);
        }

        $order = OrdersQuery::create()->findOneById($data->eOrderNumber);

        if (!$order instanceof Orders) {
            $msg = 'no order found with eOrderNumber "' . $data->eOrderNumber . '".';
            $this->logger->info($msg);

            return self::responseStatus('Error', 'SalesOrderCaptureOrRefundResult', [$msg]);
        }

        // ....................
        // .....<ze code>......
        // ....................

        $this->sendStatusMail = true;
        if ($data->amount && ($data->amount > 0)) {
            // capture
            if ($order->getPaymentGatewayId()) {
                $result = $this->SalesOrderCapture($data, $order);
                if ($result !== true) {
                    $errors = $result;
                }
            }
        } else {
            // refund
            if (($data->amount < 0) && $order->getPaymentGatewayId()) {
                $result = $this->SalesOrderRefund($data, $order);
                if ($result !== true) {
                    $errors = $result;
                }
            }
        }
        // ....................
        // .....</ze code>.....
        // ....................


        if (count($errors)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': SalesOrderCaptureOrRefundResult failed with the following error(s)', $errors);
            $this->timer->logAll('Time spend on order: #'.$order->getId());

            return self::responseStatus('Error', 'SalesOrderCaptureOrRefundResult', $errors);
        }

        $this->SalesOrderLockUnlock((object) [
            'eOrderNumber' => $data->eOrderNumber,
            'orderStatus'  => 4,
            'sendMail'     => 0,
        ]);

        $this->timer->logAll('Time spend on order: #'.$order->getId());

        return self::responseStatus('Ok', 'SalesOrderCaptureOrRefundResult');
    }


    /**
     * change status of an order, the method also sends statuschanges on request.
     *
     * @param \stdCLass|object $data
     *   $data->eOrderNumber; // string
     *   $data->orderStatus;  // integer
     *   $data->sendMail;     // bool
     *
     * @return object SalesOrderLockUnlockResult
     */
    public function SalesOrderLockUnlock ($data)
    {
        if (empty($data->eOrderNumber)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no eOrderNumber given.');

            return self::responseStatus('Error', 'SalesOrderLockUnlockResult', ['no eOrderNumber given.']);
        }

        if ($data->orderStatus < 1 || $data->orderStatus > 4) {
            $this->logger->info(__METHOD__.' '.__LINE__.': order status id #' . $data->orderStatus . ' is not known.');

            return self::responseStatus('Error', 'SalesOrderLockUnlockResult', ['order status id #' . $data->orderStatus . ' is not known.']);
        }

        $order = OrdersQuery::create()->findOneById($data->eOrderNumber);

        if (!$order instanceof Orders) {
            $this->logger->info(__METHOD__.' '.__LINE__.': order #' . $data->eOrderNumber . ' does not exist.');

            return self::responseStatus('Error', 'SalesOrderLockUnlockResult', ['order #' . $data->eOrderNumber . ' does not exist.']);
        }

        if ($order->getInEdit()) {
            $this->logger->info(__METHOD__.' '.__LINE__.': order #' . $data->eOrderNumber . ' cannot be locked, the order is "in edit".');

            return self::responseStatus('Error', 'SalesOrderLockUnlockResult', ['order #' . $data->eOrderNumber . ' cannot be locked, the order is "in edit".']);
        }

        // ....................
        // .....<ze code>......
        // ....................

        $status_map = [
            1 => Orders::STATE_PENDING,
            2 => Orders::STATE_BUILDING,
            3 => Orders::STATE_BEING_PROCESSED,
            4 => Orders::STATE_SHIPPED,
        ];

        if ($data->sendMail) {
            $this->timer->reset();
            try {
                $name = trim($order->getFirstName() . ' ' . $order->getLastName());
                $mailer = $this->hanzo->container->get('mail_manager');
                $mailer->setTo($order->getEmail(), $name);
                $mailer->setMessage('order.status_processing', [
                    'order_id' => $order->getId()
                ]);
                $mailer->send();
            } catch (Exception $e) {
                Tools::log($e->getMessage());
            }
            $this->timer->lap('time spend sending email');
        }

        $order->setState($status_map[$data->orderStatus]);

        // the order is considered finished when shipped
        if (4 == $data->orderStatus) {
            $order->setFinishedAt(time());
        }

        $order->save();

        // ....................
        // .....</ze code>.....
        // ....................

        return self::responseStatus('Ok', 'SalesOrderLockUnlockResult');
    }


    /**
     * Attach a document to a sales order.
     *
     * @param object $data
     * @return object SalesOrderAddDocumentResult
     */
    public function SalesOrderAddDocument($data)
    {
        if (empty($data->eOrderNumber)) {
            $this->logger->info(__METHOD__.' '.__LINE__.': no eOrderNumber given.');

            return self::responseStatus('Error', 'SalesOrderAddDocumentResult', ['no eOrderNumber given.']);
        }

        $order = OrdersQuery::create()
            ->filterById($data->eOrderNumber)
            ->count()
        ;

        if (!$order) {
            $this->logger->info(__METHOD__.' '.__LINE__.': order #' . $data->eOrderNumber . ' does not exist.');

            return self::responseStatus('Error', 'SalesOrderAddDocumentResult', ['order #' . $data->eOrderNumber . ' does not exist.']);
        }

        // ....................
        // .....<ze code>......
        // ....................

        $exists = OrdersAttributesQuery::create()
            ->filterByOrdersId($data->eOrderNumber)
            ->filterByNs('attachment')
            ->filterByCValue($data->fileName)
            ->count()
        ;

        if ($exists) {
            return self::responseStatus('Error', 'SalesOrderAddDocumentResult', ['Document "'.$data->fileName.'" already exists for order #'.$data->eOrderNumber]);
        }

        $attachment_index = OrdersAttributesQuery::create()
            ->filterByOrdersId($data->eOrderNumber)
            ->filterByNs('attachment')
            ->count()
        ;

        $attribute = new OrdersAttributes();
        $attribute->setOrdersId($data->eOrderNumber);
        $attribute->setNs('attachment');
        $attribute->setCKey('attachment_'.$attachment_index);
        $attribute->setCValue($data->fileName);
        $attribute->save();

        // ....................
        // .....</ze code>.....
        // ....................

        return self::responseStatus('Ok', 'SalesOrderAddDocumentResult');
    }


    /**
     * building responce message
     *
     * @param string $status
     * @param string $var message type
     * @param array $messages array of messages to send
     *
     * @return object
     */
    protected function responseStatus ($status, $var, $messages = [])
    {
        $response = (object) [
            $var => (object) [
                'Status'  => new \SoapVar($status, \XSD_STRING, "", "http://schemas.pompdelux.dk/webintegration/ResponseStatus"),
                'Message' => [],
            ]
        ];

        foreach ($messages as $message) {
            $response->{$var}->Message[] = new \SoapVar($message, \XSD_STRING, "", "http://schemas.pompdelux.dk/webintegration/ResponseStatus");
        }

        return $response;
    }


    /**
     * SalesOrderCapture - split out from SalesOrderCaptureOrRefund
     *
     * @param \stdClass $data  soap data object
     * @param Orders    $order order object
     *
     * @return mixed true on success array of errors on error
     *
     * @see ECommerceServices::SalesOrderCaptureOrRefund
     */
    protected function SalesOrderCapture($data, Orders $order)
    {
        $error = [];

        $provider = strtolower($order->getBillingMethod());
        if (in_array($provider, ['paybybill', 'gothia', 'manualpayment', 'invoicepayment'])) {
            return true;
        }

        if ('' == $provider) {
            return [
                'no billing method found for order #'.$data->eOrderNumber,
                'line : ' . __LINE__
            ];
        }

        $attributes = $order->getAttributes();

        // work arround for iDEAL payments not supporting capture
        if (('pensio' == $provider) &&
            (isset($attributes->payment)) &&
            (isset($attributes->payment->nature)) &&
            ('idealpayment' == strtolower($attributes->payment->nature))
        ) {
            $this->timer->reset();
            $total  = $order->getTotalPrice();
            $amount = str_replace(',', '.', $data->amount);

            if ($amount < $total) {
                $gateway = $this->hanzo->container->get('payment.'.$provider.'api');
                $diff = $total - $amount;

                try{
                    $gateway->call()->refund($order, $diff);
                } catch (PaymentApiCallException $e) {
                    $error = [
                        'cound not refund "'.$diff.'" to order #'.$data->eOrderNumber,
                        'error: ' . $e->getMessage(),
                        'line : ' . __LINE__
                    ];
                }
            }
            $this->timer->lap('time in '.$provider.' gateway');

            return count($error) ? $error : true;
        }

        try {
            $gateway = $this->hanzo->container->get('payment.'.$provider.'api');

            $this->timer->reset();
            try {
                $response = $gateway->call()->capture($order, $data->amount);
                $result = $response->debug();
            } catch (PaymentApiCallException $e) {
                $error = [
                    'cound not capture order #' . $data->eOrderNumber,
                    'error: ' . $e->getMessage(),
                    'line : ' . __LINE__
                ];
            }
            $this->timer->lap('time in '.$provider.' gateway');

            if (empty($result['status']) || (!in_array($result['status'], [true, 'ACCEPTED'], true))) {
                $error = [
                    'cound not capture order #' . $data->eOrderNumber,
                    'error: '.(empty($result['status_description']) ? 'unknown error' : $result['status_description'])
                ];
            }
        } catch (Exception $e) {
            $error = [
                'cound not capture order #' . $data->eOrderNumber,
                'error: ' . $e->getMessage(),
                'line : ' . __LINE__
            ];
        }

        if (count($error)) {
            Tools::log($error);
            return $error;
        }

        return true;
    }


    /**
     * SalesOrderRefund - split out from SalesOrderCaptureOrRefund
     *
     * @param array  $data  refund data
     * @param Orders $order order object
     *
     * @return mixed true on success array of errors on error
     *
     * @see ECommerceServices::SalesOrderCaptureOrRefund
     */
    protected function SalesOrderRefund($data, Orders $order)
    {
        $setStatus = false;
        $errors = [];

        $provider = strtolower($order->getBillingMethod());
        $gateway = $this->hanzo->container->get('payment.'.$provider.'api');
        $domain = strtoupper($order->getAttributes()->global->domain_key);

        $doSendError = false;
        try {
            $call = $gateway->call();

            if (method_exists($call, 'refund'))  {
                $this->timer->reset();
                $response = $call->refund($order, ($data->amount * -1));
                $result   = $response->debug();
                $this->timer->lap('time in '.$provider.' gateway');
            } else {
                // if no refund method is implemented, we just have to accept that the refund succeded
                $result = ['status' => true];
            }

            if (!in_array($result['status'], [0, true, 1, '1', 'ACCEPTED'], true)) {
                $doSendError = true;
                $errors = [
                    'cound not refund order #' . $data->eOrderNumber,
                    'error: ' . $result['status_description'],
                    'line : ' . __LINE__
                ];
            } else {
                $name = $order->getFirstName() . ' ' . $order->getLastName();
                $parameters = [
                    'order_id' => $order->getId(),
                    'name'     => $name,
                    'amount'   => $data->amount,
                ];

                $this->timer->reset();
                $mailer = $this->hanzo->container->get('mail_manager');

                if (in_array($domain, ['COM'])) {
                    $mailer->setMessage('order.credited', $parameters, 'en_GB');
                } else {
                    $mailer->setMessage('order.credited', $parameters);
                }

                $bcc = Tools::getBccEmailAddress('order', $order);
                if ($bcc) {
                    $mailer->setBcc($bcc);
                }

                $mailer->setTo($order->getEmail(), $name);
                $mailer->send();
                $this->timer->lap('time sending emails');

                $this->sendStatusMail = false;
            }

        } catch (Exception $e) {
            $doSendError = true;
            $errors = [
                'cound not refund order #' . $data->eOrderNumber,
                'error: ' . $e->getMessage(),
                'line : ' . __LINE__
            ];
        }

        if ($doSendError) {
            Tools::log($errors);

            $to = Tools::getBccEmailAddress('retur', $order);

            $mailer = $this->hanzo->container->get('mail_manager');
            $mailer->setTo($to);
            $mailer->setsubject('Refundering fejlede på ordre #' . $data->eOrderNumber);
            $mailer->setBody(
                "Fejlen opstået på: {$domain}\n\n".
                "E-ordrenummer: ".$data->eOrderNumber." PaymentOrderId: ".$order->getPaymentGatewayId()."\n\n".
                "Fejlbeskeder:\n- ".
                implode("\n- ", $errors)."\n\n".
                "Med venlig hilsen DIBS\n"
            );
            $mailer->send();
        }

        return count($errors) ? $errors : true;
    }



    /**
     * tmp mapping of currency to domain.
     * @fixme: skal laves om i både ax og web. og skal ikke være hardcoded
     *
     * @param \stdClass $entry
     *
     * @return array|false
     */
    public function getDomainKeyFromCurrencyKey($entry)
    {
        $k = strtolower($entry->Currency.'.'.$entry->CustAccount);

        $c_map = [
            'dkk.dkk'     => ['currency' => 'DKK', 'domain' => 'DK',      'vat' => 25],
            'eur.eur'     => ['currency' => 'EUR', 'domain' => 'COM',     'vat' => 25],
            'dkk.salesdk' => ['currency' => 'DKK', 'domain' => 'SalesDK', 'vat' => 25],

            'nok.nok'     => ['currency' => 'NOK', 'domain' => 'NO',      'vat' => 25],
            'nok.salesno' => ['currency' => 'NOK', 'domain' => 'SalesNO', 'vat' => 25],

            'sek.sek'     => ['currency' => 'NOK', 'domain' => 'SE',      'vat' => 25],
            'sek.salesse' => ['currency' => 'NOK', 'domain' => 'SalesSE', 'vat' => 25],

            'eur.fin'     => ['currency' => 'NOK', 'domain' => 'FI',      'vat' => 24],
            'eur.salesfi' => ['currency' => 'NOK', 'domain' => 'SalesFI', 'vat' => 24],

            'eur.nld'     => ['currency' => 'NOK', 'domain' => 'NL',      'vat' => 21],
            'eur.salesnl' => ['currency' => 'NOK', 'domain' => 'SalesNL', 'vat' => 21],

            'eur.de'      => ['currency' => 'EUR', 'domain' => 'DE',      'vat' => 19],
            'eur.salesde' => ['currency' => 'EUR', 'domain' => 'SalesDE', 'vat' => 19],

            'eur.aut'     => ['currency' => 'EUR', 'domain' => 'AT',      'vat' => 20],
            'eur.salesat' => ['currency' => 'EUR', 'domain' => 'SalesAT', 'vat' => 20],

            'chf.chf'     => ['currency' => 'CHF', 'domain' => 'CH',      'vat' => 8],
            'chf.salesch' => ['currency' => 'CHF', 'domain' => 'SalesCH', 'vat' => 8],
        ];

        return isset($c_map[$k]) ? $c_map[$k] : false;
    }

    protected function boot()
    {
        $this->timer = new Timer('ax');
    }
}
