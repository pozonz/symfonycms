<?php

namespace ExWife\Engine\Cms\Shipping\Controller;

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
class ShippingController extends BaseController
{
    use ManageControllerTratis;

    /**
     * @route("/section/{section}/orms/ShippingByWeight/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/ShippingByWeight/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function shipping(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'ShippingByWeight';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/shipping/shipping.twig",
        ]);
    }

    /**
     * @route("/section/{section}/orms/ShippingByWeight/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function copyShipping(Request $request, $section, $id)
    {
        $className = 'ShippingByWeight';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/shipping/shipping.twig"
        ]);
    }
}
