<?php

namespace SymfonyCMS\Engine\Cms\File\Controller;

use SymfonyCMS\Engine\Cms\_Core\Base\Controller\BaseController;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;

use Doctrine\DBAL\Connection;

use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use SymfonyCMS\Engine\Cms\File\Form\FileForm;
use SymfonyCMS\Engine\Cms\File\Service\FileManagerService;
use MillenniumFalcon\Core\Service\AssetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * @Route("/manage")
 * Class FileController
 * @package SymfonyCMS\Engine\Cms\File\Controller
 */
class FileController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @Route("/section/files")
     * @param Request $request
     */
    public function manageFiles(Request $request)
    {
        $returnQuery =  $request->getQueryString() ? '?' . $request->getQueryString() : '';

        /** @var Model $model */
        $model = UtilsService::getModelFromName('Asset', $this->_connection);
        $params = array_merge($this->getParamsByRequest($request), [
            'model' => $model,
            'returnQuery' => $returnQuery,
        ]);
        return $this->render("/cms/{$this->_theme}/file/files.twig", $params);
    }

    /**
     * @route("/section/files/orms/Asset/{id}")
     * @route("/section/files/orms/Asset/{id}/version/{versionUuid}")
     * @return Response
     */
    public function asset(Request $request, $id, $versionUuid = null, FileManagerService $fileManagerService)
    {
        $className = 'Asset';
        $section = 'files';

        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/file/file.twig",
            'formClass' => FileForm::class,
            'callback' => function($form, $orm) use ($fileManagerService) {
                $uploadedFile = $form['file']->getData();
                if ($uploadedFile) {
                    $fileManagerService->processUploadedFileWithAsset($uploadedFile, $orm);
                }
            },
        ]);
    }
}
