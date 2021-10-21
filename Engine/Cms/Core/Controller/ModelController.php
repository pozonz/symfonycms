<?php

namespace ExWife\Engine\Cms\Core\Controller;

use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Service\ModelService;
use ExWife\Engine\Cms\Core\Model\Form\ModelForm;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class ModelController
 * @package ExWife\Engine\Cms\Model\Controller
 */
class ModelController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/section/{section}/models", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function models(Request $request, $section)
    {
        $returnQuery = $request->getQueryString() ? '?' . $request->getQueryString() : '';
        $filterKeyword = $request->get('keyword');
        $filterModelCategory = $request->get('modelCategory') ?: 1;

        $data = $this->_cmsService->getModels();

        if ($filterKeyword) {
            $data = array_filter($data, function ($itm) use ($filterKeyword) {
                return strpos(strtolower($itm->title), strtolower($filterKeyword)) !== false || strpos(strtolower($itm->className), strtolower($filterKeyword)) !== false;
            });
        }

        if ($filterModelCategory) {
            $data = array_filter($data, function ($itm) use ($filterModelCategory) {
                return $itm->modelCategory == $filterModelCategory;
            });
        }

        usort($data, function ($a, $b) {
            $diff = $a->_rank - $b->_rank;

            if ($diff == 0) {
                return strcmp($a->title, $b->title) > 0;
            }

            return $diff > 0;
        });

        $params = array_merge($this->getParamsByRequest($request), [
            'data' => $data,
            'filterKeyword' => $filterKeyword,
            'filterModelCategory' => $filterModelCategory,
            'returnQuery' => $returnQuery
        ]);
        return $this->render("/cms/{$this->_theme}/core/models.twig", $params);
    }

    /**
     * @route("/section/{section}/models/{className}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param ModelService $modelService
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function model(Request $request, $section, $className, ModelService $modelService)
    {
        $returnUrl = "/manage/section/{$section}/models";
        $returnQuery = $request->getQueryString() ? '?' . $request->getQueryString() : '';

        $model = UtilsService::getModelFromName($className, $this->_connection);
        if (!$model) {
            $model = new Model($this->_connection);

            $user = $this->_security->getUser();
            $model->_userId = $user->getId();

            $fields = array_keys($model->_getReflectionData()->fields);
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }

                $value = $request->get($field);
                if ($value) {
                    $model->$field = $value;
                }
            }
        }

        $params = $this->_modelFormAndParams($request, $section, $model, [
            'modelService' => $modelService,
        ]);
        return $this->render("/cms/{$this->_theme}/core/model.twig", (array)$params);
    }

    /**
     * @route("/section/{section}/models/copy/{className}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $className
     * @param ModelService $modelService
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function copyModel(Request $request, $section, $className, ModelService $modelService)
    {
        $model = UtilsService::getModelFromName($className, $this->_connection);
        if (!$model) {
            $model = new Model($this->_connection);

            $user = $this->_security->getUser();
            $model->_userId = $user->getId();
        }

        $model->id = null;
        $model->_uniqid = Uuid::uuid4();
        $model->_added = date('Y-m-d H:i:s');
        $model->_modified = date('Y-m-d H:i:s');

        $params = $this->_modelFormAndParams($request, $section, $model, [
            'modelService' => $modelService,
        ]);
        return $this->render("/cms/{$this->_theme}/core/model.twig", array_merge($params, [
            'copying' => 1,
        ]));
    }

    /**
     * @route("/shipping/regions")
     * @return Response
     */
    public function shippingRegions(Request $request)
    {
        $zone = $request->get('zone');

        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        $orms = $fullClass::active($this->_connection, [
            'whereSql' => 'm.parentId = ?',
            'params' => [$zone],
            'sort' => 'm.title'
        ]);
        return new JsonResponse($orms);
    }
}
