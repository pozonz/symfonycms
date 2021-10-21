<?php

namespace ExWife\Engine\Cms\File\Controller;

use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;

use Doctrine\DBAL\Connection;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Form\FileForm;
use ExWife\Engine\Cms\File\Service\FileManagerService;
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
 * @package ExWife\Engine\Cms\File\Controller
 */
class FileController extends BaseController
{
    use ManageControllerTratis;

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
