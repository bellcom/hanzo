<?php

namespace Hanzo\Bundle\ProductBundle\Controller;

use Criteria;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Core\CoreController;

use Hanzo\Model\ProductsDomainsPricesPeer;
use Hanzo\Model\Products;
use Hanzo\Model\ProductsI18nQuery;
use Hanzo\Model\ProductsQuery;
use Hanzo\Model\ProductsImagesProductReferencesQuery;
use Hanzo\Model\ProductsWashingInstructions;
use Hanzo\Model\ProductsWashingInstructionsQuery;
use Hanzo\Model\ProductsToCategoriesQuery;
use Symfony\Component\HttpFoundation\Request;

class BundleController extends CoreController
{
    public function viewAction(Request $request, $image_id, $return)
    {
        $hanzo = Hanzo::getInstance();
        $translator = $this->get('translator');
        $router = $this->get('router');

        $cache_id = array('product.image.bundle', $image_id);
        $products = $this->getCache($cache_id);

        if (empty($products)) {
            $product_ids = array();

            $main_product = ProductsQuery::create()
                ->useProductsI18nQuery()
                ->filterByLocale($request->getLocale())
                ->endUse()
                ->useProductsImagesQuery()
                ->filterById($image_id)
                ->endUse()
                ->filterByIsActive(true)
                ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
                ->useProductsDomainsPricesQuery()
                ->filterByDomainsId($hanzo->get('core.domain_id'))
                ->filterByFromDate(array('max' => 'now'))
                ->_or()
                ->condition('c1', ProductsDomainsPricesPeer::FROM_DATE . ' <= NOW()')
                ->condition('c2', ProductsDomainsPricesPeer::TO_DATE . ' >= NOW()')
                ->where(array('c1', 'c2'), 'AND')
                ->endUse()
                ->joinWithProductsImages()
                ->findOne()
            ;

            if (!$main_product instanceof Products) {
                return $this->redirect($this->generateUrl('_homepage'));
            }

            $product_ids[] = $main_product->getId();

            /**
             * As a product can be in more than one category we have to find the correct one
             *
             * The category_map.php was used for that
             * - But if the product is in category X, and that one does not have an cms page it is not going to be in the map.
             * - Looping over the result is also not an option because the first matching one might also be wrong.
             * - So we use the route send in the return var
             *
             * If $return route is empty, we fallback to the old version tho..
             */
            if (empty($return)) {
                $return = $this->productToCategryRoute($request->getLocale(), $main_product);
            }
            $product_route = $return;

            // Without this i18n behaviour uses da_DK
            $main_product->setLocale($request->getLocale());

            $image = $main_product->getProductsImagess()->getFirst();
            $products[$main_product->getId()] = array(
                'id'           => $main_product->getId(),
                'master'       => $main_product->getSku(),
                'title'        => $main_product->getTitle(),
                'color'        => $image->getColor(),
                'image'        => $image->getImage(),
                'washing_id'   => $main_product->getWashing(),
                'url'          => $router->generate($product_route, array(
                    'product_id' => $main_product->getId(),
                    'title'      => Tools::stripText($main_product->getSku()),
                )),
                'out_of_stock' => true,
            );

            $result = ProductsQuery::create()
                ->useProductsI18nQuery()
                ->filterByLocale($request->getLocale())
                ->endUse()
                ->useProductsImagesProductReferencesQuery()
                ->filterByProductsImagesId($image_id)
                ->endUse()
                ->filterByIsActive(true)
                ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
                ->useProductsDomainsPricesQuery()
                ->filterByDomainsId($hanzo->get('core.domain_id'))
                ->filterByFromDate(array('max' => 'now'))
                ->_or()
                ->condition('c1', ProductsDomainsPricesPeer::FROM_DATE . ' <= NOW()')
                ->condition('c2', ProductsDomainsPricesPeer::TO_DATE . ' >= NOW()')
                ->where(array('c1', 'c2'), 'AND')
                ->endUse()
                ->useProductsImagesQuery()
                ->filterByType('overview')
                ->where('products_images.COLOR = products_images_product_references.COLOR')
                ->groupByProductsId()
                ->endUse()
                ->joinWithProductsImages()
                ->find()
            ;

            foreach ($result as $product) {
                // handle bare return urls.
                if (empty($return)) {
                    $product_route = $this->productToCategryRoute($request->getLocale(), $product);

                    // if not able to map the category, skip the product.
                    if (empty($product_route)) {
                        continue;
                    }
                }

                // Without this i18n behaviour uses da_DK
                $product->setLocale($request->getLocale());

                $image = $product->getProductsImagess()->getFirst();
                $products[$product->getId()] = array(
                    'id'           => $product->getId(),
                    'master'       => $product->getSku(),
                    'title'        => $product->getTitle(),
                    'color'        => $image->getColor(),
                    'image'        => $image->getImage(),
                    'washing_id'   => $product->getWashing(),
                    'url'          => $router->generate($product_route, array(
                        'product_id' => $product->getId(),
                        'title'      => Tools::stripText($product->getSku()),
                    )),
                    'out_of_stock' => true,
                );

                $product_ids[] = $product->getId();
            }

            foreach (ProductsDomainsPricesPeer::getProductsPrices($product_ids) as $i => $price) {
                $products[$i]['prices'] = $price;
            }

            // Add description and washing details to all products.
            $find = '~(background|src)="(../|/)~';
            $replace = '$1="' . $hanzo->get('core.cdn');

            foreach ($products as $id => $product) {
                $translation_key = 'description.' . Tools::stripText($product['master'], '_', false);

                $description = $translator->trans($translation_key, array('%cdn%' => $hanzo->get('core.cdn')), 'products');
                $description = preg_replace($find, $replace, $description);

                $washing = null;
                $result = ProductsWashingInstructionsQuery::create()
                    ->filterByLocale($request->getLocale())
                    ->findOneByCode($product['washing_id']);

                if ($result instanceof ProductsWashingInstructions) {
                    $washing = stripslashes($result->getDescription());
                    $washing = preg_replace($find, $replace, $washing);
                }

                $products[$id]['description'] = ($description !== $translation_key) ? $description : null;
                $products[$id]['washing'] = $washing;
            }

            //$this->setCache($cache_id, $products);
        }

        foreach ($products as $id => $product) {
            $variants = ProductsQuery::create()->findByMaster($product['master']);
            $products_id = [];
            $sizes = [];
            foreach ($variants as $v) {
                $product_ids[] = $v->getId();
            }

            $stock = $this->get('stock');
            $stock->prime($product_ids);
            foreach ($variants as $v) {
                if ($stock->check($v->getId())) {
                    $sizes[$v->getSize()] = $v->getSize();
                }
                $products[$id]['out_of_stock'] = false;
            }

            Tools::sortSizes($sizes);

            $products[$id]['options'] = $sizes;
        }

        $this->setSharedMaxAge(86400);
        $this->get('twig')->addGlobal('body_classes', 'body-product body-buy-set');
        $responce = $this->render('ProductBundle:Bundle:view.html.twig', array(
            'page_type' => 'bundle',
            'products'  => $products,
        ));

        return $responce;
    }


