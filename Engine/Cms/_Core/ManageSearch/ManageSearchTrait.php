<?php

namespace ExWife\Engine\Cms\_Core\ManageSearch;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait ManageSearchTrait
{
    /**
     * @return float|int
     */
    public function getManageSearchRank()
    {
        $prefix = 0;
        $fullClass = UtilsService::getFullClassFromName('ManageSearchRank');
        $ManageSearchRank = $fullClass::getByField($this->_connection, 'title', $this->getManageSearchCategory());
        if ($ManageSearchRank) {
            $prefix = $ManageSearchRank->_rank * 100000;
        } else {
            $prefix = 99 * 100000;
        }
        return $this->_rank + $prefix;
    }

    /**
     *
     */
    public function updateManageSearchList()
    {
        $ormId = $this->getManageSearchOrmId();

        $fullClass = UtilsService::getFullClassFromName('ManageSearch');
        $ManageSearch = $fullClass::data($this->_connection, [
            'whereSql' => 'm.ormId = ? AND m.category = ?',
            'params' => [$this->getManageSearchOrmId(), $this->getManageSearchCategory()],
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if (!$ManageSearch) {
            $ManageSearch = new $fullClass($this->_connection);
        }

        if ($this->_status) {
            $ManageSearch->title = $this->getManageSearchTitle() ?: '';
            $ManageSearch->category = $this->getManageSearchCategory();
            $ManageSearch->image = $this->getManageSearchImage();
            $ManageSearch->description = $this->getManageSearchDescription() ?: '';
            $ManageSearch->url = $this->getManageSearchUrl();
            $ManageSearch->ormId = $ormId;
            $ManageSearch->_status = 1;

            $ManageSearch->_rank = $this->getManageSearchRank();
            $ManageSearch->searchKeywords = $this->getManageSearchKeywords();

            $model = static::getModel($this->_connection);
            $ManageSearch->modelnitials = $this->generate($model->title);
            $ManageSearch->modelTitle = $model->title;
            $ManageSearch->save();
        } else {
            $ManageSearch->delete();
        }
    }

    /**
     * @param $ormId
     */
    public function deleteManageSearchList($ormId)
    {
        $fullClass = UtilsService::getFullClassFromName('ManageSearch');
        $ManageSearch = $fullClass::data($this->_connection, [
            'whereSql' => 'm.ormId = ? AND m.category = ?',
            'params' => [$this->getManageSearchOrmId(), $this->getManageSearchCategory()],
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if ($ManageSearch) {
            $ManageSearch->delete();
        }
    }

    /**
     * @return mixed
     */
    public function getManageSearchTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getManageSearchCategory()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return mixed
     */
    public function getManageSearchOrmId()
    {
        return $this->id;
    }

    /**
     * @return null
     */
    public function getManageSearchKeywords()
    {
        if (method_exists($this, 'getSearchKeywords')) {
            return $this->searchKeywords;
        }
        return null;
    }

    /**
     * @return string|string[]|null
     */
    public function getManageSearchUrl()
    {
        return null;

    }

    /**
     * @return null
     */
    public function getManageSearchImage()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getManageSearchDescription()
    {
        return null;
    }

    /**
     * Generate initials from a name
     *
     * @param string $name
     * @return string
     */
    protected function generate(string $name) : string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return $this->makeInitialsFromSingleWord($name);
    }

    /**
     * Make initials from a word with no spaces
     *
     * @param string $name
     * @return string
     */
    protected function makeInitialsFromSingleWord(string $name) : string
    {
        preg_match_all('#([A-Z]+)#', $name, $capitals);
        if (count($capitals[1]) >= 2) {
            return substr(implode('', $capitals[1]), 0, 2);
        }
        return strtoupper(substr($name, 0, 2));
    }
}
