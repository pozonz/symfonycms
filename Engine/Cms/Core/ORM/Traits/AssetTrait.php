<?php

namespace ExWife\Engine\Cms\Core\ORM\Traits;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;

trait AssetTrait
{
    /**
     * @return string|string[]|null
     */
    public function getManageSearchUrl()
    {
        if ($this->isFolder == 1) {
            return "/manage/section/files?currentFolderId={$this->id}";
        }

        return "/manage/section/files/orms/Asset/{$this->id}";
    }

    /**
     * @return null
     */
    public function getHideFromSearch()
    {
        if ($this->isFolder == 1) {
            return 1;
        }

        return $this->hideFromSearch ?? null;
    }

    /**
     * @return array
     */
    public function getFolderPath()
    {
        $path = [];
        $parent = $this;
        do {
            $path[] = $parent;
        } while ($parent = static::getById($this->_connection, $parent->parentId));
        $path[] = [
            'id' => 0,
            'title' => 'Home',
        ];
        $path = array_reverse($path);
        return $path;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return static::data($this->_connection, [
            'whereSql' => 'm.parentId = ?',
            'params' => array($this->id)
        ]);
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function save($options = [])
    {
        if (!$this->code) {
            do {
                $code = UtilsService::generateHex(4);
                $orm = static::getByField($this->_connection, 'code', $code);
            } while ($orm);
            $this->code = $code;
        }

//        if ($this->getId()) {
//            if (getenv('FASTLY_API_KEY') && getenv('FASTLY_SERVICE_ID')) {
//                $clientParams = [
//                    'base_uri' => 'https://api.fastly.com',
//                    'headers' => [
//                        'Fastly-Key' => getenv('FASTLY_API_KEY'),
//                        'Accept' => 'application/json',
//                    ]
//                ];
//                $url = "/service/" . getenv('FASTLY_SERVICE_ID') . "/purge/asset" . $this->getId();
//
//                $client = new Client($clientParams);
//                $response = $client->request('POST', $url);
//                $content = $response->getBody()->getContents();
//
//                $fastlyRequest = new FastlyRequest($this->getPdo());
//                $fastlyRequest->setTitle($this->getId() . ' / ' . $this->getCode());
//                $fastlyRequest->setUrl($url);
//                $fastlyRequest->setClientParams(json_encode($clientParams, JSON_PRETTY_PRINT));
//                $fastlyRequest->setResponse($content);
//                $fastlyRequest->save();
//            }
//        }

        return parent::save($options);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function delete($options = [])
    {
        FileManagerService::removeCaches($this->_connection, $this);

        if ($this->isFolder == 1) {
            $children = $this->getChildren();
            foreach ($children as $itm) {
                $itm->delete();
            }
        } else {
            FileManagerService::removeFile($this);

            $saveAssetsToDb = getenv('SAVE_ASSETS_TO_DB');
            if ($saveAssetsToDb) {
                $assetBinaryFullClass = UtilsService::getFullClassFromName('AssetBinary', $this->_connection);
                $assetBinary = $assetBinaryFullClass::getByField($this->_connection, 'title', $this->id);
                if ($assetBinary) {
                    $assetBinary->delete();
                }
            }
        }

        return parent::delete($options);
    }
}