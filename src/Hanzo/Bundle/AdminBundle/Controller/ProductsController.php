<?php

namespace Hanzo\Bundle\AdminBundle\Controller;

use Hanzo\Core\Tools;
use Hanzo\Model\LanguagesQuery;
use Hanzo\Model\Products;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

use Hanzo\Core\Hanzo;
use Hanzo\Core\CoreController;

use Hanzo\Model\ProductsDomainsPricesQuery;
use Hanzo\Model\ProductsImagesCategoriesSortQuery;
use Hanzo\Model\ProductsImagesCategoriesSort;
use Hanzo\Model\ProductsImagesProductReferences;
use Hanzo\Model\ProductsImagesProductReferencesQuery;
use Hanzo\Model\ProductsToCategoriesQuery;
use Hanzo\Model\ProductsToCategories;
use Hanzo\Model\ProductsImagesQuery;
use Hanzo\Model\ProductsQuery;
use Hanzo\Model\ProductsQuantityDiscountQuery;
use Hanzo\Model\ProductsQuantityDiscount;
use Hanzo\Model\DomainsQuery;
use Hanzo\Model\Categories;
use Hanzo\Model\CategoriesQuery;
use Hanzo\Model\RelatedProducts;
use Hanzo\Model\RelatedProductsQuery;

use Hanzo\Bundle\AdminBundle\Event\FilterCategoryEvent;
use Hanzo\Bundle\AdminBundle\Exporter\LanguageExporter;


/**
 * Class ProductsController
 *
 * @package Hanzo\Bundle\AdminBundle
 */
class ProductsController extends CoreController
{
    /**
     * @param Request $request
     * @param int     $category_id
     * @param int     $subcategory_id
     *
     * @return array
     * @Template()
     */
    public function indexAction(Request $request, $category_id, $subcategory_id)
    {
        $categories = null;
        $products   = null;
        $qClean     = null;

        $locale = $request->query->get('locale');

        if (!$locale) {
            $locale = LanguagesQuery::create()
                ->orderById()
                ->findOne($this->getDbConnection())
                ->getLocale();
        }

        if (isset($_GET['q'])) {
            $qClean = $request->get('q', null);
            $q = '%'.$qClean.'%';

            $products = ProductsQuery::create()
                ->filterBySku($q)
                ->_or()
                ->filterById($qClean)
                ->find($this->getDbConnection());

            $parentCategory = null;

        } elseif (!$category_id) {

            $categories = CategoriesQuery::create()
                ->where('categories.PARENT_ID IS NULL')
                ->joinWithI18n($locale)
                ->orderById()
                ->find($this->getDbConnection());

            $parentCategory = null;

        } elseif (!$subcategory_id) {

            $categories = CategoriesQuery::create()
                ->filterByParentId($category_id)
                ->joinWithI18n($locale)
                ->orderById()
                ->find($this->getDbConnection());

            $parentCategory = CategoriesQuery::create()
                ->joinWithI18n($locale)
                ->filterById($category_id)
                ->findOne($this->getDbConnection());

            $products = ProductsQuery::create()
                ->useProductsToCategoriesQuery()
                ->filterByCategoriesId($category_id)
                ->endUse()
                ->find($this->getDbConnection());

        } else {
            // Both $category_id and $subcategory_id are set. Show some products!

            $products = ProductsQuery::create()
                ->useProductsToCategoriesQuery()
                    ->filterByCategoriesId($subcategory_id)
                ->endUse()
                ->find($this->getDbConnection());

            $parentCategory = CategoriesQuery::create()
                ->joinWithI18n($locale)
                ->filterById($subcategory_id)
                ->findOne($this->getDbConnection());
        }

        $categoriesList = [];
        $productsList   = [];

        if ($categories) {
            foreach ($categories as $category) {
                $category->setLocale($locale);
                $categoriesList[] = [
                    'id'        => $category->getId(),
                    'context'   => $category->getContext(),
                    'is_active' => $category->getIsActive(),
                    'title'     => $category->getTitle(),
                ];
            }
        }

        if ($products) {
            foreach ($products as $product) {
                $productsList[] = [
                    'id'              => $product->getId(),
                    'sku'             => $product->getSku(),
                    'master'          => $product->getMaster(),
                    'size'            => $product->getSize(),
                    'color'           => $product->getColor(),
                    'unit'            => $product->getUnit(),
                    'is_out_of_stock' => $product->getIsOutOfStock(),
                    'is_active'       => $product->getIsActive()
                ];
            }

        }

        return [
            'categories'      => $categoriesList,
            'products'        => $productsList,
            'parent_category' => $parentCategory,
            'category_id'     => $category_id,
            'subcategory_id'  => $subcategory_id,
            'search_query'    => $qClean,
            'database'        => $this->getRequest()->getSession()->get('database')
        ];
    }

