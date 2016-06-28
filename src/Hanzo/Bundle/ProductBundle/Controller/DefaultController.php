<?php

namespace Hanzo\Bundle\ProductBundle\Controller;

use Criteria;

use Hanzo\Core\CoreController;
use Hanzo\Core\Hanzo;
use Hanzo\Core\Tools;
use Hanzo\Model\Products;
use Hanzo\Model\ProductsDomainsPrices;
use Hanzo\Model\ProductsDomainsPricesPeer;
use Hanzo\Model\ProductsI18nQuery;
use Hanzo\Model\ProductsImages;
use Hanzo\Model\ProductsImagesPeer;
use Hanzo\Model\ProductsImagesProductReferencesQuery;
use Hanzo\Model\ProductsImagesQuery;
use Hanzo\Model\ProductsQuery;
use Hanzo\Model\ProductsSeoI18nQuery;
use Hanzo\Model\ProductsWashingInstructions;
use Hanzo\Model\ProductsWashingInstructionsQuery;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends CoreController
{
    /**
     * @param Request $request
     * @param int     $product_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function viewAction(Request $request, $product_id)
    {
        $hanzo = Hanzo::getInstance();
        $translator = $this->get('translator');

        $router = $this->get('router');
        $route = $request->get('_route');
        $focus = $request->get('focus', false);

        $products = ProductsI18nQuery::create()
            ->joinWithProducts()
            ->filterByLocale($hanzo->get('core.locale'))
            ->useProductsQuery()
                ->filterByIsActive(true)
                ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
                ->useProductsDomainsPricesQuery()
                    ->filterByDomainsId($hanzo->get('core.domain_id'))
                    ->filterByFromDate(['max' => 'now'])
                    ->_or()
                    ->condition('c1', ProductsDomainsPricesPeer::FROM_DATE . ' <= NOW()')
                    ->condition('c2', ProductsDomainsPricesPeer::TO_DATE . ' >= NOW()')
                    ->where(['c1', 'c2'], 'AND')
                ->endUse()
                ->joinWithProductsDomainsPrices()
                ->joinWithProductsImages()
                ->joinWithProductsToCategories()
            ->endUse()
            ->findById($product_id)
        ;

        // if no product matched the query, throw a 404 exception
        if ($products->count() == 0) {
            throw $this->createNotFoundException($translator->trans('product.not.found'));
        }

        $product = $products[0]->getProducts();

        // find all product images
        $images = [];
        $image_ids = [];

        $c = new \Criteria();
        $c->addAscendingOrderByColumn(ProductsImagesPeer::COLOR);
        $c->addDescendingOrderByColumn(ProductsImagesPeer::TYPE);
        $c->addAscendingOrderByColumn(ProductsImagesPeer::IMAGE);
        $product_images = $product->getProductsImagess($c);

        foreach ($product_images as $image) {
            $path_params = explode('_', explode('.', $image->getImage())[0]);
            $number = isset($path_params[3]) ? (int)$path_params[3] : 0;
            $image_ids[] = $image->getId(); // Used for references

            $images[$image->getId()] = [
                'id'     => $image->getId(),
                'name'   => $image->getImage(),
                'color'  => $image->getColor(),
                'type'   => $image->getType(),
                'number' => $number,
            ];
        }

        // Use to kep an array of all images with keys. array_shift broke the keys.
        $all_images = $images;

        // set focus image
        if ($focus && isset($images[$focus])) {
            $main_image = $images[$focus];
        } else {
            $main_image = reset($images);
        }

        $sorted_images = [];
        foreach ($images as $key => $data) {
            $s = $data['type'];
            if ('set' === $s) {
                $s = 'aaa'.$s;
            }
            $sorted_images[$data['color'].$s.$key] = $data;
        }
        ksort($sorted_images);

        $all_colors = $colors = $sizes = [];
        $product_ids = [];
        $variants = ProductsQuery::create()->findByMaster($product->getSku());

        $sizes = [];

        // All colors are used for colorbuttons
        foreach ($variants as $v) {
            $all_colors[$v->getColor()] = $v->getColor();
            $sizes[$v->getSize()] = [
                'label'    => $v->getPostfixedSize($translator),
                'value'    => $v->getSize(),
                'in_stock' => false,
            ];
        }

        $colors = $all_colors;
        // find the sizes and colors on stock
        if (!$product->getIsOutOfStock()) {
            foreach ($variants as $v) {
                $product_ids[] = $v->getId();
            }

            $stock = $this->get('stock');
            $stock->prime($product_ids);
            foreach ($variants as $v) {
                if ($stock->check($v->getId())) {
                    $sizes[$v->getSize()]['in_stock'] = true;
                }
            }

            natcasesort($colors);
            Tools::sortSizes($sizes);
        }

        $references = ProductsImagesProductReferencesQuery::create()
            ->withColumn('products_images.ID')
            ->withColumn('products_images.COLOR')
            ->withColumn('products_images.IMAGE')
            ->filterByProductsImagesId($image_ids)
            ->joinWithProducts()
            ->useProductsQuery()
                ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
                ->joinWithProductsImages()
                ->useProductsImagesQuery()
                    ->addDescendingOrderByColumn('IMAGE')
                    ->filterByType('overview')
                    ->where('products_images.COLOR = products_images_product_references.COLOR')
                ->endUse()
            ->endUse()
            ->find()
        ;

        $images_references = [];
        foreach ($references as $ref) {
            $sku = $ref->getProducts()->getTitle();
            $images_references[$ref->getProductsImagesId()]['references'][$ref->getProductsId()] = [
                'title' => $sku,
                'color' => $ref->getVirtualColumn('products_imagesCOLOR'),
                'image' => $ref->getVirtualColumn('products_imagesIMAGE'),
                'url'   => $router->generate($route, [
                    'product_id' => $ref->getProductsId(),
                    'title'      => Tools::stripText($sku),
                    'focus'      => $ref->getVirtualColumn('products_imagesID'),
                ], TRUE),
            ];
        }

        // If there are any references to this image,
        // Prepend an overview 01 image of the current product to the array using array_unshift.
        foreach ($images_references as $image_id => &$references) {
            if (count($references['references']) > 0) {
                array_unshift($references['references'], [
                    'title' => $product->getSku(),
                    'color' => '',
                    'image' => preg_replace('/_set_[0-9]+/', '_overview_01', $all_images[$image_id]['name']),
                    'url'   => $router->generate($route, [
                        'product_id' => $product->getId(),
                        'title'      => Tools::stripText($product->getSku()),
                    ], TRUE),
                ]);
            }
        }

        // Replace all overview_02 with 01
        foreach ($images_references as $image_id => &$references) {
            foreach ($references['references'] as &$image) {
                if (strpos($image['image'],'overview_02') !== false) {
                    $image['image'] = str_replace('overview_02', 'overview_01', $image['image']);
                }
            }
        }

        $translation_key = 'description.' . Tools::stripText($product->getSku(), '_', false);

        $find = '~(background|src)="(../|/)~';
        $replace = '$1="' . $hanzo->get('core.cdn');

        $description = $translator->trans($translation_key, ['%cdn%' => $hanzo->get('core.cdn')], 'products');
        $description = preg_replace($find, $replace, $description);

        $washing = '';
        $result = ProductsWashingInstructionsQuery::create()
            ->filterByLocale($hanzo->get('core.locale'))
            ->findOneByCode($product->getWashing())
            ;

        // As seo text is related to a product style we have to look at the first product id
        // Currently the SEO text import/export duplicates the meta info to all varients
        $seo = ProductsSeoI18nQuery::create()
            ->filterByLocale($hanzo->get('core.locale'))
            ->filterByProductsId($product_ids[0])
            ->findOne();

        $metaTitle       = '';
        $metaDescription = '';
        if ($seo) {
            $metaTitle       = !empty($seo->getMetaTitle()) ? $seo->getMetaTitle() : '';
            $metaDescription = !empty($seo->getMetaDescription()) ? $seo->getMetaDescription() : '';
        }

        if ($result instanceof ProductsWashingInstructions) {
            $washing = stripslashes($result->getDescription());
            $washing = preg_replace($find, $replace, $washing);
        }

        $data = [
            'id'                => $product->getId(),
            'sku'               => $product->getSku(),
            'title'             => $product->getTitle(),
            'description'       => $description,
            'washing'           => $washing,
            'main_image'        => $main_image,
            'images'            => $sorted_images,
            'prices'            => [],
            'out_of_stock'      => $product->getIsOutOfStock(),
            'colors'            => $colors,
            'all_colors'        => $all_colors,
            'sizes'             => $sizes,
//            'images_references' => $images_references,
            'has_video'         => (bool)$product->getHasVideo(),
        ];


        // find and calculate prices
        $prices = ProductsDomainsPricesPeer::getProductsPrices([$data['id']]);
        $data['prices'] = array_shift($prices);

        // $images_references = $data['images_references'];
        // unset($data['images_references']);

        $this->get('twig')->addGlobal('page_type', 'product-'.$data['id']);
        $this->get('twig')->addGlobal('body_classes', 'body-product product-'.$data['id']);

        $this->setSharedMaxAge(300);
        $response = $this->render('ProductBundle:Default:view.html.twig', [
            'page_type'        => 'product',
            'product'          => $data,
            'references'       => $images_references,
            'browser_title'    => $product->getTitle(),
            '_route'           => $route,
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDescription,
        ]);

        return $response;
    }


    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apiListAction(Request $request)
    {
        if (!$this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json_response([
                'status'  => false,
                'message' => 'requires.login',
            ], 401);
        }

        $cacheId = ['apiListAction', $request->getLocale()];
        $data = $this->getCache($cacheId);

        if (empty($data)) {
            $data = $this->generateProductList($request);
        }

        $this->setCache($cacheId, $data, 600); // 10 min cache.

        return $this->json_response($data);
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    private function generateProductList(Request $request)
    {
        $hanzo = Hanzo::getInstance();
        $locale = $request->getLocale();
        $translator = $this->get('translator');
        $router = $this->container->get('router');

        $cdn = $hanzo->get('core.cdn');
        $find = '~(background|src)="(../|/)~';
        $replace = '$1="' . $cdn;

        $products = ProductsQuery::create()
            ->joinProductsI18n()
            ->joinProductsWashingInstructions()
            ->useProductsWashingInstructionsQuery()
                ->filterByLocale($locale)
            ->endUse()
            ->useProductsI18nQuery()
                ->filterByLocale($locale)
            ->endUse()
            ->filterByMaster(null)
            ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
            ->find();

        $data = [];
        $productIds = [];

        /** @var Products $product */
        foreach ($products as $product) {
            $id = $product->getId();
            $productIds[$id] = $id;
            $translation_key = 'description.' . Tools::stripText($product->getSku(), '-', false);

            $description = $translator->trans($translation_key, ['%cdn%' => $cdn], 'products');
            $description = preg_replace($find, $replace, $description);

            $washing = '';
            if ($instructions = $product->getProductsWashingInstructions()) {
                $washing = stripslashes($instructions->getDescription());
                $washing = preg_replace($find, $replace, $washing);
            }

            $data[$id] = [
                'id'          => $id,
                'sku'         => $product->getSku(),
                'url'         => $router->generate('product_info', ['product_id' => $id], true),
                'title'       => $product->getCurrentTranslation()->getTitle(),
                'description' => $description,
                'washing'     => $washing,
                'images'      => $this->getImages($product),
                'variants'    => $this->getVariants($product),
            ];
        }

        foreach (ProductsDomainsPricesPeer::getProductsPrices($productIds) as $id => $prices) {
            foreach ($prices as $type => $price) {
                $price['formatted'] = $price['formattet'];
                unset($price['currency'], $price['formattet']);
                $prices[$type] = $price;
            }
            $data[$id]['prices'] = $prices;
        }

        sort($data);

        return $data;
    }

    /**
     * @param Products $master
     *
     * @return array
     */
    private function getVariants(Products $master)
    {
        $translator = $this->container->get('translator');

        $variants = ProductsQuery::create()
            ->filterByMaster($master->getSku())
            ->filterByRange($this->container->get('hanzo_product.range')->getCurrentRange())
            ->orderBySku()
            ->find();

        $data = [];

        /** @var Products $variant */
        foreach ($variants as $variant) {
            $colorButton = Tools::fxImageUrl('images/colorbuttons/_'.strtolower(str_replace([' ', '/'], ['-', '9'], $variant->getColor())).'.png');

            $data[] = [
                'id'         => $variant->getId(),
                'sku'        => $variant->getSku(),
                'color'      => $variant->getColor(),
                'color_dots' => [
                    'active'   => str_replace('.png', '_active.png', $colorButton),
                    'inactive' => $colorButton,
                ],
                'size'       => $variant->getSize(),
                'size_label' => str_replace($variant->getSize(), '', $variant->getPostfixedSize($translator)),
                'in_stock'   => !$variant->getIsOutOfStock(),
            ];
        }

        return $data;
    }

    /**
     * @param Products $master
     *
     * @return array
     */
    private function getImages(Products $master)
    {
        $images = ProductsImagesQuery::create()
            ->leftJoinProductsImagesProductReferences()
            ->filterByProductsId($master->getId())
            ->find();

        $data = [];

        /** @var ProductsImages $image */
        $i = 0;
        foreach ($images as $image) {
            $data[$i] = [
                'src'        => Tools::productImageUrl($image->getImage(), '234x410'),
                'type'       => $image->getType(),
                'color'      => $image->getColor(),
            ];

            $references = [];
            foreach ($image->getProductsImagesProductReferencess() as $reference) {
                $criteria = new Criteria();
                $criteria->add(ProductsImagesPeer::TYPE, 'overview');
                $criteria->add(ProductsImagesPeer::COLOR, $reference->getColor());
                $criteria->add(ProductsImagesPeer::IMAGE, '%_overview_01%', Criteria::LIKE);
                $criteria->setLimit(1);

                $image = $reference->getProducts()
                    ->getProductsImagess($criteria)
                    ->getFirst()
                    ->getImage();

                $references[] = [
                    'src'        => Tools::productImageUrl($image, '234x410'),
                    'color'      => $reference->getColor(),
                    'product_id' => $reference->getProductsId(),
                ];
            }

            if (count($references)) {
                $data[$i]['references'] = $references;
            }

            $i++;
        }

        return $data;
    }
}
