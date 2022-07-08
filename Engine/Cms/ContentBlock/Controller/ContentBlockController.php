<?php

namespace SymfonyCMS\Engine\Cms\ContentBlock\Controller;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\BaseController;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;

use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use SymfonyCMS\Engine\Cms\Page\Service\PageService;
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
 * @package SymfonyCMS\Engine\Cms\ContentBlock\Controller
 */
class ContentBlockController extends BaseController
{
    use ManageControllerTrait;

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
            'callback' => function($form, $orm) {
                PageService::createContentBlockFile($orm);
            },
        ]);
    }

    /**
     * @route("/section/{section}/orms/ContentBlock/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param $ormId
     * @return mixed
     */
    public function copyContentBlock(Request $request, $section, $id)
    {
        $className = 'ContentBlock';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/contentblock/contentblock.twig",
            'callback' => function($form, $orm) {
                PageService::createContentBlockFile($orm);
            },
        ]);
    }
}