    /**
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template()
     */
    public function listAction(Request $request)
    {
        $locale = $request->query->get('locale');

        if (!$locale) {
            $locale = LanguagesQuery::create()
                ->orderById()
                ->findOne($this->getDbConnection())
                ->getLocale();
        }

        $filter = $request->query->get('range');

        $ranges = [];
        $result = ProductsQuery::create()
            ->select('Range')
            ->distinct()
            ->find($this->getDbConnection());

        foreach ($result as $range) {
            $ranges[$range] = $range;
        }

        if ((count($ranges) === 1) && empty($filter)) {
            return $this->redirect($this->generateUrl('admin_products_list', ['range' => end($ranges)]));
        }

        if (empty($filter)) {
            return [
                'ranges'     => $ranges,
                'range_data' => [],
            ];
        }

        $cacheKey = ['admin', 'products', 'list', $filter, $this->getRequest()->getSession()->get('database')];
        $data = $this->getCache($cacheKey);

        if (empty($data)) {
            $sqlFilter = '';
            if (!empty($filter)) {
                $sqlFilter = 'AND p.range = ?';
                $filter = [$filter];
            } else {
                $filter = [];
            }

            $sql = "
                SELECT
                    p.id,
                    p.range,
                    p.sku,
                    p.is_out_of_stock,
                    pi.title,
                    (
                      SELECT
                        group_concat(ci.title)
                        FROM
                            categories_i18n AS ci
                        JOIN
                            products_to_categories AS p2c
                            ON (
                                ci.id = p2c.categories_id
                            )
                        WHERE
                            p2c.products_id = p.id
                    ) AS categories
                FROM
                    products as p
                JOIN
                    products_i18n AS pi
                    ON (
                        p.id = pi.id
                    )
                WHERE
                    p.master IS NULL
                {$sqlFilter}
                ORDER BY
                    p.range,
                    p.sku
            ";

            $stock = $this->container->get('stock');

            // Switch to the correct warehouse location.
            $databaseConnectionName = $request->getSession()->get('database', 'pdldbdk1');
            if ('pdldbdk1' !== $databaseConnectionName) {
                $stock->changeLocationByConnectionName($databaseConnectionName);
            }

            $conn  = $this->getDbConnection();
            $query = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);

            $query->execute($filter);

            $data = [];
            while ($record = $query->fetch(\PDO::FETCH_ASSOC)) {
                if (isset($data[$record['range']][$record['id']])) {
                    continue;
                }

                $record['categories'] = implode(', ', array_unique(explode(',', $record['categories'])));
                $record['stock']      = $stock->checkStyleStock($record['sku'], true);

                $data[$record['range']][$record['id']] = $record;
            }

            // Switch back to the default warehouse location.
            if ('pdldbdk1' !== $databaseConnectionName) {
                $stock->changeLocationByConnectionName('pdldbdk1');
            }

            uksort($data, "strnatcmp");
            $this->setCache($cacheKey, $data, 300);
        }

