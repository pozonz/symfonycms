<?php

namespace SymfonyCMS\Engine\Cms\_Core\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

use SymfonyCMS\Engine\Cms\_Core\ManageSearch\ManageSearchInterface;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;

use SymfonyCMS\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class ManageSearchController
 * @package SymfonyCMS\Engine\Cms\_Core\Controller
 */
class ManageSearchController extends AbstractController
{
    /**
     * @Route("/cms-search")
     * @param Connection $connection
     * @param Request $request
     */
    public function search(Connection $connection, Request $request, CmsService $cmsService)
    {
        $q = $request->get('q');
        $limit = 200;

        $accessibleModelNames = $cmsService->getUserAccessibleModelNames();
        if (!count($accessibleModelNames)) {
            return new JsonResponse([]);
        }
//var_dump($accessibleModelNames);exit;
        $qtnMks = array_map(function ($itm) {
            return '?';
        }, $accessibleModelNames);

        $fullClass = UtilsService::getFullClassFromName('ManageSearch');
        $data = $fullClass::active($connection, [
            'whereSql' => '(m.title LIKE ? OR m.description LIKE ? OR m.searchKeywords LIKE ?) AND (m.category IN (' . implode(',',  $qtnMks) . '))',
            'params' => array_merge(["%{$q}%", "%{$q}%", "%{$q}%"], $accessibleModelNames),
            'limit' => $limit,
        ]);

        $ids = array_map(function ($itm) {
            return $itm->id;
        }, $data);

        $qtnMks = array_map(function ($itm) {
            return '?';
        }, $data);

        $qs = explode(' ', $q);
        $qs = array_filter(array_map(function ($itm) {
            return trim($itm);
        }, $qs));
        if (count($qs) > 1) {
            $s = '';
            $p = [];
            foreach ($qs as $q) {
                $s .= ($s ? ' OR ' : '') . '(m.title LIKE ? OR m.description LIKE ? OR m.searchKeywords LIKE ?)';
                $p = array_merge($p, ["%{$q}%", "%{$q}%", "%{$q}%"]);
            }

            if (count($ids) && count($qtnMks)) {
                $data = array_merge($data, SiteSearch::active($connection, [
                    'whereSql' => '(' . $s . ') AND (m.id NOT IN ('. implode(',', $qtnMks) . ')) AND (m.category IN (' . implode(',',  $qtnMks) . '))',
                    'params' => array_merge($p, $ids, $accessibleModelNames),
                    'limit' => $limit - count($data),
//                    'debug' => 1,
                ]));
            } else {
                $data = array_merge($data, SiteSearch::active($connection, [
                    'whereSql' => '(' . $s . ') AND (m.category IN (' . implode(',',  $qtnMks) . '))',
                    'params' => array_merge($p, $accessibleModelNames),
                    'limit' => $limit - count($data),
                ]));
            }
        }

        $modelUrls = [];
        $data = array_values(array_filter(array_map(function ($itm) use ($connection, $cmsService, $modelUrls) {
            if (!$itm->url) {
                if ($itm->category == 'Page') {
                    $node = $cmsService->getNavTreeNodeByPageId($itm->ormId);
                    if ($node) {
                        $itm->url = $node->url;
                        return $itm;
                    }
                }

                if (!isset($modelUrls[$itm->category])) {
                    $model = UtilsService::getModelFromName($itm->category, $connection);
                    $node = $cmsService->getNavTreeNodeByModel($model);
                    $modelUrls[$itm->category] = $node ? $node->url : null;
                }

                $modelUrl = $modelUrls[$itm->category];
                if ($modelUrl) {
                    $itm->url = "$modelUrl/{$itm->ormId}";
                    return $itm;
                }

                return null;
            }

            return $itm;
        }, $data)));

        return new JsonResponse($data);
    }

    /**
     * @Route("/cms-search/sync", methods={"GET|POST"})
     * @param Connection $connection
     * @param Request $request
     * @return Response
     */
    public function sync(Connection $connection, Request $request, CmsService $cmsService)
    {
        $models = $cmsService->getModels();
        foreach ($models as $model) {
            $fullClass = UtilsService::getFullClassFromName($model->className);
            $obj = new $fullClass($connection);
            if ($obj instanceof ManageSearchInterface) {
                $data = $fullClass::active($connection);
                foreach ($data as $orm) {
                    $orm->updateManageSearchList();
                }
            }
        }

        $fullClass = UtilsService::getFullClassFromName('ManageSearch');
        $data = $fullClass::data($connection);
        foreach ($data as $itm) {
            $fullClass = UtilsService::getFullClassFromName($itm->category);
            $orm = $fullClass::active($connection, [
                'whereSql' => 'm.id = ?',
                'params' => [$itm->ormId],
                'limit' => 1,
                'oneOrNull' => 1,
            ]);
            if (!$orm) {
                $itm->delete();
            }
        }

        return new JsonResponse($data);
    }
}
