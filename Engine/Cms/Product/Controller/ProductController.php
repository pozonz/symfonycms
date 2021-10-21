<?php

namespace ExWife\Engine\Cms\Product\Controller;

use BlueM\Tree;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Model\Form\OrmForm;
use ExWife\Engine\Cms\Core\ORM\Page;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use MillenniumFalcon\Core\Service\ModelService;
use MillenniumFalcon\Core\Tree\RawData;
use MillenniumFalcon\Core\Twig\Extension;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class OrmController
 * @package ExWife\Engine\Cms\Core\Controller
 */
class ProductController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @param ContainerInterface $container
     * @return ContainerInterface|null
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        return parent::setContainer($container);
    }

    /**
     * @route("/section/{section}/orms/Product", requirements={"section" = ".*"})
     * @param Request $request
     * @return Response
     */
    public function products(Request $request, $section)
    {
        $className = 'Product';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPagination($request, $model, $section, function ($request, $whereSql, $whereParams) {
            $params = [];

            $productCategoryFullClass = UtilsService::getFullClassFromName('ProductCategory');
            $productBrandFullClass = UtilsService::getFullClassFromName('ProductBrand');

            $params['categories'] = new \BlueM\Tree($productCategoryFullClass::data($this->_connection, [
                "select" => 'm.id AS id, m.parentId AS parent, m.title, m._slug AS slug, m._status',
                "sort" => 'm._rank',
                "order" => 'ASC',
                "orm" => 0,
            ]), [
                'rootId' => null,
            ]);

            $params['brands'] = $productBrandFullClass::data($this->_connection, [
                'sort' => 'm.title',
            ]);

            $filterStatus = $request->get('status') === null ? 'all' : $request->get('status');
            $filterCategories = $request->get('category') ?: [];
            $filterBrands = $request->get('brand') ?: [];
            $filterType = $request->get('type');
            $filterDateStart = $request->get('dateStart');
            $filterDateEnd = $request->get('dateEnd');
            $params['filterStatus'] = $filterStatus;
            $params['filterCategories'] = $filterCategories;
            $params['filterBrands'] = $filterBrands;
            $params['filterType'] = $filterType;
            $params['filterDateStart'] = $filterDateStart;
            $params['filterDateEnd'] = $filterDateEnd;

            $whereSql = '';
            $whereParams = [];

            if ($filterStatus !== 'all') {
                if ($filterStatus === '0') {
                    $whereSql .= ($whereSql ? ' AND ' : '') . '(m._status = 0 OR m._status IS NULL)';
                } else {
                    $whereSql .= ($whereSql ? ' AND ' : '') . '(m._status = 1)';
                }
            }

            if (count($filterCategories)) {
                $s = '';
                $p = [];

                $filterCategoryIds = [];
                foreach ($filterCategories as $filterCategory) {
                    $ormCategory = $productCategoryFullClass::getBySlug($this->_connection, $filterCategory);
                    if ($ormCategory) {
                        $nodeCategory = $params['categories']->getNodeById($ormCategory->id);
                        $descendants = $nodeCategory->getDescendants();
                        $filterCategoryIds = array_merge($filterCategoryIds, [$nodeCategory->get('id')], array_map(function ($itm) {
                            return $itm->id;
                        }, $descendants));
                    }
                }

                $s = array_map(function ($itm) {
                    return "m.categories LIKE ?";
                }, $filterCategoryIds);
                $s = '(' . implode(' OR ', $s) . ')';

                $p = array_map(function ($itm) {
                    return '%"' . $itm . '"%';
                }, $filterCategoryIds);

                $whereSql .= ($whereSql ? ' AND ' : '') . "($s)";
                $whereParams = array_merge($whereParams, $p);
            }

            if (count($filterBrands)) {
                $s = '';
                $p = [];

                foreach ($filterBrands as $filterBrand) {
                    $ormBrand = $productBrandFullClass::getBySlug($this->_connection, $filterBrand);
                    if ($ormBrand) {
                        $s .= ($s ? ' OR ' : '') .  'm.brand = ?';
                        $p[] = $ormBrand->id;
                    }
                }
                $whereSql .= ($whereSql ? ' AND ' : '') . "($s)";
                $whereParams = array_merge($whereParams, $p);
            }

            if ($filterDateStart) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m._added >= ?)';
                $whereParams[] = date('Y-m-d 00:00:00', strtotime($filterDateStart));
            }

            if ($filterDateEnd) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m._added <= ?)';
                $whereParams[] = date('Y-m-d 23:59:59', strtotime($filterDateEnd));
            }

            if ($filterType == 1) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m.outOfStock > 0)';
            }

            if ($filterType == 2) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m.lowStock > 0)';
            }

            if ($filterType == 3) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m.thumbnail IS NULL)';
            }

            return array_merge($params, [
                'whereSql' => $whereSql,
                'whereParams' => $whereParams,
            ]);
        });

        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/product/products.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/Product/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/Product/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function product(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'Product';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/product/product.twig",
        ]);
    }

    /**
     * @route("/section/{section}/orms/Product/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function copyProduct(Request $request, $section, $id)
    {
        $className = 'Product';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/product/product.twig",
            'existOrmCallback' => function($orm) {
                $uuid = Uuid::uuid4()->toString();
                $variants = $orm->objVariants();
                foreach ($variants as $itm) {
                    $itm->id = null;
                    $itm->_uniqid = Uuid::uuid4()->toString();
                    $itm->productUniqid = $uuid;
                    $itm->save();
                }
                $orm->productUniqid = $uuid;

                return $orm;
            }
        ]);
    }

    /**
     * @route("/section/{section}/orms/ProductVariant/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/ProductVariant/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function productVariant(Request $request, $section, $id, $versionUuid = null, ?Profiler $profiler)
    {
        if ($_SERVER['APP_ENV'] == 'dev') {
            $this->container->get('profiler')->disable();
        }

        $className = 'ProductVariant';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/product/product-variant.twig",
            'allowNodeNotFound' => 1,
        ]);
    }

    /**
     * @route("/section/{section}/orms/ProductVariant/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function copyProductVariant(Request $request, $section, $id)
    {
        if ($_SERVER['APP_ENV'] == 'dev') {
            $this->container->get('profiler')->disable();
        }

        $className = 'ProductVariant';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/product/product-variant.twig",
            'allowNodeNotFound' => 1,
        ]);
    }

    /**
     * @route("/product/variants")
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieveProductVariants(Request $request)
    {
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array('ProductVariant', $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        $uniqid = $request->get('uniqid');
        $data = [];
        if ($uniqid) {
            $fullClass = UtilsService::getFullClassFromName('ProductVariant');
            $data = $fullClass::data($this->_connection, [
                'whereSql' => 'm.productUniqid = ?',
                'params' => [$uniqid],
            ]);
        }

        return new JsonResponse($data);
    }
}
