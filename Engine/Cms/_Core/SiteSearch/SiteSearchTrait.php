<?php

namespace ExWife\Engine\Cms\_Core\SiteSearch;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait SiteSearchTrait
{
    /**
     * @return float|int
     */
    public function getSiteSearchRank()
    {
        $prefix = 0;
        $fullClass = UtilsService::getFullClassFromName('SiteSearchRank');
        $siteSearchRank = $fullClass::getByField($this->_connection, 'title', $this->getSiteSearchCategory());
        if ($siteSearchRank) {
            $prefix = $siteSearchRank->_rank * 100000;
        } else {
            $prefix = 99 * 100000;
        }
        return $this->_rank + $prefix;
    }

    /**
     *
     */
    public function updateSiteSearchList()
    {
        $ormId = $this->getSiteSearchOrmId();

        $fullClass = UtilsService::getFullClassFromName('SiteSearch');
        $siteSearch = $fullClass::data($this->_connection, [
            'whereSql' => 'm.ormId = ? AND m.category = ?',
            'params' => [$this->getSiteSearchOrmId(), $this->getSiteSearchCategory()],
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if (!$siteSearch) {
            $siteSearch = new $fullClass($this->_connection);
        }

        if (method_exists($this, 'getHideFromSearch') && $this->getHideFromSearch() == 1) {
            $siteSearch->delete();
            return;
        }

        if ($this->_status) {
            $siteSearch->title = $this->getSiteSearchTitle() ?: '';
            $siteSearch->category = $this->getSiteSearchCategory();
            $siteSearch->image = $this->getSiteSearchImage();
            $siteSearch->description = $this->getSiteSearchDescription() ?: '';
            $siteSearch->url = $this->getSiteSearchUrl();
            $siteSearch->ormId = $ormId;
            $siteSearch->_status = 1;

            $siteSearch->_rank = $this->getSiteSearchRank();
            $siteSearch->searchKeywords = $this->getSiteSearchKeywords();

            $siteSearch->save();
        } else {
            $siteSearch->delete();
        }
    }

    /**
     * @param $ormId
     */
    public function deleteSiteSearchList($ormId)
    {
        $fullClass = UtilsService::getFullClassFromName('SiteSearch');
        $siteSearch = $fullClass::data($this->_connection, [
            'whereSql' => 'm.ormId = ? AND m.category = ?',
            'params' => [$this->getSiteSearchOrmId(), $this->getSiteSearchCategory()],
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if ($siteSearch) {
            $siteSearch->delete();
        }
    }

    /**
     * @return null
     */
    public function getHideFromSearch()
    {
        return $this->hideFromSearch ?? null;
    }

    /**
     * @return mixed
     */
    public function getSiteSearchTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSiteSearchCategory()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return mixed
     */
    public function getSiteSearchOrmId()
    {
        return $this->id;
    }

    /**
     * @return null
     */
    public function getSiteSearchKeywords()
    {
        if (method_exists($this, 'getSearchKeywords')) {
            return $this->searchKeywords;
        }
        return null;
    }

    /**
     * @return string|string[]|null
     */
    public function getSiteSearchUrl()
    {
        return $this->getSiteMapUrl();

    }

    /**
     * @return null
     */
    public function getSiteSearchImage()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSiteSearchDescription()
    {
        return null;
    }
}
