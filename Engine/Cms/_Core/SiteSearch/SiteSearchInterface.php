<?php

namespace ExWife\Engine\Cms\_Core\SiteSearch;

use Doctrine\DBAL\Connection;

interface SiteSearchInterface
{
    public function getSiteSearchRank();
    public function updateSiteSearchList();
    public function deleteSiteSearchList($ormId);

    public function getSiteSearchTitle();
    public function getSiteSearchCategory();
    public function getSiteSearchImage();
    public function getSiteSearchDescription();
    public function getSiteSearchUrl();
    public function getSiteSearchOrmId();
    public function getSiteSearchKeywords();
}
