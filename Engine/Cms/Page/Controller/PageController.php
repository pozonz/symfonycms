<?php

namespace ExWife\Engine\Cms\Page\Controller;

use BlueM\Tree;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;
use ExWife\Engine\Cms\Core\Model\Form\OrmForm;
use ExWife\Engine\Cms\Core\ORM\Page;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\Page\Service\PageService;
use MillenniumFalcon\Core\Service\ModelService;
use MillenniumFalcon\Core\Tree\RawData;
use MillenniumFalcon\Core\Twig\Extension;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class PageController
 * @package ExWife\Engine\Cms\Page\Controller
 */
class PageController extends BaseController
{
    use ManageControllerTratis;

    /**
     * @route("/section/{section}/orms/Page", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pages(Request $request, $section)
    {
        $fullClass = UtilsService::getFullClassFromName('PageCategory');
        $categories = $fullClass::active($this->_connection);
        $cat = $request->get('cat') || $request->get('cat') === '0' ? $request->get('cat') : (count($categories) == 0 ? 0 : $categories[0]->id);

        $returnQuery =  $request->getQueryString() ? '?' . $request->getQueryString() : '';
        if (!$returnQuery) {
            $returnQuery = "?cat=$cat";
        }

        /** @var Model $model */
        $model = UtilsService::getModelFromName('Page', $this->_connection);
        $params = array_merge($this->getParamsByRequest($request), [
            'model' => $model,
            'returnQuery' => $returnQuery,
            'categories' => $categories,
            'cat' => $cat,
            'pageTree' => PageService::getPageTreeByCategoryId($this->_connection, $cat),
        ]);
        return $this->render("/cms/{$this->_theme}/page/pages.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/Page/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/Page/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function page(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'Page';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/page/page.twig",
            'newOrmCallback' => function($orm) use ($request) {
                $cat = $request->get('cat');
                $orm->category = $cat ? json_encode([$cat]) : json_encode([]);
                return $orm;
            },
        ]);
    }

    /**
     * @route("/section/{section}/orms/Page/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function copyPage(Request $request, $section, $id)
    {
        $className = 'Page';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/page/page.twig",
        ]);
    }

    /**
     * @route("/page/category/count")
     * @return JsonResponse
     */
    public function pageCategoryCount()
    {
        $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
        $adminCmsMenuItem = $fullClass::getByField($this->_connection, '_slug', 'admin');
        if (!in_array($adminCmsMenuItem->id, $this->_security->getUser()->objAccessibleSections())) {
            throw new MethodNotAllowedHttpException([]);
        }

        $fullClass = UtilsService::getFullClassFromName('PageCategory');
        $pageCategories = $fullClass::active($this->_connection);

        $fullClass = UtilsService::getFullClassFromName('Page');
        /** @var Page[] $pages */
        $pages = $fullClass::data($this->_connection);

        $result = [];
        foreach ($pageCategories as $pageCategory) {
            $result["cat{$pageCategory->id}"] = 0;
            foreach ($pages as $page) {
                $category = (array)$page->objCategory();
                if (!$category) {
                    $category = [];
                }
                if (in_array($pageCategory->id, $category)) {
                    $result["cat{$pageCategory->id}"]++;
                }
            }
        }

        $result["cat0"] = 0;
        foreach ($pages as $page) {
            $category = (array)$page->objCategory();
            if (gettype($category) == 'array' && (in_array(0, $category) || !count($category))) {
                $result["cat0"]++;
            } elseif (!$category) {
                $result["cat0"]++;
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @route("/pages/sort")
     * @param Request $request
     * @return JsonResponse
     */
    public function pagesSort(Request $request)
    {
        $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
        $adminCmsMenuItem = $fullClass::getByField($this->_connection, '_slug', 'admin');
        if (!in_array($adminCmsMenuItem->id, $this->_security->getUser()->objAccessibleSections())) {
            throw new MethodNotAllowedHttpException([]);
        }

        $cat = $request->get('cat');
        $data = (array)json_decode($request->get('data'));

        $fullClass = UtilsService::getFullClassFromName('Page');
        foreach ($data as $itm) {
            /** @var Page $orm */
            $orm = $fullClass::getById($this->_connection, $itm->id);

            $category = (array)$orm->objCategory();
            if (!in_array($cat, $category)) {
                $category[] = $cat;
            }

            $categoryRank = (array)$orm->objCategoryRank();
            $categoryParent = (array)$orm->objCategoryParent();

            $categoryRank["cat{$cat}"] = $itm->rank;
            $categoryParent["cat{$cat}"] = $itm->parentId ?: null;

            $orm->category = json_encode($category);
            $orm->categoryRank = json_encode($categoryRank);
            $orm->categoryParent = json_encode($categoryParent);
            $orm->save();
        }

        return new JsonResponse($data);
    }

    /**
     * @route("/page/change")
     * @return Response
     */
    public function pageChange(Request $request)
    {
        $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
        $adminCmsMenuItem = $fullClass::getByField($this->_connection, '_slug', 'admin');
        if (!in_array($adminCmsMenuItem->id, $this->_security->getUser()->objAccessibleSections())) {
            throw new MethodNotAllowedHttpException([]);
        }

        $id = $request->get('id');
        $oldCat = $request->get('oldCat');
        $newCat = $request->get('newCat') ?: 0;

        $fullClass = UtilsService::getFullClassFromName('Page');
        $pageTree = PageService::getPageTreeByCategoryId($this->_connection, $oldCat);
        $pageNode = $pageTree->getNodeById($id);
        $nodes = $pageNode->getDescendantsAndSelf();

        foreach ($nodes as $node) {
            /** @var Page $orm */
            $orm = $fullClass::getById($this->_connection, $node->getId());

            $category = (array)$orm->objCategory();
            $category = array_filter($category, function ($itm) use ($oldCat) {
                return $oldCat != $itm;
            });
            if ($newCat != 0) {
                $category[] = $newCat;
            }

            $categoryRank = (array)$orm->objCategoryRank();
            $categoryParent = (array)$orm->objCategoryParent();

            $categoryRank["cat{$newCat}"] = $orm->id == $id ? 0 : $categoryRank["cat{$oldCat}"];
            $categoryParent["cat{$newCat}"] = $orm->id == $id ? null : $categoryParent["cat{$oldCat}"];

            unset($categoryRank["cat{$oldCat}"]);
            unset($categoryParent["cat{$oldCat}"]);

            $orm->category = json_encode($category);
            $orm->categoryRank = json_encode($categoryRank);
            $orm->categoryParent = json_encode($categoryParent);
            $orm->save();
        }

        return new Response('OK');
    }

    /**
     * @route("/page/closed")
     * @param Request $request
     * @return JsonResponse
     */
    public function pageClosed(Request $request)
    {
        $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
        $adminCmsMenuItem = $fullClass::getByField($this->_connection, '_slug', 'admin');
        if (!in_array($adminCmsMenuItem->id, $this->_security->getUser()->objAccessibleSections())) {
            throw new MethodNotAllowedHttpException([]);
        }
        
        $id = $request->get('id');
        $cat = $request->get('cat');
        $closed = $request->get('closed') ?: 0;

        $fullClass = UtilsService::getFullClassFromName('Page');
        /** @var Page $orm */
        $orm = $fullClass::getById($this->_connection, $id);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $categoryClosed = (array)$orm->objCategoryClosed();
        $categoryClosed["cat{$cat}"] = $closed;
        $orm->categoryClosed = json_encode($categoryClosed);
        $orm->save();

        return new JsonResponse($orm);
    }
}
