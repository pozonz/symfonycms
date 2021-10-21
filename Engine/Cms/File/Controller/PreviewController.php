<?php

namespace ExWife\Engine\Cms\File\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class PreviewController extends AbstractController
{
    /** @var Connection $_connection */
    protected $_connection;

    /** @var FileManagerService $_fileManagerService */
    protected $_fileManagerService;

    /**
     * FilePreviewController constructor.
     * @param Connection $connection
     * @param FileManagerService $fileManagerService
     */
    public function __construct(Connection $connection, FileManagerService $fileManagerService)
    {
        $this->_connection = $connection;
        $this->_fileManagerService = $fileManagerService;
    }

    /**
     * @Route("/downloads/assets/{assetCode}", methods={"GET"})
     * @Route("/downloads/assets/{assetCode}/{fileName}", requirements={"fileName"=".*"}, methods={"GET"})
     * @param Request $request
     * @param $assetCode
     * @param null $fileName
     * @return mixed
     * @throws \Exception
     */
    public function assetDownload(Request $request, $assetCode, $fileName = null)
    {
        $response = $this->assetImage($request, $assetCode, null, $fileName);
        $asset = $this->getAsset($assetCode);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName && strpos($fileName, '.') !== false ? $fileName : $asset->fileName);
        return $response;
    }

    /**
     * @Route("/images/assets/{assetCode}", methods={"GET"})
     * @Route("/images/assets/{assetCode}/{assetSizeCode}", methods={"GET"})
     * @Route("/images/assets/{assetCode}/{assetSizeCode}/{fileName}", methods={"GET"})
     * @param Request $request
     * @param $assetCode
     * @param null $assetSizeCode
     * @param null $fileName
     * @return mixed
     * @throws \Exception
     */
    public function assetImage(Request $request, $assetCode, $assetSizeCode = null, $fileName = null)
    {
        ini_set('memory_limit', '512M');

        $useWebp = in_array('image/webp', $request->getAcceptableContentTypes());
        $returnOriginalFile = $assetSizeCode && $assetSizeCode != 1 ? 0 : 1;

        $asset = $this->getAsset($assetCode);

        $isImage = $asset->isImage;
        if ($assetSizeCode && $isImage) {
            $returnOriginalFile = 0;
        }

        $modifiedTime = '@' . strtotime($asset->_modified);
        $date = new \DateTimeImmutable($modifiedTime);
        $savedDate = $date->setTimezone(new \DateTimeZone("GMT"))->format("D, d M y H:i:s T");

        $fileType = $asset->fileType;
        $fileName = $asset->fileName;
        $fileSize = $asset->fileSize;
        $ext = $asset->fileExtension;
        if ($useWebp && strtolower($ext) == 'gif') {
//            $ext = "jpg";
        }

        $cachedKey = FileManagerService::getCacheKey($asset, $assetSizeCode ?: 1);
        $cachedFolder = FileManagerService::checkAndCreatePath(FileManagerService::IMAGE_CACHE_PATH);
        $uploadPath = FileManagerService::checkAndCreatePath(FileManagerService::UPLOAD_PATH);

        $fileLocation = $uploadPath . $asset->fileLocation;
        $thumbnail = "{$cachedFolder}{$cachedKey}.$ext";
        $thumbnailHeader = "{$cachedFolder}{$cachedKey}.$ext.txt";
        $webpThumbnail = "{$thumbnail}.webp";
        $webpThumbnailHeader = "{$thumbnail}.webp.txt";

        if (!$returnOriginalFile && !$useWebp && file_exists($thumbnail) && file_exists($thumbnailHeader)) {
            $header = json_decode(file_get_contents($thumbnailHeader));
            if ($header) {
                $header = (array)$header;
                $header['Surrogate-Key'] = 'asset' . $asset->id;
                return $this->getBinaryFileResponse($thumbnail, $header);
            }
        }

        if (!$returnOriginalFile && $useWebp && file_exists($webpThumbnail) && file_exists($webpThumbnailHeader)) {
            $header = json_decode(file_get_contents($webpThumbnailHeader));
            if ($header) {
                $header = (array)$header;
                $header['Surrogate-Key'] = 'asset' . $asset->id;
                return $this->getBinaryFileResponse($webpThumbnail, $header);
            }
        }

        $fileType = strpos($fileType, 'image/svg') !== false ? 'image/svg+xml' : $fileType;

        if ($fileType == 'image/svg+xml') {
            $isImage = 1;
            $assetSizeCode = null;
        }

        if ($fileType == 'application/pdf' && !$returnOriginalFile) {
            $pdfRasterToken = getenv('PDF_RASTER_TOKEN');
            $pdfRasterEndPoint = getenv('PDF_RASTER_ENDPOINT');

            if ($pdfRasterToken && $pdfRasterEndPoint) {
                $url = $request->getSchemeAndHttpHost() . "/downloads/assets/{$assetCode}";
                $payload = [
                    'url' => $url,
                    'token' => $pdfRasterToken
                ];
                $opts = [
                    'http' =>
                        [
                            'method' => 'POST',
                            'header' => 'Content-type: application/json',
                            'content' => json_encode($payload)
                        ]
                ];
                $data = file_get_contents($pdfRasterEndPoint, false, stream_context_create($opts));

                $ff = new \finfo();
                $ffMime = $ff->buffer($data, \FILEINFO_MIME_TYPE);
                $ffExt = $ff->buffer($data, \FILEINFO_EXTENSION);

                $fileLocation = "{$cachedFolder}{$asset->fileLocation}.{$ffExt}";
                $thumbnail = "{$thumbnail}.{$ffExt}";
                file_put_contents($fileLocation, $data);

                $isImage = 1;
                $fileType = $ffMime;
            }
        }

        if (!$isImage && !$returnOriginalFile) {
            return $this->getBinaryFileResponse(FileManagerService::TEMPLATE_FILE_PATH . "no-preview-big1.jpg", [
                "cache-control" => 'max-age=31536000',
                "content-length" => 11042,
                "content-type" => 'image/jpeg',
                "last-modified" => $savedDate,
                "etag" => '"' . sprintf("%x-%x", $date->getTimestamp(), $fileSize) . '"',
                "Surrogate-Key" => 'asset' . $asset->id,
            ]);
        }

        if ($assetSizeCode) {
            $qualityCmd = "-quality 90%";
            $colorCmd = '-colorspace sRGB';
            $resizeCmd = '';
            $cropCmd = '';

            $assetSize = null;
            if ($assetSizeCode != 1) {
                $fullClass = UtilsService::getFullClassFromName('ImageSize');
                $assetSize = $fullClass::getByField($this->_connection, 'code', $assetSizeCode);
                if (!$assetSize) {
                    throw new NotFoundHttpException();
                }
            }
            if ($assetSize) {
                if ($assetSize->resizeBy == 1) {
                    $resizeCmd = "-resize \"x{$assetSize->width}>\"";
                } else {
                    $resizeCmd = "-resize \"{$assetSize->width}>\"";
                }
            }

            $fullClass = UtilsService::getFullClassFromName('ImageCrop');
            $assetCrop = $fullClass::data($this->_connection, [
                'whereSql' => 'm.assetId = ? AND m.assetSizeId = ?',
                'params' => [$asset->id, $assetSize ? $assetSize->id : null],
                'limit' => 1,
                'oneOrNull' => 1,
            ]);
            if (!$assetCrop) {
                $assetCrop = $fullClass::data($this->_connection, [
                    'whereSql' => 'm.assetId = ? AND m.assetSizeId = ?',
                    'params' => [$asset->id, 'All sizes'],
                    'limit' => 1,
                    'oneOrNull' => 1,
                ]);
            }
            if ($assetCrop and strpos($assetSizeCode, 'cms_') !== 0) {
                $cropCmd = "-crop {$assetCrop->width}x{$assetCrop->height}+{$assetCrop->x}+{$assetCrop->y}";
            }

            $command = getenv('CONVERT_CMD') . " $fileLocation {$qualityCmd} {$cropCmd} {$resizeCmd} {$colorCmd} -strip $thumbnail";
        }

        $saveAssetsToDb = getenv('SAVE_ASSETS_TO_DB');
        if ($saveAssetsToDb && !file_exists($fileLocation)) {
            $assetBinaryFullClass = UtilsService::getFullClassFromName('AssetBinary');
            $assetBinary = $assetBinaryFullClass::getByField($this->_connection, 'title', $asset->id);
            if (!$assetBinary) {
                throw new NotFoundHttpException();
            }
            file_put_contents($fileLocation, $assetBinary->blob);
        }

        if ($assetSizeCode && !$returnOriginalFile) {
            $returnValue = $this->_fileManagerService->generateOutput($command);
            $fileSize == filesize($thumbnail);
        } else {
            copy($fileLocation, $thumbnail);
        }

        $thumbnailHeaderContent = [
            "cache-control" => 'max-age=31536000',
            "content-length" => $fileSize,
            "content-type" => $fileType,
            "last-modified" => $savedDate,
            "etag" => '"' . sprintf("%x-%x", $date->getTimestamp(), $fileSize) . '"',
            "Surrogate-Key" => 'asset' . $asset->id,
        ];
        file_put_contents($thumbnailHeader, json_encode($thumbnailHeaderContent));

        if ($saveAssetsToDb && file_exists($fileLocation)) {
            unlink($fileLocation);
        }

        if ($useWebp && $assetSizeCode && !$returnOriginalFile && $fileType != 'image/gif') {
            $command = getenv('CWEBP_CMD') . " $thumbnail -o $webpThumbnail";
            $returnValue =  $this->_fileManagerService->generateOutput($command);

            $fileSize == filesize($webpThumbnail);
            $fileType = 'image/webp';

            $webpThumbnailHeaderContent = [
                "cache-control" => 'max-age=31536000',
                "content-length" => $fileSize,
                "content-type" => $fileType,
                "last-modified" => $savedDate,
                "etag" => '"' . sprintf("%x-%x", $date->getTimestamp(), $fileSize) . '"',
                "Surrogate-Key" => 'asset' . $asset->id,
            ];
            file_put_contents($webpThumbnailHeader, json_encode($webpThumbnailHeaderContent));

            $thumbnail = $webpThumbnail;
            $thumbnailHeaderContent = $webpThumbnailHeaderContent;
        }

        return $this->getBinaryFileResponse($thumbnail, $thumbnailHeaderContent);
    }

    /**
     * @param $assetCode
     * @return mixed
     */
    protected function getAsset($assetCode)
    {
        $fullClass = UtilsService::getFullClassFromName('Asset');
        $asset = $fullClass::getByField($this->_connection, 'code', $assetCode);
        if (!$asset) {
            $asset = $fullClass::getById($this->_connection, $assetCode);
        }
        if (!$asset) {
            throw new NotFoundHttpException();
        }
        return $asset;
    }

    /**
     * @param $file
     * @param $header
     * @return BinaryFileResponse
     */
    protected function getBinaryFileResponse($file, $header)
    {
        $header = (array)$header;
        return BinaryFileResponse::create($file, Response::HTTP_OK, $header, true, null, false, true);
    }
}
