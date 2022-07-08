<?php

namespace SymfonyCMS\Engine\Web\_Core\Service;

use BlueM\Tree;
use BlueM\Tree\Node;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Base\ORM\BaseORM;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\KernelInterface;

class WebService
{
    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var KernelInterface
     */
    protected $_kernel;

    /**
     * CmsService constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     */
    public function __construct(Connection $connection, KernelInterface $kernel)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
    }

    /**
     * @param $categoryCode
     * @return Tree|null
     */
    public function getNavTree($categoryCode)
    {
        $result = null;
        $fullClass = UtilsService::getFullClassFromName('PageCategory');
        $category = $fullClass::getByField($this->_connection, 'code', $categoryCode);
        if ($category) {
            $fullClass = UtilsService::getFullClassFromName('Page');
            $pages = $fullClass::active($this->_connection, [
                'whereSql' => 'm.category LIKE ? ',
                'params' => array('%"' . $category->id . '"%'),
                'ignorePreview' => 1,
            ]);

            $nodes = [];
            foreach ($pages as $itm) {
                $categoryParent = !$itm->categoryParent ? [] : (array)json_decode($itm->categoryParent);
                $categoryRank = !$itm->categoryRank ? [] : (array)json_decode($itm->categoryRank);
                $parent = isset($categoryParent['cat' . $category->id]) ? $categoryParent['cat' . $category->id] : 0;
                $rank = isset($categoryRank['cat' . $category->id]) ? $categoryRank['cat' . $category->id] : 0;

                $node = $itm->jsonSerialize();
                $node->status = $itm->_status == 1 && $itm->hideFromFrontendNav != 1 ? 1 : 0;
                $node->parent = $parent . '';
                $node->rank = $rank;

                $nodes[] = (array)$node;
            }

            usort($nodes, function ($a, $b) {
                return $a['rank'] >= $b['rank'] ? 1 : 0;
            });

            return new Tree($nodes, [
                'buildwarningcallback' => function () {
                },
            ]);
        }

        return null;
    }

    /**
     * @param $id
     * @param Tree $tree
     * @return Node|null
     */
    static public function getNodeByIdInTree($id, Tree $tree)
    {
        try {
            return $tree->getNodeById($id);
        } catch (\InvalidArgumentException $ex) {
        }
        return null;
    }


    /**
     * @param Node $parentNode
     * @param Node|null $childNode
     * @param Tree $tree
     * @return bool
     */
    static public function containsInTree(Node $parentNode, Node $childNode = null, Tree $tree)
    {
        if (!$childNode) {
            return false;
        }

        try {
            $myChildNode = $tree->getNodeById($childNode->getId());
        } catch (\InvalidArgumentException $ex) {
            $myChildNode = $childNode;
        }
        return static::contains($parentNode, $myChildNode);
    }

    /**
     * @param Node $theNode
     * @return bool
     */
    static public function hasActiveChildren(Node $theNode)
    {
        foreach ($theNode->getChildren() as $child) {
            if ($child->get('status') == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Node $parentNode
     * @param Node $childNode
     * @return bool
     */
    static public function contains(Node $parentNode, Node $childNode)
    {
        if ($childNode->getId() == $parentNode->getId()) {
            return true;
        }

        $ancestors = $childNode->getAncestors();
        foreach ($ancestors as $ancestor) {
            if ($ancestor->getId() == $parentNode->getId()) {
                return true;
            }
        }
        return false;
    }
}