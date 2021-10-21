<?php

namespace ExWife\Engine\Cms\Order\Controller;

use BlueM\Tree;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class OrmController
 * @package ExWife\Engine\Cms\Core\Controller
 */
class OrderController extends BaseController
{
    use ManageControllerTratis;

    /**
     * @route("/section/{section}/orms/Order", requirements={"section" = ".*"})
     * @param Request $request
     * @return Response
     */
    public function orders(Request $request, $section)
    {
        $className = 'Order';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPagination($request, $model, $section, function ($request, $whereSql, $whereParams) {
            $params = [];

            $whereSql .= ($whereSql ? ' AND ' : '') . '(m.category != 0)';

            return array_merge($params, [
                'whereSql' => $whereSql,
                'whereParams' => $whereParams,
            ]);
        });

        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/order/orders.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/Order/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/Order/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function order(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'Order';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/order/order.twig",
        ]);
    }
}
