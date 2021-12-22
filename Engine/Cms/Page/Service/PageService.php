<?php

namespace ExWife\Engine\Cms\Page\Service;

use BlueM\Tree;
use ExWife\Engine\Cms\_Core\ORM\Page;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

class PageService
{
    const BLOCK_MAP = array(
        'Text' => 'fragment-0-text.html.txt',
        'Textarea' => 'fragment-1-textarea.html.txt',
        'Asset picker' => 'fragment-2-assetpicker.html.txt',
        'Asset files picker' => 'fragment-3-assetfolderpicker.html.txt',
        'Checkbox' => 'fragment-4-checkbox.html.txt',
        'Wysiwyg' => 'fragment-5-wysiwyg.html.txt',
        'Date picker' => 'fragment-6-date.html.txt',
        'Date & time picker' => 'fragment-7-datetime.html.txt',
        'Time picker' => 'fragment-8-time.html.txt',
        'Choice' => 'fragment-9-choice.html.txt',
        'Choice multi' => 'fragment-10-choicemultijson.html.txt',
        'Placeholder' => 'fragment-11-placeholder.html.txt',
        'Choice tree' => 'fragment-12-choice-tree.html.txt',
        'Choice tree multi' => 'fragment-13-choice-multi-json-tree.html.txt',
        'Choice sortable'  => 'fragment-14-choice-sortable.html.txt',
        'Multiple key value pair' => 'fragment-15-list.html.txt',
    );

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
            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $str = file_get_contents(static::getResourceFilesPath() . 'template.txt');
            file_put_contents($file, $str);
        }
    }

    /**
     * @param $contentBlock
     */
    static public function createContentBlockFile($contentBlock)
    {
        $file = static::getTemplatePath() . 'fragments/' . $contentBlock->twig;
        if (!file_exists($file)) {
            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $str = '<div>' . "\n";
            $objItems = $contentBlock->objItems();
            foreach ($objItems as $objItem) {
                $lines = explode("\n", str_replace('[value]', $objItem->id, file_get_contents(static::getResourceFilesPath() . 'fragments/' . static::BLOCK_MAP[$objItem->widget])));
                foreach ($lines as $line) {
                    $str .= "\t" . $line . "\n";
                }
            }
            $str .= '</div>';
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
