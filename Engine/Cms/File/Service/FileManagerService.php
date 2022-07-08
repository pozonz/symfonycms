<?php

namespace SymfonyCMS\Engine\Cms\File\Service;

use BlueM\Tree;
use BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer;
use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use MillenniumFalcon\Core\ORM\_Model;
use MillenniumFalcon\Core\Service\AssetService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class FileManagerService
{
    const UPLOAD_PATH = __DIR__ . '/../../../../../../../uploads/';

    const IMAGE_CACHE_PATH = __DIR__ . '/../../../../../../../cache/image/';

    const TEMPLATE_FILE_PATH = __DIR__ . '/../../../../Resources/files/';

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var KernelInterface
     */
    protected $_kernel;

    /**
     * FileManagerService constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection, KernelInterface $kernel)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
    }

    /**
     * @param $currentFolderId
     * @return \stdClass
     */
    public function getFolderRoot($currentFolderId)
    {
        $fullClass = UtilsService::getFullClassFromName('Asset');
        $data = $fullClass::data($this->_connection, array(
            "select" => 'm.id AS id, m.parentId AS parent, m.title AS text',
            'whereSql' => 'm.isFolder = 1',
            "sort" => 'm._rank',
            "order" => 'ASC',
            "orm" => 0,
        ));
        foreach ($data as &$itm) {
            if ($itm['id'] == $currentFolderId || !$currentFolderId) {
                $itm['state'] = [
                    'opened' => true,
                    'selected' => $currentFolderId == $itm['id'] ? true : false,
                ];
            }
        }

        $tree = new Tree($data, [
            'rootId' => 0,
            'jsonserializer' => new HierarchicalTreeJsonSerializer(),
            'buildwarningcallback' => function () {
            },
        ]);

        $root = new \stdClass();
        $root->id = '0';
        $root->title = 'Home';
        $root->text = 'Home';
        $root->closed = 0;
        $root->status = 1;
        $root->state = [
            'opened' => true,
            'selected' => $currentFolderId ? false : true,
        ];
        $root->children = $tree->jsonSerialize();
        return $root;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $asset
     * @return mixed
     */
    public function processUploadedFileWithAsset(UploadedFile $uploadedFile, $asset)
    {
        $sourceFile = $uploadedFile->getPathname();
        return $this->processFileWithAsset($sourceFile, $asset, [
            'originalName' => $uploadedFile->getClientOriginalName(),
            'fileType' => $uploadedFile->getMimeType(),
            'fileSize' => $uploadedFile->getSize(),
            'fileExtension' => $uploadedFile->getClientOriginalExtension(),
        ]);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $foldId
     * @return mixed
     */
    public function processUploadedFile(UploadedFile $uploadedFile, $foldId)
    {
        $sourceFile = $uploadedFile->getPathname();
        return $this->processFile($sourceFile, $foldId, [
            'originalName' => $uploadedFile->getClientOriginalName(),
            'fileType' => $uploadedFile->getMimeType(),
            'fileSize' => $uploadedFile->getSize(),
            'fileExtension' => $uploadedFile->getClientOriginalExtension(),
        ]);
    }

    /**
     * @param $sourceFile
     * @param $foldId
     * @param array $options
     * @return mixed
     */
    public function processFile($sourceFile, $foldId, $options = [])
    {
        $originalName = $options['originalName'] ?? null;

        $fullClass = UtilsService::getFullClassFromName('Asset');
        $rank = $fullClass::data($this->_connection, [
            'select' => 'MIN(m._rank) AS min',
            'orm' => 0,
            'whereSql' => 'm.parentId = ?',
            'params' => [$foldId],
            'oneOrNull' => 1,
        ]);
        $min = $rank['min'] - 1;

        $asset = new $fullClass($this->_connection);
        $asset->title = $originalName;
        $asset->isFolder = 0;
        $asset->parentId = $foldId;
        $asset->_rank = $min;
        $asset->save();

        return $this->processFileWithAsset($sourceFile, $asset, $options);
    }

    /**
     * @param $sourceFile
     * @param $asset
     * @param array $options
     * @return mixed
     */
    public function processFileWithAsset($sourceFile, $asset, $options = [])
    {
        $originalName = $options['originalName'] ?? null;
        $fileType = $options['fileType'] ?? null;
        $fileSize = $options['fileSize'] ?? null;
        $fileExtension = $options['fileExtension'] ?? null;

        static::removeFile($asset);
        static::removeCaches($this->_connection, $asset);

        //Check if the file is image
        $asset->isImage = 0;
        $asset->width = null;
        $asset->height = null;

        $info = false;
        try {
            $info = getimagesize($sourceFile);
        } catch (\Exception $ex) {
        }
        if ($info !== false) {
            list($x, $y) = $info;
            $asset->isImage = 1;
            $asset->width = $x;
            $asset->height = $y;
        }

        //Create upload folder if does not exist
        $uploadedDir = static::UPLOAD_PATH;
        static::checkAndCreatePath($uploadedDir);

        $targetFile = $uploadedDir . $asset->id . '.' . $fileExtension;
        if ($asset->isImage == 1) {
            $command = $_ENV['CONVERT_CMD'] . ' "' . $sourceFile . '" -auto-orient ' . $targetFile;
            $this->generateOutput($command);
        } else {
            copy($sourceFile, $targetFile);
        }

        $asset->fileName = $originalName;
        $asset->fileType = $fileType;
        $asset->fileSize = $fileSize;
        $asset->fileExtension = $fileExtension;
        $asset->fileLocation = $asset->id . '.' . $asset->fileExtension;
        $asset->save();

        $saveAssetsToDb = $_ENV['SAVE_ASSETS_TO_DB'];
        if ($saveAssetsToDb) {
            $fileLocation = $uploadedDir . $asset->fileLocation;
            if (file_exists($fileLocation)) {
                $content = file_get_contents($fileLocation);

                $assetBinaryFullClass = UtilsService::getFullClassFromName('AssetBinary');
                $assetBinary = $assetBinaryFullClass::getByField($this->_connection, 'title', $asset->id);
                if (!$assetBinary) {
                    $assetBinary = new $assetBinaryFullClass($this->_connection);
                    $assetBinary->title = $asset->id;
                }
                $assetBinary->blob = $content;
                $assetBinary->save();

                static::removeFile($asset);
                static::removeCaches($this->_connection, $asset);
            }
        }

        return $asset;
    }

    /**
     * @param $command
     * @param string $in
     * @param null $out
     * @return int
     */
    public function generateOutput($command, &$in = '', &$out = null)
    {
        $logFolder = static::IMAGE_CACHE_PATH;
        static::checkAndCreatePath($logFolder);
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", $logFolder . 'error-output.txt', 'a') // stderr is a file to write to
        );

        $returnValue = -999;

        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {

            fwrite($pipes[0], $in);
            fclose($pipes[0]);

            $out = "";
            //read the output
            while (!feof($pipes[1])) {
                $out .= fgets($pipes[1], 4096);
            }
            fclose($pipes[1]);
            $returnValue = proc_close($process);
        }

        return $returnValue;
    }

    /**
     * @param $path
     * @return mixed
     */
    static public function checkAndCreatePath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * @param $asset
     * @param $assetSizeCode
     * @return string
     */
    static public function getCacheKey($asset, $assetSizeCode)
    {
        return "{$asset->id}-{$assetSizeCode}";
    }

    /**
     * @param $asset
     */
    static public function removeFile($asset)
    {
        $link = static::UPLOAD_PATH . $asset->fileLocation;
        if (file_exists($link) && is_file($link)) {
            unlink($link);
        }
    }

    /**
     * @param $connection
     * @param $asset
     */
    static public function removeCaches($connection, $asset)
    {
        $fullClass = UtilsService::getFullClassFromName('ImageSize');
        $assetSizes = $fullClass::data($connection);
        foreach ($assetSizes as $assetSize) {
            static::removeCache($asset, $assetSize);
        }
    }

    /**
     * @param $asset
     * @param $assetSize
     */
    static public function removeCache($asset, $assetSize)
    {
        $ext = $asset->fileExtension;

        $cachedFolder = static::IMAGE_CACHE_PATH;
        $cachedKey = static::getCacheKey($asset, $assetSize->code);
        $cachedFile = "{$cachedFolder}{$cachedKey}.{$ext}";
        if (file_exists($cachedFile)) {
            unlink($cachedFile);
        }

        $cachedFile = "{$cachedFolder}{$cachedKey}.{$ext}.txt";

        if (file_exists($cachedFile)) {
            unlink($cachedFile);
        }

        if (strtolower($ext) == 'gif') {
            $ext = "jpg";
        }

        $cachedFile = "{$cachedFolder}{$cachedKey}.{$ext}.webp";
        if (file_exists($cachedFile)) {
            unlink($cachedFile);
        }

        $cachedFile = "{$cachedFolder}{$cachedKey}.{$ext}.webp.txt";
        if (file_exists($cachedFile)) {
            unlink($cachedFile);
        }
    }
}