    public function customAction($set)
    {
        $hanzo = Hanzo::getInstance();
        $translator = $this->get('translator');
        $router = $this->get('router');

        $cache_id = array('product.bundle.custom', str_replace(',', '-', $set));
        $products = $this->getCache($cache_id);

        if (empty($products)) {
            $set = explode(',', $set);

            $where = [];
            $products_ids = [];
            foreach ($set as $product) {
                $pieces = explode('-', $product, 2);

                if (empty($pieces[1])) {
                    Tools::log("Invalid set url: {$_SERVER['REQUEST_URI']}");
                    continue;
                }

                $where[] = array(
                    'ProductsId' => $pieces[0],
                    'Color' => str_replace(['9', '-'], ['/', ' '], $pieces[1]),
                );
                $product_ids[] = $pieces[0];
            }

            $router_keys = include $this->container->getParameter('kernel.cache_dir').'/category_map.php';
            $locale = strtolower($hanzo->get('core.locale'));

            $result = ProductsQuery::create()
                ->useProductsI18nQuery()
                ->filterByLocale($hanzo->get('core.locale'))
                ->endUse()
                ->filterByIsActive(true)
                ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
                ->useProductsI18nQuery()
                ->filterByLocale($hanzo->get('core.locale'))
                ->endUse()
                ->useProductsImagesQuery()
                ->filterByType('overview')
            ;
            // Add all sets to conditions seperately.
            $combines = [];
            foreach ($where as $i => $where_clause) {
                $result = $result->condition('id_' . $i, 'products_images.products_id = ?', $where_clause['ProductsId'])
                    ->condition('color_' . $i, 'products_images.color = ?', $where_clause['Color'])
                    ->combine(array('id_' . $i, 'color_' . $i), 'and', 'combine_' . $i)
                ;

                $combines[] = 'combine_' . $i;
            }
            $result = $result->where($combines, 'or')
                ->groupByProductsId()
                ->endUse()
                ->joinWithProductsImages()
                ->useProductsDomainsPricesQuery()
                ->filterByDomainsId($hanzo->get('core.domain_id'))
                ->filterByFromDate(array('max' => 'now'))
                ->_or()
                ->condition('c1', ProductsDomainsPricesPeer::FROM_DATE . ' <= NOW()')
                ->condition('c2', ProductsDomainsPricesPeer::TO_DATE . ' >= NOW()')
                ->where(array('c1', 'c2'), 'AND')
                ->endUse()
                ->find()
            ;

            $products = [];
            foreach ($result as $product) {
                $products2category = ProductsToCategoriesQuery::create()
                    ->useProductsQuery()
                    ->filterBySku($product->getSku())
                    ->endUse()
                    ->findOne()
                ;

                $key = '_' . $locale . '_' . $products2category->getCategoriesId();
                $product_route = $router_keys[$key];

                // Without this i18n behaviour uses da_DK
                $product->setLocale($hanzo->get('core.locale'));

                $image = $product->getProductsImagess()->getFirst();
                $products[$product->getId()] = array(
                    'id'           => $product->getId(),
                    'master'       => $product->getSku(),
                    'title'        => $product->getTitle(),
                    'color'        => $image->getColor(),
                    'image'        => $image->getImage(),
                    'washing_id'   => $product->getWashing(),
                    'url'          => $router->generate($product_route, array(
                        'product_id' => $product->getId(),
                        'title'      => Tools::stripText($product->getSku()),
                    )),
                    'out_of_stock' => true,
                );


                $product_ids[] = $product->getId();
            }

            foreach (ProductsDomainsPricesPeer::getProductsPrices($product_ids) as $i => $price) {
                $products[$i]['prices'] = $price;
            }

            // Add description and washing details to all products.
            $find = '~(background|src)="(../|/)~';
            $replace = '$1="' . str_replace(['https:', 'http:'], '', $hanzo->get('core.cdn'));

            foreach ($products as $id => $product) {

                $translation_key = 'description.' . Tools::stripText($product['master'], '_', false);

                $description = $translator->trans($translation_key, array('%cdn%' => $hanzo->get('core.cdn')), 'products');
                $description = preg_replace($find, $replace, $description);

                $washing = null;
                $result = ProductsWashingInstructionsQuery::create()
                    ->filterByLocale($hanzo->get('core.locale'))
                    ->findOneByCode($product['washing_id']);

                if ($result instanceof ProductsWashingInstructions) {
                    $washing = stripslashes($result->getDescription());
                    $washing = preg_replace($find, $replace, $washing);
                }

                $products[$id]['description'] = ($description !== $translation_key) ? $description : null;
                $products[$id]['washing'] = $washing;
            }

            $this->setCache($cache_id, $products);
        }

        foreach ($products as $id => $product) {
            if (empty($product['master'])) {
                continue;
            }

            $variants = ProductsQuery::create()->findByMaster($product['master']);
            $products_id = [];
            $sizes = [];
            foreach ($variants as $v) {
                $product_ids[] = $v->getId();
            }

            $stock = $this->get('stock');
            $stock->prime($product_ids);
            foreach ($variants as $v) {
                if ($stock->check($v->getId())) {
                    $sizes[$v->getSize()] = $v->getSize();
                }
                $products[$id]['out_of_stock'] = false;
            }

            uksort($sizes, function ($a, $b) {
                return (int) $a - (int) $b;
            });

            $products[$id]['options'] = $sizes;
        }

        $this->setSharedMaxAge(86400);
        $this->get('twig')->addGlobal('body_classes', 'body-product body-buy-set');
        $responce = $this->render('ProductBundle:Bundle:view.html.twig', array(
            'page_type' => 'bundle',
            'products'  => $products,
        ));

        return $responce;
    }

    /**
     * Try to match a category to a product route.
     *
     * @param string   $locale
     * @param Products $product
     *
     * @return bool
     */
    private function productToCategryRoute($locale, Products $product)
    {
        static $routerKeys;

        if (empty($routerKeys)) {
            $routerKeys = include $this->container->getParameter('kernel.cache_dir') . '/category_map.php';
        }

        $products2category = ProductsToCategoriesQuery::create()
            ->useProductsQuery()
            ->filterBySku($product->getSku())
            ->endUse()
            ->find()
        ;

        foreach ($products2category as $item) {
            $key = '_' . strtolower($locale) . '_' . $item->getCategoriesId();

            if (isset($routerKeys[$key])) {
                return $routerKeys[$key];
            }
        }

        return false;
    }
}
