<?php

namespace ExWife\Engine\Cms\File\Controller;

use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;

use Doctrine\DBAL\Connection;

use GeoIp2\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * @Route("/manage")
 * Class FileController
 * @package ExWife\Engine\Cms\File\Controller
 */
class FileManagerController extends BaseController
{
    use ManageControllerTrait;

    protected $fileManagerService;

    /**
     * ManageControllerTrait constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     * @param Security $security
     * @param SessionInterface $session
     * @param CmsService $cmsService
     */
    public function __construct(
        Connection $connection,
        KernelInterface $kernel,
        Environment $environment,
        Security $security,
        SessionInterface $session,
        CmsService $cmsService,
        FileManagerService $fileManagerService
    )
    {
        parent::__construct($connection, $kernel, $environment, $security, $session);

        $this->_cmsService = $cmsService;
        $this->_fileManagerService = $fileManagerService;
        $this->_theme = CmsService::getTheme();
    }


    /**
     * @route("/file/get/file")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileGetFile(Request $request)
    {
        $id = $request->get('id');
        $fullClass = UtilsService::getFullClassFromName('Asset');
        $orm = $fullClass::getByField($this->_connection, 'code', $id);
        if (!$orm) {
            $orm = $fullClass::getById($this->_connection, $id);
        }
        return new JsonResponse($orm);
    }

    /**
     * @Route("/file/folders")
     * @param Request $request
     */
    public function fileFolders(Request $request)
    {
        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $this->_session->set('currentFolderId', $currentFolderId);

        return new JsonResponse(array(
            'folders' => $this->_fileManagerService->getFolderRoot($currentFolderId),
        ));
    }

    /**
     * @Route("/file/files")
     * @param Request $request
     */
    public function fileFiles(Request $request)
    {
        $keyword = $request->get('keyword') ?: '';
        $currentFolderId = $request->get('currentFolderId') ?: 0;
        $pageNum = $request->get('pageNum') ?: 1;
        $total = 0;

        $this->_session->set('currentFolderId', $currentFolderId);
        $fullClass = UtilsService::getFullClassFromName('Asset');
        if ($keyword) {
            $limit = 240;

            $data = $fullClass::data($this->_connection, [
                'whereSql' => 'm.isFolder = 0 AND (m.title LIKE ? OR m.code LIKE ?)',
                'params' => array("%$keyword%", "%$keyword%"),
                'page' => $pageNum,
                'limit' => $limit,
            ]);
            $total = $fullClass::data($this->_connection, [
                'whereSql' => 'm.isFolder = 0 AND (m.title LIKE ? OR m.code LIKE ?)',
                'params' => array("%$keyword%", "%$keyword%"),
                'count' => 1,
            ]);

        } else {
            $limit = 239;

            $data = $fullClass::data($this->_connection, [
                'whereSql' => 'm.isFolder = 0 AND m.parentId = ?',
                'params' => array($currentFolderId),
                'page' => $pageNum,
                'limit' => $limit,
            ]);
            $total = $fullClass::data($this->_connection, [
                'whereSql' => 'm.isFolder = 0 AND m.parentId = ?',
                'params' => array($currentFolderId),
                'count' => 1,
            ]);
        }
        $total = ceil($total['count'] / $limit);

//        $fullClass = ModelService::fullClass($this->_connection, 'AssetOrm');
//        $modelName = $request->get('modelName');
//        $attributeName = $request->get('attributeName');
//        $ormId = $request->get('ormId');
//        if ($modelName && $attributeName && $ormId) {
//            $assetOrmMap = array();
//            $result = $fullClass::data($this->_connection, array(
//                'whereSql' => 'm.modelName = ? AND m.attributeName = ? AND ormId = ?',
//                'params' => array($modelName, $attributeName, $ormId),
//            ));
//            foreach ($result as $itm) {
//                $assetOrmMap[$itm->getTitle()] = 1;
//            }
//
//            foreach ($data as &$itm) {
//                $itm = json_decode(json_encode($itm));
//                $itm->_selected = isset($assetOrmMap[$itm->id]) ? 1 : 0;
//            }
//        }


        return new JsonResponse(array(
            'files' => $data,
            'pageNum' => $pageNum,
            'total' => $total,
        ));
    }

    /**
     * @route("/file/nav")
     * @return Response
     */
    public function fileNav(Request $request)
    {
        $currentFolderId = $request->get('currentFolderId') ?: 0;

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $orm = $fullClass::getById($this->_connection, $currentFolderId);
        if (!$orm) {
            $path[] = [
                'id' => 0,
                'title' => 'Home',
            ];
        } else {
            $path = $orm->getFolderPath();
        }

        $this->_session->set('currentFolderId', $currentFolderId);
        return new JsonResponse([
            'currentFolder' => end($path),
            'path' => $path,
        ]);
    }

    /**
     * @route("/file/folders/update")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileFoldersUpdate(Request $request)
    {
        $fullClass = UtilsService::getFullClassFromName('Asset');
        $data = json_decode($request->get('data') ?: '[]');
        foreach ($data as $itm) {
            $orm = $fullClass::getById($this->_connection, $itm->id);
            $orm->parentId = $itm->parentId;
            $orm->_rank = $itm->_rank;
            $orm->save();
        }
        return new JsonResponse($data);
    }

    /**
     * @route("/file/move")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileFileMove(Request $request)
    {
        $id = $request->get('id');
        $parentId = $request->get('parentId');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $orm = $fullClass::getById($this->_connection, $id);
        $orm->parentId = $parentId;
        $orm->save();
        return new JsonResponse($orm);
    }

    /**
     * @route("/file/upload")
     * @return Response
     */
    public function fileUpload(Request $request)
    {
        $file = $files = $request->files->get('file');
        $parentId = $request->request->get('parentId') ?: 0;

        if ($file) {
            return $this->_fileManagerService->processUploadedFile($file, $parentId);
        }

        return new JsonResponse(array(
            'status' => 0,
            'orm' => array(
                'title' => 'Error Occurred',
                'code' => 'Oops'
            ),
        ));
    }

