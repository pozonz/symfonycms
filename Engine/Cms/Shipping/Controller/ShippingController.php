<?php

namespace SymfonyCMS\Engine\Cms\Shipping\Controller;

use BlueM\Tree;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;
use SymfonyCMS\Engine\Cms\_Core\Model\Form\OrmForm;
use SymfonyCMS\Engine\Cms\_Core\ORM\Page;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\BaseController;

use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
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
 * @package SymfonyCMS\Engine\Cms\_Core\Controller
 */
class ShippingController extends BaseController
{
    use ManageControllerTrait;

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
