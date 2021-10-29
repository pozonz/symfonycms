<?php

namespace ExWife\Engine\Cms\_Core\Service;

use BlueM\Tree;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\_Core\ORM\Page;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\KernelInterface;

class UtilsService
{
    /**
     * @param $theNode
     * @param $node
     * @param Tree $tree
     * @return int
     */
    static public function theNodeInParent($theNode, $node, Tree $tree)
    {
        try {
            $theNode = $tree->getNodeById($theNode->id);
            $ancestors = $theNode->getAncestorsAndSelf();

            return count(array_filter($ancestors, function ($ancestor) use ($node) {
                return $ancestor->getId() == $node->getId() ? 1 : 0;
            }));

        } catch (\Exception $ex) {

        }

        return 0;
    }

    /**
     * @param $theNode
     * @param Tree $tree
     * @return int
     */
    static public function hasActiveChildren($node, Tree $tree)
    {
        if (isset($node->children)) {
            foreach ($node->children as $child) {
                if ($child->status == 1) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * @param $className
     * @param $connection
     * @return mixed|null
     */
    static public function getModelFromName($className, $connection)
    {
        $fullClassNames = [
            "\\App\\ORM\\Model\\{$className}Model",
            "\\ExWife\\Engine\\Cms\\_Core\\ORM\\Model\\{$className}Model",
        ];
        foreach ($fullClassNames as $fullClassName) {
            if (class_exists($fullClassName)) {
                return new $fullClassName($connection);
            }
        }
        return null;
    }

    /**
     * @param $className
     * @return string|null
     */
    static public function getFullClassFromName($className)
    {
        $fullClassNames = [
            "\\App\\ORM\\{$className}",
            "\\ExWife\\Engine\\Cms\\_Core\\ORM\\{$className}",
        ];
        foreach ($fullClassNames as $fullClassName) {
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }
        return null;
    }

    /**
     * @param $valLength
     * @return bool|string
     */
    static public function generateHex($valLength)
    {
        $result = '';
        $moduleLength = 40;   // we use sha1, so module is 40 chars
        $steps = round(($valLength / $moduleLength) + 0.5);

        for ($i = 0; $i < $steps; $i++) {
            $result .= sha1(uniqid() . md5(rand() . uniqid()));
        }

        return substr($result, 0, $valLength);
    }

    /**
     * @param $name
     * @param string $delimeter
     * @return mixed|string
     */
    static public function basename($name, $delimeter = '\\')
    {
        $array = explode($delimeter, $name);
        return end($array);
    }
}