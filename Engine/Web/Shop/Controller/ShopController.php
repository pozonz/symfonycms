<?php

namespace SymfonyCMS\Engine\Web\Shop\Controller;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\BaseController;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;
use SymfonyCMS\Engine\Web\_Core\Base\Controller\Traits\WebControllerTrait;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;


class ShopController extends BaseController
{
    use WebControllerTrait;

    /**
     * @route("/shop")
     * @route("/shop/{categories}", requirements={"categories" = ".*"})
     * @param Request $request
     * @return mixed
     */
    public function shop(Request $request, $categories = null)
    {
        $category = null;
        if ($categories) {
            $categories = explode('/', $categories);
            $category = array_pop($categories);
        }

        $params = array_merge($this->getParamsByUrl('/shop'), $this->filterProductResult($request, $category));
        return $this->render('shop/products.twig', $params);
    }

    /**
     * @route("/product/{slug}")
     * @param Request $request
     * @return mixed
     */
    public function product(Request $request, $slug)
    {
        $params = $this->getParamsByRequest($request);

        $fullClass = UtilsService::getFullClassFromName('Product');
        $params['orm'] = $fullClass::getBySlug($this->_connection, $slug);
        return $this->render('shop/product.twig', $params);
    }

    /**
     * @route("/product/variant/price")
     * @param Request $request
     * @return JsonResponse
     * @throws RedirectException
     */
    public function productPrice(Request $request)
    {
        $uniqid = $request->get('uniqid');
        $fullClass = UtilsService::getFullClassFromName('ProductVariant');
        $variant = $fullClass::getByField($this->_connection, '_uniqid', $uniqid);

        if (!$variant) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'html' => $this->_environment->render('shop/includes/product-price.twig', [
                'product' => $variant->objProduct(),
                'variant' => $variant,
            ]),
        ]);
    }

    /**
     * @route("/products/filter/shop")
     * @route("/products/filter/shop/{categories}", requirements={"categories" = ".*"})
     * @param Request $request
     * @return JsonResponse
     * @throws RedirectException
     */
    public function productsFilter(Request $request, $categories = null)
    {
        $category = null;
        if ($categories) {
            $categories = explode('/', $categories);
            $category = array_pop($categories);
        }

        $params = $this->filterProductResult($request, $category);
        return new JsonResponse([
            'productHtml' => $this->_environment->render('shop/includes/product-results.twig', $params),
            'brandHtml' => $this->_environment->render('shop/includes/product-brands.twig', $params),
            'total' => $params['total'],
        ]);
    }

    /**
     * @param Request $request
     * @param null $category
     * @return array
     */
    protected function filterProductResult(Request $request, $category = null)
    {
        $limit = $_ENV['PRODUCT_LISTING_LIMIT'] ?: 21;
        $productCategorySlug = $category ?? $request->get('category');
        $productBrandSlug = $request->get('brand');
        $productKeyword = $request->get('keyword');
        $pageNum = $request->get('pageNum') ?: 1;
        $sortby = $request->get('sortby');
        $sort = 'CAST(m.pageRank AS UNSIGNED)';
        $order = 'DESC';

        if ($sortby == 'price-high-to-low') {
            $sort = 'CAST(m.price AS UNSIGNED)';
            $order = 'DESC';
        } elseif ($sortby == 'price-low-to-high') {
            $sort = 'CAST(m.price AS UNSIGNED)';
            $order = 'ASC';
        } elseif ($sortby == 'newest') {
            $sort = 'm._added';
            $order = 'DESC';
        } elseif ($sortby == 'oldest') {
            $sort = 'm._added';
            $order = 'ASC';
        }

        $productCategoryFullClass = UtilsService::getFullClassFromName('ProductCategory');
        $productBrandFullClass = UtilsService::getFullClassFromName('ProductBrand');
        $productFullClass = UtilsService::getFullClassFromName('Product');

        $allBrands = $productBrandFullClass::active($this->_connection);
        $brands = array_filter($allBrands, function ($itm) use ($limit, $productCategorySlug, $productKeyword, $pageNum, $sortby, $sort, $order, $allBrands) {
            $result = $this->_filterProductResult($limit, $productCategorySlug, $itm->_slug, null, $pageNum, $sortby, $sort, $order, $allBrands, true);
            return $result['total']['count'] > 0 ? 1 : 0;
        });

        return array_merge($this->_filterProductResult($limit, $productCategorySlug, $productBrandSlug, $productKeyword, $pageNum, $sortby, $sort, $order, $brands), [
            'brands' => $brands,
        ]);
    }

    /**
     * @param $limit
     * @param $productCategorySlug
     * @param $productBrandSlug
     * @param $productKeyword
     * @param $pageNum
     * @param $sortby
     * @param $sort
     * @param $order
     * @param $brands
     * @return array
     */
    protected function _filterProductResult($limit, $productCategorySlug, $productBrandSlug, $productKeyword, $pageNum, $sortby, $sort, $order, $brands, $productCountOnly = false)
    {
        $productCategoryFullClass = UtilsService::getFullClassFromName('ProductCategory');
        $productBrandFullClass = UtilsService::getFullClassFromName('ProductBrand');
        $productFullClass = UtilsService::getFullClassFromName('Product');

        $categories = new \BlueM\Tree($productCategoryFullClass::active($this->_connection, [
            "select" => 'm.id AS id, m.parentId AS parent, m.title, m._slug AS slug, m._status AS status',
            "sort" => 'm._rank',
            "order" => 'ASC',
            "orm" => 0,
        ]), [
            'rootId' => null,
        ]);

        $selectedProductCategory = $productCategoryFullClass::getBySlug($this->_connection, $productCategorySlug);
        if ($selectedProductCategory) {
            $selectedProductCategory = $categories->getNodeById($selectedProductCategory->id);
        }

        $selectedProductBrand = null;
        foreach ($brands as $itm) {
            if ($itm->_slug == $productBrandSlug) {
                $selectedProductBrand = $itm;
            }
        }

        $whereSql = '';
        $params = [];

        if ($selectedProductCategory) {
            $descendants = $selectedProductCategory->getDescendants();
            $selectedProductCategoryIds = array_merge([$selectedProductCategory->get('id')], array_map(function ($itm) {
                return $itm->getId();
            }, $descendants));

            $s = array_map(function ($itm) {
                return "m.categories LIKE ?";
            }, $selectedProductCategoryIds);
            $p = array_map(function ($itm) {
                return '%"' . $itm . '"%';
            }, $selectedProductCategoryIds);
            $whereSql .= ($whereSql ? ' AND ' : '') . '(' . implode(' OR ', $s) . ')';
            $params = array_merge($params, $p);
        }

        if ($selectedProductBrand) {
            $whereSql .= ($whereSql ? ' AND ' : '') . '(m.brand = ?)';
            $params = array_merge($params, [$selectedProductBrand->id]);
        }

        if ($productKeyword) {
            $whereSql .= ($whereSql ? ' AND ' : '') . '(m.title LIKE ? OR m.sku LIKE ? OR m.description LIKE ?)';
            $params = array_merge($params, ['%' . $productKeyword . '%', '%' . $productKeyword . '%', '%' . $productKeyword . '%']);
        }

        $products = null;
        if (!$productCountOnly) {
            $products = $productFullClass::active($this->_connection, [
                'whereSql' => $whereSql,
                'params' => $params,
                'page' => $pageNum,
                'limit' => $limit,
                'sort' => $sort,
                'order' => $order,
                'debug' => 0,
            ]);
        }

        $total = $productFullClass::active($this->_connection, [
            'whereSql' => $whereSql,
            'params' => $params,
            'count' => 1
        ]);

        $pageTotal = ceil($total['count'] / $limit);

        return [
            'orms' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'selectedProductCategory' => $selectedProductCategory,
            'selectedProductBrand' => $selectedProductBrand,
            'productKeyword' => $productKeyword,
            'pageNum' => $pageNum,
            'pageTotal' => $pageTotal,
            'total' => $total,
            'sortby' => $sortby,
            'limit' => $limit,
        ];
    }
}
