<?php

namespace ExWife\Engine\Cms\ContentBlock\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @route("/manage")
 * Class ContentBlockController
 * @package ExWife\Engine\Cms\ContentBlock\Controller
 */
class ContentBlockController extends BaseController
{
    use ManageControllerTratis;

    /**
     * @route("/section/{section}/orms/ContentBlockTag", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentBlockTags(Request $request, $section)
    {
        $className = 'ContentBlockTag';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPaginationWithDataType($request, $model, $section);
        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/core/orms-pagination-with-datatype.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/ContentBlockDefault", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentBlockDefaults(Request $request, $section)
    {
        $className = 'ContentBlockDefault';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPaginationWithDataType($request, $model, $section);
        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/core/orms-pagination-with-datatype.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/ContentBlock", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ContentBlocks(Request $request, $section)
    {
        $className = 'ContentBlock';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPaginationWithDataType($request, $model, $section);
        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/core/orms-pagination-with-datatype.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/ContentBlockDefault/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/ContentBlockDefault/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function contentBlockDefault(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'ContentBlockDefault';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/contentblock/contentblock-default.twig",
        ]);
    }

    /**
     * @route("/section/{section}/orms/ContentBlock/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/ContentBlock/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function contentBlock(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'ContentBlock';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/contentblock/contentblock.twig",
        ]);
    }
}
