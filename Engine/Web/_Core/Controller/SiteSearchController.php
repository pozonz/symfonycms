<?php

namespace ExWife\Engine\Web\_Core\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

use ExWife\Engine\Cms\_Core\Service\CmsService;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

use ExWife\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SiteSearchController
 * @package ExWife\Engine\Cms\_Core\Controller
 */
class SiteSearchController extends AbstractController
{
    /**
     * @Route("/site-search")
     * @param Connection $connection
     * @param Request $request
     */
    public function search(Connection $connection, Request $request)
    {
        $q = $request->get('q');

        $fullClass = UtilsService::getFullClassFromName('SiteSearch');
        $data = $fullClass::active($connection, [
            'whereSql' => 'm.title LIKE ? OR m.description LIKE ? OR m.searchKeywords LIKE ?',
            'params' => ["%{$q}%", "%{$q}%", "%{$q}%"],
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
                    'whereSql' => '(' . $s . ') AND (m.id NOT IN ('. implode(',', $qtnMks) . '))',
                    'params' => array_merge($p, $ids),
//                    'debug' => 1,
                ]));
            } else {
                $data = array_merge($data, SiteSearch::active($connection, [
                    'whereSql' => $s,
                    'params' => $p,
                ]));
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/site-search/sync", methods={"GET|POST"})
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
            if ($obj instanceof SiteSearchInterface) {
                $data = $fullClass::active($connection);
                foreach ($data as $orm) {
                    $orm->updateSiteSearchList();
                }
            }
        }

        $fullClass = UtilsService::getFullClassFromName('SiteSearch');
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
