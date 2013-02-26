<?php

namespace Hanzo\Bundle\CategoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

use Hanzo\Core\CoreController;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;

use Hanzo\Model\ProductsImagesCategoriesSortQuery;
use Hanzo\Model\ProductsImagesCategoriesSortPeer;
use Hanzo\Model\CategoriesPeer;

use Hanzo\Model\ProductsQuery;
use Hanzo\Model\Products;
use Hanzo\Model\ProductsImagesPeer;
use Hanzo\Model\ProductsDomainsPricesPeer;

use Hanzo\Model\CmsPeer;

class DefaultController extends CoreController
{

    /**
     * handle category listings
     *
     * @param $cms_id
     * @param $category_id
     * @param $pager
     */
    public function viewAction($cms_id, $category_id, $show, $pager = 1)
    {
        $hanzo = Hanzo::getInstance();
        $container = $hanzo->container;
        $locale = $hanzo->get('core.locale');

        $cache_id = explode('_', $this->get('request')->get('_route'));
        $cache_id = array($cache_id[0], $cache_id[2], $cache_id[1], $show, $pager);

        if($this->getFormat() !== 'json') $cache_id[] = 'html'; // Extra cache id if its not a json call

        $html = $this->getCache($cache_id); // If there a cached version, html has both the json and html version
        $data = null;

        /*
         *  If html wasnt cached retrieve a fresh set of data
         */
        if(!$html){
            $cms_page = CmsPeer::getByPK($cms_id, $locale);
            $settings = json_decode($cms_page->getSettings());

            $color_map = null;
            if(!empty($settings->colors)){
                $color_map = explode(',', $settings->colors);
            }

            $route = $container->get('request')->get('_route');
            $router = $container->get('router');
            $domain_id = $hanzo->get('core.domain_id');
            $show_by_look = (bool)($show === 'look');

            $result = ProductsImagesCategoriesSortQuery::create()
                ->useProductsQuery()
                    ->where('products.MASTER IS NULL')
                    ->filterByIsOutOfStock(FALSE)
                    ->useProductsDomainsPricesQuery()
                        ->filterByDomainsId($domain_id)
                    ->endUse()
                ->endUse()
                ->joinWithProducts()
                ->useProductsImagesQuery()
                    ->filterByType($show_by_look?'set':'overview')
                    ->groupByImage()
                ->endUse()
                ->joinWithProductsImages()
                ->filterByCategoriesId($category_id)
            ;

            // If there are any colors in the settings to order from, add the order column here.
            // Else order by normal Sort in db
            if($color_map){
                $result = $result->addDescendingOrderByColumn(sprintf(
                    "FIELD(%s, %s)",
                    ProductsImagesPeer::COLOR,
                    '\''.implode('\',\'', $color_map).'\''
                ));
            }else{
                $result = $result->orderBySort();
            }

            if($pager === 'all'){
                $result = $result->paginate(null, null);
            }else{
                $result = $result->paginate($pager, 12);
            }

            $product_route = str_replace('category_', 'product_', $route);

            $records = array();
            $product_ids = array();
            foreach ($result as $record) {
                $product = $record->getProducts();
                $product_ids[] = $product->getId();

                $image_overview = str_replace('_set_', '_overview_', $record->getProductsImages()->getImage());
                $image_set = str_replace('_overview_', '_set_', $record->getProductsImages()->getImage());

                $records[] = array(
                    'sku' => $product->getSku(),
                    'id' => $product->getId(),
                    'title' => $product->getSku(),
                    'image' => ($show_by_look)?$image_set:$image_overview,
                    'image_flip' => ($show_by_look)?$image_overview:$image_set,
                    'url' => $router->generate($product_route, array(
                        'product_id' => $product->getId(),
                        'title' => Tools::stripText($product->getSku()),
                        'focus' => $record->getProductsImages()->getId()
                    )),
                );
            }

            // get product prices
            $prices = ProductsDomainsPricesPeer::getProductsPrices($product_ids);

            // attach the prices to the products
            foreach ($records as $i => $data) {
                if (isset($prices[$data['id']])) {
                    $records[$i]['prices'] = $prices[$data['id']];
                }
            }

            $data = array(
                'title' => '',
                'products' => $records,
                'paginate' => NULL,
            );

            if ($result->haveToPaginate()) {

                $pages = array();
                foreach ($result->getLinks(20) as $page) {
                    $pages[$page] = $router->generate($route, array('pager' => $page, 'show' => $show), TRUE);
                }

                $data['paginate'] = array(
                    'next' => ($result->getNextPage() == $pager ? '' : $router->generate($route, array('pager' => $result->getNextPage()), TRUE)),
                    'prew' => ($result->getPreviousPage() == $pager ? '' : $router->generate($route, array('pager' => $result->getPreviousPage()), TRUE)),

                    'pages' => $pages,
                    'index' => $pager,
                    'see_all' => array(
                        'total' => $result->getNbResults(),
                        'url' => $router->generate($route, array('pager' => 'all'), TRUE)
                    )
                );
            }

            if ($this->getFormat() == 'json') {

                // for json we need the real image paths
                foreach ($data['products'] as $k => $product) {
                    $data['products'][$k]['image'] = Tools::productImageUrl($product['image'], '234x410');
                    $data['products'][$k]['image_flip'] = Tools::productImageUrl($product['image_flip'], '234x410');
                }
                $this->setCache($cache_id, $data, 5);
                $html = $data; // Use the json data as the html returned to call
            }else{
                $this->get('twig')->addGlobal('page_type', 'category-'.$category_id);
                $this->get('twig')->addGlobal('body_classes', 'body-category category-'.$category_id.' body-'.$show);
                $this->get('twig')->addGlobal('show_new_price_badge', $hanzo->get('webshop.show_new_price_badge'));
                $this->get('twig')->addGlobal('cms_id', $cms_page->getParentId());
                $this->get('twig')->addGlobal('show_by_look', ($show === 'look'));
                $html = $this->renderView('CategoryBundle:Default:view.html.twig', $data);
                $this->setCache($cache_id, $html, 5);
            }
        } // End of retrival of fresh data

        // json requests
        if ($this->getFormat() == 'json') {
            return $this->json_response($html);
        }else{
            $this->setSharedMaxAge(1800);
            return $this->response($html);
        }

    }

    public function listProductsAction($view = 'simple', $filter = 'G_')
    {
        $filter_map = array(
            'G_' => 'Girl',
            'LG_' => 'Little Girl',
            'B_' => 'Boy',
            'LB_' => 'Little Boy',
        );

        $hanzo = Hanzo::getInstance();
        $domain_id = $hanzo->get('core.domain_id');

        $products = ProductsQuery::create()
            ->where('products.MASTER IS NULL')
            ->useProductsDomainsPricesQuery()
                ->filterByDomainsId($domain_id)
            ->endUse()
            ->useProductsToCategoriesQuery()
                ->useCategoriesQuery()
                    ->filterByContext($filter.'%', \Criteria::LIKE)
                ->endUse()
            ->endUse()
            ->joinWithProductsToCategories()
            ->orderBySku()
            ->groupBySku()
            ->find()
        ;

        $records = array();
        foreach ($products as $product) {
            $records[] = array(
                'sku' => $product->getSku(),
                'id' => $product->getId(),
                'title' => $product->getSku(),
            );
        }

        $max = ceil(count($records)/3);
        $records = array_chunk($records, $max);

        $this->setSharedMaxAge(86400);
        return $this->render('CategoryBundle:Default:contextList.html.twig', array(
            'page_type' => 'context-list',
            'products' => $records,
            'page_title' => $filter_map[$filter]
        ));
    }
}