    /**
     * @route("/file/edit/folder")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileEditFolder(Request $request)
    {
        $id = $request->get('id');
        $title = $request->get('title');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $orm = $fullClass::getById($this->_connection, $id);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $orm->title = $title;
        $orm->save();
        return new JsonResponse($orm);
    }

    /**
     * @route("/file/add/folder")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileAddFolder(Request $request)
    {
        $title = $request->get('title');
        $parentId = $request->get('parentId');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $rank = $fullClass::data($this->_connection, array(
            'select' => 'MAX(m._rank) AS max',
            'orm' => 0,
            'whereSql' => 'm.parentId = ?',
            'params' => array($parentId),
            'oneOrNull' => 1,
        ));
        $max = ($rank['max'] ?: 0) + 1;

        $orm = new $fullClass($this->_connection);
        $orm->title = $title;
        $orm->parentId = $parentId;
        $orm->isFolder = 1;
        $orm->_rank = $max;
        $orm->save();

        return new JsonResponse($orm);
    }

    /**
     * @route("/file/crop")
     * @return Response
     */
    public function fileCrop(Request $request)
    {
        $x = $request->get('x');
        $y = $request->get('y');
        $width = $request->get('width');
        $height = $request->get('height');
        $assetId = $request->get('assetId');
        $assetSizeId = $request->get('assetSizeId');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $asset = $fullClass::getById($this->_connection, $assetId);
        if (!$asset) {
            $asset = $fullClass::getByField($this->_connection, 'code', $assetId);
        }
        if (!$asset) {
            throw new NotFoundHttpException();
        }
        $asset->save();

        $fullClass = UtilsService::getFullClassFromName('ImageSize');
        $imageSize = $fullClass::getById($this->_connection, $assetSizeId);
        if (!$imageSize) {
//            throw new NotFoundHttpException();
        }

        $fullClass = UtilsService::getFullClassFromName('ImageCrop');
        $imageCrop = $fullClass::data($this->_connection, [
            'whereSql' => 'm.assetId = ? AND m.assetSizeId = ?',
            'params' => array($asset->id, $assetSizeId),
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if (!$imageCrop) {
            $imageCrop = new $fullClass($this->_connection);
            $imageCrop->title = ($asset ? $asset->code : '') . ' - ' . ($imageSize ? $imageSize->title : 'all');
        }

        if ($imageSize) {
            FileManagerService::removeCache($asset, $imageSize);
        } else {
            $fullClass = UtilsService::getFullClassFromName('ImageSize');
            $allAssetSizes = $fullClass::data($this->_connection);
            foreach ($allAssetSizes as $itm) {
                FileManagerService::removeCache($asset, $itm);
            }

            //bite me
            $allSizeCode = new $fullClass($this->_connection);
            $allSizeCode->code = 1;
            FileManagerService::removeCache($asset, $allSizeCode);

            $fullClass = UtilsService::getFullClassFromName('ImageCrop');
            $result = $fullClass::data($this->_connection, [
                'whereSql' => 'm.assetId = ? AND assetSizeId != ?',
                'params' => [$asset->id, 'All sizes'],
            ]);
            foreach ($result as $itm) {
                $itm->delete();
            }
        }

        $imageCrop->x = $x;
        $imageCrop->y = $y;
        $imageCrop->width = $width;
        $imageCrop->height = $height;
        $imageCrop->assetId = $asset->id;
        $imageCrop->assetSizeId = $assetSizeId;
        $imageCrop->save();
        return new JsonResponse($imageCrop);

    }

    /**
     * @route("/file/current/folder")
     * @return Response
     */
    public function fileCurrentFolder(Request $request)
    {
        $currentFolderId = 0;

        $currentAssetId = $request->get('currentAssetId') ?: 0;
        $fullClass = UtilsService::getFullClassFromName('Asset');
        $asset = $fullClass::getById($this->_connection, $currentAssetId);
        if (!$asset) {
            $asset = $fullClass::getByField($this->_connection, 'code', $currentAssetId);
        }
        if ($asset) {
            $currentFolderId = $asset->parentId;
            $this->_session->set('currentFolderId', $currentFolderId);
        } else {
            $currentFolderId = $this->_session->get('currentFolderId');
        }

        return new JsonResponse(array(
            'currentFolderId' => $currentFolderId,
        ));
    }

    /**
     * @route("/file/files/chosen")
     * @return Response
     */
    public function filesFilesChosen(Request $request)
    {
        $files = json_decode($request->get('files') ?: '[]');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $data = array_filter(array_map(function ($itm) use ($fullClass) {
            return $fullClass::getById($this->_connection, $itm);
        }, $files));

        return new JsonResponse($data);
    }

    /**
     * @route("/file/size")
     * @return Response
     */
    public function fileImageSize(Request $request)
    {
        $assetId = $request->get('code');
        $assetSize = $request->get('size');

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $asset = $fullClass::getById($this->_connection, $assetId);
        if (!$asset) {
            $asset = $fullClass::getByField($this->_connection, 'code', $assetId);
        }

        if (!$asset) {
            return new JsonResponse([
                'id' => null,
                'width' => null,
                'height' => null,
                'size' => null,
            ]);
        }

        return new JsonResponse([
            'id' => $asset->id,
            'width' => $asset->width,
            'height' => $asset->height,
            'size' => $assetSize,
        ]);
    }
}
