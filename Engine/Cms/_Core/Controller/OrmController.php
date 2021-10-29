<?php

namespace ExWife\Engine\Cms\_Core\Controller;

use BlueM\Tree;
use ExWife\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\_Core\Model\Form\OrmForm;
use ExWife\Engine\Cms\_Core\Service\CmsService;
use ExWife\Engine\Cms\_Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\_Core\Model\Model;
use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class OrmController
 * @package ExWife\Engine\Cms\_Core\Controller
 */
class OrmController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/section/{section}/orms/{className}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orms(Request $request, $section, $className)
    {
        $model = UtilsService::getModelFromName($className, $this->_connection);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        switch ($model->listingType) {
            case 1:
                $result = $this->_ormsDragDrop($request, $model, $section);
                break;
            case 2:
                $result = $this->_ormsPagination($request, $model, $section);
                break;
            case 3:
                $result = $this->_ormsTree($request, $model, $section);
                break;
            default:
                throw new NotFoundHttpException();
        }

        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render($result['template'] ?? null, $params);
    }

    /**
     * @route("/section/{section}/orms/{className}/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/{className}/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function orm(Request $request, $section, $className, $id, $versionUuid = null)
    {
        return $this->_orm($request, $section, $className, $id, $versionUuid);
    }

    /**
     * @route("/section/{section}/orms/{className}/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param $ormId
     * @return mixed
     */
    public function copyOrm(Request $request, $section, $className, $id)
    {
        return $this->_copyOrm($request, $section, $className, $id);
    }
}
