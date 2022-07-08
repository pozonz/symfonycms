<?php

namespace SymfonyCMS\Engine\Cms\_Core\Base\Controller;

use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;

use SymfonyCMS\Engine\Cms\_Core\Service\ModelService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @route("/manage")
 * Class WebController
 * @package SymfonyCMS\Engine\Cms\_Core\Controller
 */
class ManageController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/section/{section}")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function section(Request $request)
    {
        $params = $this->getParamsByRequest($request);
        if (count($params['theNode']->children) > 0) {
            foreach ($params['theNode']->children as $child) {
                $childNode = $this->_getSectionChildNodeWithUrl($child);
                if ($childNode) {
                    return new RedirectResponse($childNode->url);
                }
            }
        }
        return $this->render("/cms/{$this->_theme}/section.twig", $params);
    }

    /**
     * @route("/data/sort")
     * @param Request $request
     * @param ModelService $modelService
     * @return JsonResponse
     */
    public function dataSort(Request $request, ModelService $modelService)
    {
        $className = $request->get('className');
        $data = $request->get('data');
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        if ($className == 'Model') {
            array_walk($data, function ($value, $key) use ($modelService) {
                $model = UtilsService::getModelFromName($value, $this->_connection);
                if ($model) {
                    $model->_rank = $model->modelCategory == 1 ? $key + 1000 : $key;
                    $model->save([
                        'modelService' => $modelService,
                    ]);
                }
            });

        } else {
            $fullClass = UtilsService::getFullClassFromName($className);
            if (!$fullClass) {
                throw new NotFoundHttpException();
            }

            array_walk($data, function ($value, $key) use ($fullClass) {
                $orm = $fullClass::getById($this->_connection, $value);
                if ($orm) {
                    $orm->_rank = $key;
                    $orm->save();
                }
            });
        }

        return new JsonResponse($data);
    }

    /**
     * @route("/data/status")
     * @param Request $request
     * @param ModelService $modelService
     * @return JsonResponse
     */
    public function dataStatus(Request $request, ModelService $modelService)
    {
        $className = $request->get('className');
        $id = $request->get('id');
        $value = $request->get('value');
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        if ($className == 'Model'){
            $model = UtilsService::getModelFromName($id, $this->_connection);
            if (!$model) {
                throw new NotFoundHttpException();
            }

            $model->_status = $value;
            $model->save([
                'modelService' => $modelService,
            ]);
            return new JsonResponse($model);

        } else {
            $fullClass = UtilsService::getFullClassFromName($className);
            if (!$fullClass) {
                throw new NotFoundHttpException();
            }

            $orm = $fullClass::getById($this->_connection, $id);
            if (!$orm) {
                throw new NotFoundHttpException();
            }

            $user = $this->_security->getUser();
            $orm->_userId = $user->getId();
            
            $orm->_status = $value;
            $orm->save();
            return new JsonResponse($orm);
        }
    }

    /**
     * @route("/data/delete")
     * @param Request $request
     * @param ModelService $modelService
     * @return JsonResponse
     */
    public function dataDelete(Request $request, ModelService $modelService)
    {
        $className = $request->get('className');
        $id = $request->get('id');
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        if ($className == 'Model') {
            $model = UtilsService::getModelFromName($id, $this->_connection);
            if (!$model) {
                throw new NotFoundHttpException();
            }

            $model->delete([
                'modelService' => $modelService,
            ]);
            return new JsonResponse($model);

        } else {
            $fullClass = UtilsService::getFullClassFromName($className);
            if (!$fullClass) {
                throw new NotFoundHttpException();
            }

            $orm = $fullClass::data($this->_connection, [
                'whereSql' => 'm.id = ?',
                'params' => [$id],
                'includePreviousVersion' => 1,
                'limit' => 1,
                'oneOrNull' => 1,
            ]);
            if (!$orm) {
                throw new NotFoundHttpException();
            }

            $orm->delete();
            return new JsonResponse($orm);

        }
    }

    /**
     * @route("/data/draft")
     * @param Request $request
     * @param ModelService $modelService
     * @return JsonResponse
     */
    public function dataDraft(Request $request, ModelService $modelService)
    {
        $className = $request->get('className');
        $id = $request->get('id');
        $name = $request->get('name');
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        $fullClass = UtilsService::getFullClassFromName($className);
        if (!$fullClass) {
            throw new NotFoundHttpException();
        }

        $orm = $fullClass::data($this->_connection, [
            'whereSql' => 'm.id = ?',
            'params' => [$id],
            'includePreviousVersion' => 1,
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $orm->_draftName = $name;
        $orm->save();
        return new JsonResponse($orm);
    }

    /**
     * @route("/model/note")
     * @param Request $request
     * @return JsonResponse
     */
    public function modelNote(Request $request)
    {
        $className = $request->get('className');
        $note = $request->get('note');
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        $model = UtilsService::getModelFromName($className, $this->_connection);
        if (!$model) {
            throw new NotFoundHttpException();
        }

        $modelNote = $model->objModelNote();
        $modelNote->note = $note;
        $modelNote->save();
        return new JsonResponse($modelNote);
    }

    /**
     * @route("/tree/sort")
     * @param Request $request
     * @return JsonResponse
     */
    public function treeSort(Request $request)
    {
        $className = $request->get('className');
        $data = (array)json_decode($request->get('data'));
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        $fullClass = UtilsService::getFullClassFromName($className);
        foreach ($data as $itm) {
            $orm = $fullClass::getById($this->_connection, $itm->id);
            $orm->_rank = $itm->rank;
            $orm->parentId = $itm->parentId ?: null;
            $orm->save();
        }

        return new JsonResponse($data);
    }

    /**
     * @route("/tree/closed")
     * @param Request $request
     * @return JsonResponse
     */
    public function treeClosed(Request $request)
    {
        $className = $request->get('className');
        $id = $request->get('id');
        $closed = $request->get('closed') ?: 0;
        $accessibleModelNames = $this->_cmsService->getUserAccessibleModelNames();
        if (!in_array($className, $accessibleModelNames)) {
            throw new MethodNotAllowedHttpException([]);
        }

        $fullClass = UtilsService::getFullClassFromName($className);
        $orm = $fullClass::getById($this->_connection, $id);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $orm->_closed = $closed;
        $orm->save();

        return new JsonResponse($orm);
    }

    /**
     * @route("")
     * @route("/{page}", requirements={"page" = ".*"})
     * @return Response
     */
    public function manage(Request $request, $page = null)
    {
        if (!$page) {
            $navTree = $this->_cmsService->getNavTree();
            if (count($navTree->getRootNodes()) && isset($navTree->getRootNodes()[0]->_slug)) {
                return new RedirectResponse("/manage/section/{$navTree->getRootNodes()[0]->_slug}");
            }
        }
        throw new NotFoundHttpException();
    }
}
