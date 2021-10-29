<?php

namespace ExWife\Engine\Cms\Page\Service;

use BlueM\Tree;
use ExWife\Engine\Cms\_Core\ORM\Page;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

class PageService
{

    /**
     * @param $connection
     * @param $pageCategroyId
     * @return Tree
     */
    static public function getPageTreeByCategoryId($connection, $pageCategroyId)
    {
        $nodes = [];

        $fullClass = UtilsService::getFullClassFromName('Page');
        /** @var Page[] $pages */
        $pages = $fullClass::data($connection);
        foreach ($pages as $page) {
            $category = (array)$page->objCategory();
            if (!in_array($pageCategroyId, $category) && !($pageCategroyId == 0 && count($category) == 0)) {
                continue;
            }
            $categoryParent = (array)$page->objCategoryParent();
            $categoryRank = (array)$page->objCategoryRank();
            $categoryClosed = (array)$page->objCategoryClosed();

            $categoryParentValue = isset($categoryParent["cat$pageCategroyId"]) ? $categoryParent["cat$pageCategroyId"] : 0;
            $categoryRankValue = isset($categoryRank["cat$pageCategroyId"]) ? $categoryRank["cat$pageCategroyId"] : 0;
            $categoryClosedValue = isset($categoryClosed["cat$pageCategroyId"]) ? $categoryClosed["cat$pageCategroyId"] : 0;

            $page->parentId = $categoryParentValue;
            $page->rank = $categoryRankValue;
            $page->closed = $categoryClosedValue;

            $nodes[] = [
                'id' => $page->id,
                'title' => $page->title,
                'parent' => $categoryParentValue,
                'rank' => (int)$categoryRankValue,
                'status' => $page->_status,
                'closed' => $categoryClosedValue,
                'data' => $page,
            ];
        }

        usort($nodes, function ($a, $b) {
            return $a['rank'] >= $b['rank'];
        });

        $tree = new Tree($nodes, [
            'buildwarningcallback' => function () {
            },
        ]);

        return $tree;
    }

    /**
     * @param $pageTemplate
     */
    static public function createTemplateFile($pageTemplate)
    {
        $file = static::getTemplatePath() . $pageTemplate->fileName;
        if (!file_exists($file)) {
            $str = file_get_contents(static::getResourceFilesPath() . 'template.txt');
            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file, $str);
        }
    }

    /**
     * @return string
     */
    static public function getResourceFilesPath()
    {
        return __DIR__ . '/../../../../Resources/files/';
    }

    /**
     * @return string
     */
    static public function getTemplatePath()
    {
        return __DIR__ . '/../../../../../../../templates/';
    }
}
