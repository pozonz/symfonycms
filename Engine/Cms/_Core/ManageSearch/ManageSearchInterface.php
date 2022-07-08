<?php

namespace SymfonyCMS\Engine\Cms\_Core\ManageSearch;

use Doctrine\DBAL\Connection;

interface ManageSearchInterface
{
    public function getManageSearchRank();
    public function updateManageSearchList();
    public function deleteManageSearchList($ormId);

    public function getManageSearchTitle();
    public function getManageSearchCategory();
    public function getManageSearchImage();
    public function getManageSearchDescription();
    public function getManageSearchUrl();
    public function getManageSearchOrmId();
    public function getManageSearchKeywords();
}
