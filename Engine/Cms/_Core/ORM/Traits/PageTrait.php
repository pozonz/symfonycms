<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;

trait PageTrait
{
    /**
     * PageTrait constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->type = 1;
    }

    /**
     * @return mixed
     */
    public function getSiteSearchDescription()
    {
        return $this->_ContentBlocksContent($this->content ?: '[]');
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function save($options = [])
    {
        if (!is_numeric($this->templateFile) && $this->templateFile) {
            $json = json_decode($this->templateFile);
            if ($json) {
                $templateName = $json->name;
                $templateFile = preg_replace("/[^a-z0-9\_\-\.]/i", '', $json->file);
                $templateFile = basename($templateFile, '.twig') . '.twig';

                $fullClass = UtilsService::getFullClassFromName('PageTemplate');
                $orm = new $fullClass($this->_connection);
                $orm->title = $templateName;
                $orm->fileName = $templateFile;
                $orm->save();

                $this->templateFile = $orm->id;
            }
        }
        return parent::save($options);
    }

    /**
     * @return mixed
     */
    public function objCategory()
    {
        return json_decode($this->category) ?: [];
    }

    /**
     * @return mixed
     */
    public function objCategoryClosed()
    {
        return json_decode($this->categoryClosed) ?: [];
    }

    /**
     * @return mixed
     */
    public function objCategoryParent()
    {
        return json_decode($this->categoryParent) ?: [];
    }

    /**
     * @return mixed
     */
    public function objCategoryRank()
    {
        return json_decode($this->categoryRank) ?: [];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function objPageTemplate()
    {
        $fullClass = UtilsService::getFullClassFromName('PageTemplate');
        return $fullClass::getById($this->_connection, $this->templateFile);
    }
}