        return [
            'ranges'     => $ranges,
            'range_data' => $data,
        ];
    }

    /**
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function viewAction($id)
    {
        $categories = CategoriesQuery::create()
            ->where('categories.PARENT_ID IS NOT NULL')
            ->joinWithI18n('en_GB')
            ->orderByContext()
            ->find($this->getDbConnection());

        $productCategories = CategoriesQuery::create()
            ->useProductsToCategoriesQuery()
                ->filterByProductsId($id)
            ->endUse()
            ->orderById()
            ->find($this->getDbConnection());

        $currentProduct = ProductsQuery::create()
            ->filterById($id)
            ->orderBySku()
            ->findOne($this->getDbConnection());

        $styles = ProductsQuery::create()
            ->filterByMaster($currentProduct->getSku())
            ->orderBySku()
            ->find($this->getDbConnection());

        $allProducts = ProductsQuery::create()
            ->filterByMaster(null)
            ->orderBySku()
            ->find($this->getDbConnection());

        $productImages = ProductsImagesQuery::create()
            ->joinProducts()
            ->filterByProductsId($id)
            ->find($this->getDbConnection());

        $relatedProducts = RelatedProductsQuery::create()
            ->filterByMaster($currentProduct->getSku())
            ->orderBySku()
            ->find($this->getDbConnection());

        $productImagesList = [];

        foreach ($productImages as $record) {
            $productImageInCategories = ProductsImagesCategoriesSortQuery::create()
                ->joinWithCategories()
                ->filterByProductsImagesId($record->getId())
                ->find($this->getDbConnection());

            $imageCategoriesList = [];
            foreach ($productImageInCategories as $ref) {

                $imageCategoriesList[] = [
                    'id'          => $record->getId(),
                    'category_id' => $ref->getCategoriesId(),
                    'title'       => $ref->getCategories()->getContext()
                ];
            }

            $productsRefs = ProductsImagesProductReferencesQuery::create()
                ->joinWithProducts()
                ->joinWithProductsImages()
                ->filterByProductsImagesId($record->getId())
                ->find($this->getDbConnection());

            $productsRefsList = [];

            if ($productsRefs) {
                foreach ($productsRefs as $ref) {
                    $productRef = $ref->getProducts();

                    $productsRefsList[] = [
                        'id'    => $productRef->getId(),
                        'sku'   => $productRef->getSku(),
                        'color' => $ref->getColor()
                    ];
                }
            }

            $productImagesList[$record->getId()] = [
                'id'               => $record->getProductsId(),
                'image'            => $record->getImage(),
                'image_id'         => $record->getId(),
                'product_ref_ids'  => $productsRefsList,
                'image_categories' => $imageCategoriesList
            ];
        }

        $formHasVideo = $this->createFormBuilder($currentProduct)
            ->add('has_video', 'checkbox', [
                'label'              => 'product.label.has_video',
                'translation_domain' => 'admin',
                'required'           => false
            ])->add('is_discountable', 'checkbox', [
                'label'              => 'product.label.is_discountable',
                'translation_domain' => 'admin',
                'required'           => false
            ])->getForm();

        $request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $formHasVideo->bind($request);

            if ($formHasVideo->isValid()) {
                $currentProduct->save($this->getDbConnection());

                // update child products
                ProductsQuery::create()
                    ->filterByMaster($currentProduct->getSku())
                    ->update(['IsDiscountable' => $currentProduct->getIsDiscountable()]);
            }
        }

        $pricesResult = ProductsDomainsPricesQuery::create()
            ->filterByProductsId($id)
            ->joinWithDomains()
            ->orderByProductsId()
            ->orderByFromDate()
            ->find($this->getDbConnection());

        $prices = [];
        foreach ($pricesResult as $price) {
            $prices[] = [
                'domain'    => $price->getDomains()->getDomainKey(),
                'price'     => number_format($price->getPrice()+$price->getVat(), 2, ',', ''),
                'from_date' => $price->getFromDate('Y-m-d H:i'),
                'to_date'   => ($price->getToDate() ? $price->getToDate('Y-m-d H:i') : '-'),
            ];
        }

        return $this->render('AdminBundle:Products:view.html.twig', [
            'styles'                => $styles,
            'product_categories'    => $productCategories,
            'categories'            => $categories,
            'current_product'       => $currentProduct,
            'product_images'        => $productImagesList,
            'products'              => $allProducts,
            'related_products'      => $relatedProducts,
            'has_video_form'        => $formHasVideo->createView(),
            'prices'                => $prices,
            'database' => $this->getRequest()->getSession()->get('database')
        ]);
    }

    /**
     * @param Request $request
     * @param int     $product_id
     *
     * @throws \Exception
     * @throws \PropelException
     * @return Response
     */
    public function quantityDiscountsAction(Request $request, $product_id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $currentProduct = ProductsQuery::create()
            ->filterById($product_id)
            ->findOne($this->getDbConnection());

        $domainsAvailible = DomainsQuery::Create()->find($this->getDbConnection());

        foreach ($domainsAvailible as $domain) {
            $domainsAvailibleData[$domain->getId()] = $domain->getDomainKey();
        }

        $quantityDiscount = new ProductsQuantityDiscount();
        $quantityDiscount->setProductsMaster($currentProduct->getSku());

        $form = $this->createFormBuilder($quantityDiscount)
            ->add('domains_id', 'choice', [
                'label'              => 'admin.products.discount.domains_id',
                'choices'            => $domainsAvailibleData,
                'required'           => true,
                'translation_domain' => 'admin'
            ])->add('span', 'integer', [
                'label'              => 'admin.products.discount.span',
                'required'           => true,
                'translation_domain' => 'admin'
            ])->add('discount', 'number', [
                'label'              => 'admin.products.discount.discount',
                'required'           => true,
                'translation_domain' => 'admin'
            ])->add('valid_from', 'date', [
                'input'              => 'string',
                'widget'             => 'single_text',
                'format'             => 'dd-MM-yyyy',
                'label'              => 'admin.gift_cards.active_from',
                'translation_domain' => 'admin',
                'required'           => false,
                'attr'               => ['class' => 'datepicker'],
                'data'               => $quantityDiscount->getValidFrom('Y-m-d'),
            ])->add('valid_to', 'date', [
                'input'              => 'string',
                'widget'             => 'single_text',
                'format'             => 'dd-MM-yyyy',
                'label'              => 'admin.gift_cards.active_to',
                'translation_domain' => 'admin',
                'required'           => false,
                'attr'               => ['class' => 'datepicker'],
                'data'               => $quantityDiscount->getValidTo('Y-m-d'),
            ])->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $duplicate = ProductsQuantityDiscountQuery::create()
                    ->filterByProductsMaster($quantityDiscount->getProductsMaster())
                    ->filterBySpan($quantityDiscount->getSpan())
                    ->filterByDomainsId($quantityDiscount->getDomainsId())
                    ->findOne($this->getDbConnection());

                if ($duplicate instanceof ProductsQuantityDiscount) {

                    $duplicate->setDiscount($quantityDiscount->getDiscount());
                    $duplicate->save($this->getDbConnection());

                } else {

                    $quantityDiscount->save($this->getDbConnection());

                }

                $this->get('session')->getFlashBag()->add('notice', 'admin.products.discount.saved');
            }
        }

        $quantityDiscounts = ProductsQuantityDiscountQuery::create()
            ->joinWithDomains()
            ->filterByProductsMaster($currentProduct->getSku())
            ->orderByDomainsId()
            ->find($this->getDbConnection());

        return $this->render('AdminBundle:Products:discount.html.twig', [
            'quantity_discounts'    => $quantityDiscounts,
            'form'                  => $form->createView(),
            'current_product'       => $currentProduct,
            'database' => $this->getRequest()->getSession()->get('database')
        ]);
    }

    /**
     * @param string $master
     * @param int    $domains_id
     * @param mixed  $span
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteQuantityDiscountAction($master, $domains_id, $span)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $discount = ProductsQuantityDiscountQuery::create()
            ->filterByProductsMaster($master)
            ->filterBySpan($span)
            ->filterByDomainsId($domains_id)
            ->findOne($this->getDbConnection());

        if ($discount instanceof ProductsQuantityDiscount) {
            $discount->delete($this->getDbConnection());

            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status'  => true,
                    'message' => $this->get('translator')->trans('delete.products.discount.success', [], 'admin')
                ]);
            }

            $this->get('session')->getFlashBag()->add('notice', 'delete.products.discount.success');

            return $this->redirect($this->generateUrl('admin_products_discount', ['product_id' => $master->getId()]));
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => false,
                'message' => $this->get('translator')->trans('delete.products.discount.failed', [], 'admin')
            ]);
        }

        $this->get('session')->getFlashBag()->add('notice', 'delete.products.discount.failed');

        return $this->redirect($this->generateUrl('admin_products_discount', ['product_id' => $master->getId()]));

    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteStylesAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $master = ProductsQuery::create()
            ->filterById($id)
            ->findOne($this->getDbConnection());

        if ($master instanceof Products) {
            $master->delete($this->getDbConnection());

            $this->get('session')->getFlashBag()->add('notice', 'delete.products.styles.success');

            return $this->redirect($this->generateUrl('admin_product', ['id' => $id]));
        }

        $this->get('session')->getFlashBag()->add('notice', 'delete.products.styles.failed');

        return $this->redirect($this->generateUrl('admin_product', ['id' => $id]));

    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteStyleAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $style = ProductsQuery::create()
            ->filterById($id)
            ->findOne($this->getDbConnection());

        if ($style instanceof Products) {
            $style->delete($this->getDbConnection());

            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status'  => true,
                    'message' => $this->get('translator')->trans('delete.products.style.success', [], 'admin')
                ]);
            }
            $this->get('session')->getFlashBag()->add('notice', 'delete.products.style.success');

            return $this->redirect($this->generateUrl('admin_product', ['id' => $id]));
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => false,
                'message' => $this->get('translator')->trans('delete.products.style.failed', [], 'admin')
            ]);
        }

        $this->get('session')->getFlashBag()->add('notice', 'delete.products.style.failed');

        return $this->redirect($this->generateUrl('admin_product', ['id' => $id]));

    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function addCategoryAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $requests = $this->get('request');
        $categoryId = $requests->get('category');
        $productId = $requests->get('product');

        $categoryToProduct = new ProductsToCategories();
        $categoryToProduct->setCategoriesId($categoryId);
        $categoryToProduct->setProductsId($productId);

        try {
            $categoryToProduct->save($this->getDbConnection());
        } catch (PropelException $e) {
            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status' => true,
                    'message' => $this->get('translator')->trans('save.changes.failed', [], 'admin')
                ]);
            }
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('save.changes.success', [], 'admin')
            ]);
        }
    }

    /**
     * @param int $category_id
     * @param int $product_id
     *
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteCategoryAction($category_id, $product_id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $categoryToProduct = ProductsToCategoriesQuery::create()
            ->filterByCategoriesId($category_id)
            ->filterByProductsId($product_id)
            ->findOne($this->getDbConnection());

        if ($categoryToProduct) {
            $categoryToProduct->delete($this->getDbConnection());
        }

        $node = new Categories();
        $node->setId($category_id);

        $this->get('event_dispatcher')->dispatch('category.node.deleted', new FilterCategoryEvent($node, null, $this->getDbConnection()));

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('delete.changes.success', [], 'admin'),
            ]);
        }
    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function addRelatedProductAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $requests = $this->get('request');
        $master   = $requests->get('master');
        $sku      = $requests->get('sku');

        $relatedProducts = new RelatedProducts();
        $relatedProducts->setMaster($master);
        $relatedProducts->setSku($sku);

        try {
            $relatedProducts->save($this->getDbConnection());
        } catch (PropelException $e) {
            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status'  => true,
                    'message' => $this->get('translator')->trans('save.changes.failed', [], 'admin')
                ]);
            }
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('save.changes.success', [], 'admin')
            ]);
        }
    }

    /**
     * @param string $master
     * @param string $sku
     *
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteRelatedProductAction($master, $sku)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $relatedProduct = RelatedProductsQuery::create()
            ->filterByMaster($master)
            ->filterBySku($sku)
            ->findOne($this->getDbConnection());

        if ($relatedProduct instanceof RelatedProducts) {
            $relatedProduct->delete($this->getDbConnection());
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('delete.changes.success', [], 'admin'),
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     * @throws \PropelException
     * @return Response
     */
    public function addReferenceAction(Request $request)
    {
        $imageId   = $request->get('image');
        $productId = $request->get('product');
        $color     = $request->get('color');

        $reference = new ProductsImagesProductReferences();
        $reference->setProductsImagesId($imageId);
        $reference->setProductsId($productId);
        $reference->setColor($color);

        try {
            $reference->save($this->getDbConnection());
        } catch (\PropelException $e) {
            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status'  => true,
                    'message' => $this->get('translator')->trans('save.changes.failed', [], 'admin')
                ]);
            }
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('save.changes.success', [], 'admin')
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function addReferenceGetColorsAction(Request $request)
    {
        $productId = $request->get('product');

        $images = ProductsImagesQuery::create()
            ->filterByProductsId($productId)
            ->groupBy('Color')
            ->find($this->getDbConnection());

        $allColors = [];

        foreach ($images as $image) {
            $allColors[] = $image->getColor();
        }

        return $this->json_response([
            'status'  => true,
            'message' => $this->get('translator')->trans('save.changes.failed', [], 'admin'),
            'data'    => $allColors
        ]);
    }

    /**
     * @param int $image_id
     * @param int $product_id
     *
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteReferenceAction($image_id, $product_id)
    {
        $productRef = ProductsImagesProductReferencesQuery::create()
            ->filterByProductsImagesId($image_id)
            ->filterByProductsId($product_id)
            ->findOne($this->getDbConnection());

        if ($productRef) {
            $productRef->delete($this->getDbConnection());
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('delete.imageReference.success', [], 'admin'),
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     * @throws \PropelException
     * @return Response
     */
    public function addImageToCategoryAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $imageId    = $request->get('image');
        $categoryId = $request->get('category');
        $image      = ProductsImagesQuery::create()->findOneById($imageId);

        $reference = new ProductsImagesCategoriesSort();
        $reference->setProductsId($image->getProductsId());
        $reference->setProductsImagesId($imageId);
        $reference->setCategoriesId($categoryId);

        $c = ProductsToCategoriesQuery::create()
            ->filterByProductsId($image->getProductsId())
            ->filterByCategoriesId($categoryId)
            ->findOne($this->getDbConnection());

        if (!$c instanceof ProductsToCategories) {
            $c = new ProductsToCategories();
            $c->setProductsId($image->getProductsId());
            $c->setCategoriesId($categoryId);
            $c->save($this->getDbConnection());
        }

        try {
            $reference->save($this->getDbConnection());

        } catch (PropelException $e) {
            if ($this->getFormat() == 'json') {
                return $this->json_response([
                    'status'  => true,
                    'message' => $this->get('translator')->trans('save.changes.failed', [], 'admin')
                ]);
            }
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('save.changes.success', [], 'admin')
            ]);
        }
    }

    /**
     * @param int $image_id
     * @param int $category_id
     *
     * @return Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteImageFromCategoryAction($image_id, $category_id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $ref = ProductsImagesCategoriesSortQuery::create()
            ->filterByProductsImagesId($image_id)
            ->filterByCategoriesId($category_id)
            ->findOne($this->getDbConnection());

        if ($ref) {
            $productId = $ref->getProductsId();
            $ref->delete($this->getDbConnection());

            $imageCount = ProductsImagesCategoriesSortQuery::create()
                ->filterByProductsId($productId)
                ->filterByCategoriesId($category_id)
                ->count($this->getDbConnection());

            if (0 === $imageCount) {
                ProductsToCategoriesQuery::create()
                    ->filterByProductsId($productId)
                    ->filterByCategoriesId($category_id)
                    ->delete($this->getDbConnection());
            }
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('delete.success', [], 'admin'),
            ]);
        }
    }

    /**
     * @param int $category_id
     *
     * @return Response
     */
    public function sortAction($category_id)
    {
        $currentCategory = CategoriesQuery::create()
            ->joinWithI18n()
            ->filterById($category_id)
            ->findOne($this->getDbConnection());

        $categoriesResult = CategoriesQuery::create()
            ->where('categories.PARENT_ID IS NOT NULL')
            ->joinWithI18n()
            ->orderByParentId()
            ->find($this->getDbConnection());

        $categories = [];
        foreach ($categoriesResult as $category) {
            $tmp = explode('_', $category->getContext());
            $categories[$category->getContext()]['group'] = $tmp[0];
            $categories[$category->getContext()]['title']= $category->getTitle();
            $categories[$category->getContext()]['id']= $category->getId();
        }

        $products = ProductsImagesCategoriesSortQuery::create()
            ->useProductsQuery()
                ->where('products.MASTER IS NULL')
            ->endUse()
            ->joinWithProducts()
            ->useProductsImagesQuery()
                ->filterByType('overview')
                ->groupByImage()
            ->endUse()
            ->joinWithProductsImages()
            ->orderBySort()
            ->filterByCategoriesId($category_id)
            ->find($this->getDbConnection());

        $records = [];
        foreach ($products as $record) {
            $product = $record->getProducts();

            $records[] = [
                'sku'       => $product->getSku(),
                'id'        => $product->getId(),
                'title'     => $product->getSku(),
                'image'     => $record->getProductsImages()->getImage(),
                'color'     => $record->getProductsImages()->getColor(),
                'is_active' => $product->getIsActive()
            ];
        }

        return $this->render('AdminBundle:Products:sort.html.twig', [
            'products'          => $records,
            'current_category'  => $currentCategory,
            'categories'        => $categories,
            'database'          => $this->getRequest()->getSession()->get('database')
        ]);
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     * @throws \PropelException
     * @return Response
     */
    public function updateSortAction(Request $request)
    {
        $products = $request->get('data');

        $sort = 0;
        foreach ($products as $product) {
            $itemParts  = explode('-', substr($product, 5));
            $productId  = $itemParts[0];
            $color      = $itemParts[1];
            $categoryId = $itemParts[2];

            $images = ProductsImagesQuery::create()
                ->filterByProductsId($productId)
                ->filterByColor($color)
                ->find();

            $imagesId = [];
            foreach ($images as $image) {
                $imagesId[] = $image->getId();
            }

            ProductsImagesCategoriesSortQuery::create()
                ->filterByCategoriesId($categoryId)
                ->filterByProductsId($productId)
                ->filterByProductsImagesId($imagesId)
                ->update(['Sort' => $sort], $this->getDbConnection());

            $node = new Categories();
            $node->setId($categoryId);
            $this->get('event_dispatcher')->dispatch('category.product_sort.update', new FilterCategoryEvent($node, null, $this->getDbConnection()));

            $sort++;
        }

        if ($this->getFormat() == 'json') {
            return $this->json_response([
                'status'  => true,
                'message' => $this->get('translator')->trans('save.changes.success', [], 'admin')
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function stockAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $stock  = $this->container->get('stock');

        // Switch to the correct warehouse location.
        $databaseConnectionName = $request->getSession()->get('database', 'pdldbdk1');
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName($databaseConnectionName);
        }

        $parser = new \PropelCSVParser();
        $parser->delimiter = ';';

        $products = ProductsQuery::create()
            ->groupById()
            ->orderBySku()
            ->filterByMaster(null, \Criteria::ISNOTNULL)
            ->find($this->getDbConnection());

        $stock->prime($products);

        $stockData = [];
        $stockData[0] = ['SKU', 'STOCK'];

        foreach ($products as $product) {
            foreach ($stock->get($product, true) as $level) {
                if (!is_array($level) || empty($level['date']) || (0 == $level['quantity'])) {
                    $stockData[] = [$product->getSku(), 0];
                    continue;
                }

                $stockData[] = [$product->getSku(), $level['quantity']];
            }
        }

        // Switch to the default warehouse location.
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName('pdldbdk1');
        }

        return new Response(
            $parser->toCSV($stockData, true, false),
            200,
            [
                 'Content-Type' => 'text/csv',
                 'Content-Disposition' => sprintf('attachment; filename="stock_' . date('Y-m-d', time()) . '.csv"', 'stock_' . date('Y-m-d', time()) .'.csv')
            ]
        );
    }

    /**
     * Gives an overview of the stock state on a style
     *
     * @param Request  $request
     * @param Products $product
     * @param int      $category_id
     * @param int      $subcategory_id
     *
     * @return Response
     *
     * @ParamConverter("product", class="Hanzo\Model\Products")
     */
    public function viewStockAction(Request $request, Products $product, $category_id, $subcategory_id)
    {
        $stock = $this->container->get('stock');

        // Switch to the correct warehouse location.
        $databaseConnectionName = $request->getSession()->get('database', 'pdldbdk1');
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName($databaseConnectionName);
        }

        $products = ProductsQuery::create()
            ->filterByMaster($product->getSku())
            ->orderBySku()
            ->find($this->getDbConnection());

        $stock->prime($products);


        $items       = [];
        $skus        = [];
        $skusInStock = [];

        foreach ($products as $product) {
            $current    = '';
            $sku        = $product->getSku();
            $skus[$sku] = [
                'id'         => $product->getId(),
                'marked_out' => $product->getIsOutOfStock()
            ];

            foreach ($stock->get($product, true) as $key => $level) {
                if ('total' === $key) {
                    continue;
                }

                $skusInStock[$sku] = $sku;

                $data = [
                    'sku'          => $sku,
                    'date'         => '-',
                    'stock'        => 0,
                    'reservations' => 0,
                    'marked_out'   => ($product->getIsOutOfStock() ? 'Ja' : 'Nej'),
                ];

                // only show reservations once
                if ($current != $sku) {
                    $current = $sku;

                    $data['reservations'] = $stock->getProductReservations($product->getId());
                }

                if ($level['date']) {
                    $data['date'] = $level['date'];
                }

                if ($level['quantity']) {
                    $data['stock'] = $level['quantity'];
                }


                $items[] = $data;
            }
        }

        // we add any products not on stock to show the full stock/reservation "picture" for a given style
        foreach ($skus as $sku => $product) {
            if (empty($skusInStock[$sku])) {
                $items[] = [
                    'sku'          => $sku,
                    'date'         => '-',
                    'stock'        => 0,
                    'reservations' => $stock->getProductReservations($product['id']),
                    'marked_out'   => ($product['marked_out'] ? 'Ja' : 'Nej'),
                ];
            }
        }

        // Switch to the correct warehouse location.
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName('pdldbdk1');
        }

        return $this->render('AdminBundle:Products:stock.html.twig', [
            'category_id'    => $category_id,
            'subcategory_id' => $subcategory_id,
            'items'          => $items,
            'database'       => $request->getSession()->get('database')
        ]);
    }

    /**
     * @param Request  $request
     * @param Products $product
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @ParamConverter("product", class="Hanzo\Model\Products")
     */
    public function purgeStockAction(Request $request, Products $product)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $stock = $this->container->get('stock');

        // Switch to the correct warehouse location.
        $databaseConnectionName = $request->getSession()->get('database', 'pdldbdk1');
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName($databaseConnectionName);
        }

        $stock->flushStyle($product);

        // Switch to the correct warehouse location.
        if ('pdldbdk1' !== $databaseConnectionName) {
            $stock->changeLocationByConnectionName('pdldbdk1');
        }

        $this->container->get('session')->getFlashBag()->add('notice', 'Lageret for "'.$product->getSku().'" er nu nulstillet.');

        return $this->redirect($this->generateUrl('admin_products_list', ['range' => $request->query->get('range')]));
    }

    /**
     * @param Request $request
     *
     * @author Henrik Farre <hf@bellcom.dk>
     *
     * @return Response
     */
    public function exportLanguagesAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('admin'));
        }

        $exporter = new LanguageExporter();
        $exporter->setDBConnection($this->getDbConnection());
        $csvData = $exporter->getDataAsCsv();

        return new Response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_languages.csv"',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     */
    public function importLanguagesAction(Request $request)
    {
        return $this->render('AdminBundle:Products:import_languages.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @author Henrik Farre <hf@bellcom.dk>
     */
    public function performImportLanguagesAction(Request $request)
    {
        $fileTmp   = $request->files->get('language_file');
        $tmpDir    = sys_get_temp_dir();
        $fileName  = 'language_file.csv';
        $inputFile = $tmpDir.'/'.$fileName;
        $locale    = $request->request->get('import_locale');
        $errors    = [];
        $sql       = "UPDATE products_i18n SET title = :title WHERE id = :id AND locale = :locale";
        $line      = 0;

        ini_set("auto_detect_line_endings", true);

        $fileTmp->move($tmpDir, $fileName);

        if (is_file($inputFile)) {
            $conn = $this->getDbConnection();
            $stmt = $conn->prepare($sql);

            $handle = fopen($inputFile, 'r');

            while (($data = fgetcsv($handle, 2048, ';', '"')) !== false) {
                $line++;

                if ($line == 1) {
                    continue;
                }

                if (empty($data[0]) || empty($data[2])) {
                    $errors[] = 'Line '.$line.' does not contain the required id and title';
                    continue;
                }

                $values = [
                    'id'     => $data[0],
                    'title'  => trim($data[2]),
                    'locale' => $locale,
                ];

                $stmt->execute($values);
            }

            fclose($handle);
            unlink($inputFile);
        } else {
            $errors[] = 'Uploaded file was not found';
        }

        if (!empty($errors)) {
            $this->get('session')->getFlashBag()->add('warning', 'Some errors happened: '. implode("<br>", $errors));
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'OK, file uploade and products updated');
        }

        return $this->redirect($this->generateUrl('admin_products_language_import'));
    }
}
