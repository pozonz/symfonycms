<?php

namespace SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits;

use BlueM\Tree;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Form\ModelForm;
use SymfonyCMS\Engine\Cms\_Core\Model\Form\OrmForm;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\ModelService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use SymfonyCMS\Engine\Cms\_Core\SymfonyKernel\RedirectException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

trait ManageControllerTrait
{
    /**
     * ManageControllerTrait constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     * @param Security $security
     * @param SessionInterface $session
     * @param CmsService $cmsService
     */
    public function __construct(Connection $connection, KernelInterface $kernel, Environment $environment, Security $security, SessionInterface $session, CmsService $cmsService)
    {
        parent::__construct($connection, $kernel, $environment, $security, $session);

        $this->_cmsService = $cmsService;
        $this->_theme = CmsService::getTheme();
    }

    /**
     * @return Tree\Node[]
     */
    protected function getNodes()
    {
        $navTree = $this->_cmsService->getNavTree();
        return $navTree->getNodes();
    }

    /**
     * @param $requestUri
     * @param array $options
     * @return array
     */
    protected function getParamsByUrl($requestUri, $options = [])
    {
        $params = parent::getParamsByUrl($requestUri, $options);

        $cmsMenuItem = null;
        if (strpos($requestUri, '/manage/section/') === 0) {
            $requestUriFragments = array_filter(explode('/', $requestUri));
            if (count($requestUriFragments) >= 3) {
                $fullClass = UtilsService::getFullClassFromName('CmsMenuItem');
                $cmsMenuItem = $fullClass::getActiveBySlug($this->_connection, $requestUriFragments[3]);
            }
        }
//        var_dump($cmsMenuItemNode->getChildren());exit;
        return array_merge($params, [
            '_theme' => $this->_theme,
            'cmsMenuItem' => $cmsMenuItem,
        ]);
    }

    /**
     * @param $node
     * @return null
     */
    protected function _getSectionChildNodeWithUrl($node)
    {
        if ($node->url) {
            return $node;
        }

        foreach ($node->children as $child) {
            $childNode = $this->_getSectionChildNodeWithUrl($child);
            if ($childNode) {
                return $childNode;
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param $model
     * @return array|RedirectResponse
     */
    protected function _modelFormAndParams(Request $request, $section, $model, $options)
    {
        $modelService = $options['modelService'] ?? null;

        $returnUrl = "/manage/section/{$section}/models";
        $returnQuery = $request->getQueryString() ? '?' . $request->getQueryString() : '';

        $form = $this->container->get('form.factory')->create(ModelForm::class, $model, [
            'connection' => $this->_connection,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->save([
                'modelService' => $modelService
            ]);

            $modelService->saveClassTraitFile($model);
            $modelService->saveClassFile($model);

            $submit = $request->get('submit');
            if ($submit == 'Save & exit') {
                throw new RedirectException("{$returnUrl}{$returnQuery}");
            }
            throw new RedirectException("{$returnUrl}/{$model->className}{$returnQuery}");
        }

        $modelColumnWidgets = ModelService::getModelColumnWidgets();
        $modelColumnTypes = ModelService::getModelColumnTypes();
        $modelColumnTypes = array_map(function ($itm, $idx) {
            return [
                'value' => $idx,
                'label' => strpos($idx, 'date') !== false ? '(Date) ' . $idx : $idx,
            ];
        }, $modelColumnTypes, array_keys($modelColumnTypes));

        $params = array_merge($this->getParamsByRequest($request), [
            'formView' => $form->createView(),
            'model' => $model,
            'modelColumnWidgets' => $modelColumnWidgets,
            'modelColumnTypes' => $modelColumnTypes,
            'returnUrl' => "{$returnUrl}{$returnQuery}",
        ]);
        return $params;
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return array
     */
    protected function _ormsDragDrop(Request $request, Model $model, $section)
    {
        $fullClass = UtilsService::getFullClassFromName($model->className);
        $template = "/cms/{$this->_theme}/core/orms.twig";
        $default = $this->_ormsDefault($request, $model, $section);

        $data = $fullClass::data($this->_connection);
        return array_merge($default, [
            'template' => $template,
            'total' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * @param Request $request
     * @param Model $model
     * @param null $extraFilterCallback
     * @return array
     */
    protected function _ormsPagination(Request $request, Model $model, $section, $extraFilterCallback = null)
    {
        $fullClass = UtilsService::getFullClassFromName($model->className);
        $template = "/cms/{$this->_theme}/core/orms-pagination.twig";
        $default = $this->_ormsDefault($request, $model, $section);

        $keyword = $request->get('keyword') ?: null;
        $pageNum = $request->get('pageNum') ?: 1;
        $limit = $model->pageSize;
        $sort = $request->get('sort') ?: $model->defaultSortBy;
        $order = $request->get('order') ?: $model->defaultOrderBy;

        $keyword = trim($keyword);
        
        $whereSql = '';
        $whereParams = [];

        $columnsJson = $default['columnsJson'] ?? [];
        $fields = array_filter(array_map(function ($itm) {
            return $itm->queryable == 1 ? $itm->field : null;
        }, $columnsJson));
        if ($keyword && count($fields) > 0) {
            $whereSql = ($whereSql ? ' AND ' : '') . ('(' . join(' OR ', array_map(function ($field) {
                        return "m.{$field} LIKE ?";
                    }, $fields)) . ')');

            $whereParams = array_merge($whereParams, array_map(function ($field) use ($keyword) {
                $keyword = trim($keyword);
                return "%{$keyword}%";
            }, $fields));
        }

        $extraFilterParams = [];
        if ($extraFilterCallback) {
            $extraFilterParams = $extraFilterCallback($request, $whereSql, $whereParams);
            $whereSql = $extraFilterParams['whereSql'] ?? null;
            $whereParams = $extraFilterParams['whereParams'] ?? [];
        }

        $data = $fullClass::data($this->_connection, [
            'whereSql' => $whereSql,
            'params' => $whereParams,
            'page' => $pageNum,
            'limit' => $limit,
            'sort' => $sort,
            'order' => $order,
        ]);

        $total = $fullClass::data($this->_connection, [
            'whereSql' => $whereSql,
            'params' => $whereParams,
            'count' => 1,
        ]);
        $pageTotal = ceil($total['count'] / $limit);

        return array_merge($default, [
            'template' => $template,
            'data' => $data,
            'total' => $total['count'],
            'pageTotal' => $pageTotal,
            'keyword' => $keyword,
            'pageNum' => $pageNum,
            'sort' => $sort,
            'order' => $order,
        ], $extraFilterParams);
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return array
     */
    protected function _ormsTree(Request $request, Model $model, $section)
    {
        $fullClass = UtilsService::getFullClassFromName($model->className);
        $template = "/cms/{$this->_theme}/core/orms-tree.twig";
        $default = $this->_ormsDefault($request, $model, $section);

        $result = $fullClass::data($this->_connection);

        $nodes = [];
        foreach ($result as $itm) {
            $nodes[] = [
                'id' => $itm->id,
                'parent' => $itm->parentId,
                'title' => $itm->title,
                'status' => $itm->_status,
                'closed' => $itm->_closed,
            ];
        }

        $data = new Tree($nodes, [
            'rootId' => null,
            'buildwarningcallback' => function () {
            },
        ]);

        return array_merge($default, [
            'template' => $template,
            'total' => count($nodes),
            'data' => $data,
        ]);
    }

    /**
     * @param Request $request
     * @param $className
     * @return Response
     */
    protected function _ormsPaginationWithDataType(Request $request, Model $model, $section)
    {
        $model = UtilsService::getModelFromName($model->className, $this->_connection);
        $result = $this->_ormsPagination($request, $model, $section, function ($request, $whereSql, $whereParams) {
            $dataType = $request->get('dataType') ?: 1;
            $whereSql .= ($whereSql ? ' AND ' : '') . '(m.dataType = ?)';
            $whereParams[] = $dataType;
            return [
                'whereSql' => $whereSql,
                'whereParams' => $whereParams,
                'dataType' => $dataType,
            ];
        });
        return $result;
    }

    /**
     * @param Request $request
     * @param Model $model
     * @return array
     */
    protected function _ormsDefault(Request $request, Model $model, $section)
    {
        $returnQuery = $request->getQueryString() ? '?' . $request->getQueryString() : '';
        $columnsJson = $model->objColumnsJson();

        $columns = [];

        if ($model->_displayAdded) {
            $columns[] = [
                'label' => 'Added',
                'field' => '_added',
                'listingWidth' => 150,
                'queryable' => null,
            ];
        }

        $columns = array_merge($columns, array_filter(array_map(function ($itm) {
            return $itm->listing == 1 ? array_merge((array)$itm, [
                'label' => rtrim($itm->label, ':'),
            ]) : null;
        }, $columnsJson)));

        if ($model->_displayModified) {
            $columns[] = [
                'label' => 'Modified',
                'field' => '_modified',
                'listingWidth' => 150,
                'queryable' => null,
            ];
        }

        if ($model->_displayUser) {
            $columns[] = [
                'label' => 'User',
                'field' => '_userId',
                'listingWidth' => 150,
                'queryable' => null,
            ];
        }

        return [
            'model' => $model,
            'returnQuery' => $returnQuery,
            'columnsJson' => $columnsJson,
            'columns' => $columns,
            'section' => $section,
        ];
    }

    /**
     * @param Request $request
     * @param $section
     * @param $className
     * @param $id
     * @param null $versionUuid
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws RedirectException
     */
    protected function _orm(Request $request, $section, $className, $id, $versionUuid = null, $options = [])
    {
        $twig = $options['twig'] ?? "/cms/{$this->_theme}/core/orm.twig";
        $formClass = $options['formClass'] ?? OrmForm::class;
        $newOrmCallback = $options['newOrmCallback'] ?? null;
        $existOrmCallback = $options['existOrmCallback'] ?? null;
        $callback = $options['callback'] ?? null;

        $fullClass = UtilsService::getFullClassFromName($className);
        $orm = $fullClass::getById($this->_connection, $id);
        if (!$orm) {
            $orm = new $fullClass($this->_connection);

            $fields = array_keys($orm->_getReflectionData()->fields);
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }

                $value = $request->get($field);
                if ($value) {
                    $orm->$field = $value;
                }
            }

            if ($newOrmCallback) {
                $orm = $newOrmCallback($orm);
            }
        }

        if ($existOrmCallback) {
            $orm = $existOrmCallback($orm);
        }

        if ($versionUuid) {
            $orm = $orm->getByVersionUuid($versionUuid);
        }

        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $refererReturnUrl = $request->get('returnUrl');
        $listingUrl = "/manage/section/{$section}/orms/{$className}";
        $returnQuery = $request->getQueryString() ? '?' . $request->getQueryString() : '';
        $returnUrl = $refererReturnUrl ?: "{$listingUrl}{$returnQuery}";
        $params = $this->getParamsByRequest($request, $options);
        if (!isset($params['cmsMenuItem']) || !$params['cmsMenuItem'] || ($params['cmsMenuItem']->_slug == 'pages' && $className == 'Page')) {
            $returnUrl = null;
        }

        $model = UtilsService::getModelFromName($className, $this->_connection);
        $form = $this->container->get('form.factory')->create($formClass, $orm, [
            'model' => $model,
            'orm' => $orm,
            'connection' => $this->_connection,
            'cmsService' => $this->_cmsService,
        ]);

        $submitted = 0;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = 1;
            $isNew = $orm->id ? 0 : 1;

            $submit = $request->get('submit');

            $this->_convertDataFormat($orm, $model);

            $user = $this->_security->getUser();
            $orm->_userId = $user->getId();

            switch ($submit) {
                case 'Preview':
                    $newOrm = $orm->savePreview();
                    $newOrm->_userId = $user->getId();
                    $newOrm->save(true);
                    throw new RedirectException($orm->getSiteMapUrl() . "?__preview_" . strtolower($className) . "=" . $newOrm->_versionUuid);
                    break;
                case 'Restore':
                case 'Publish draft':
                    $orm->delete();

                    $orm->id = $orm->_originalOrm->id;
                    foreach ($orm->_originalOrm as $idx => $itm) {
                        if (strpos($idx, '_') === 0) {
                            $orm->$idx = $itm;
                        }
                    }

                    $orm->save([
                        'saveVersion' => 1,
                    ]);
                    break;
                case 'Save as draft':
                    $data = $request->get('orm');
                    $draftName = $data['__draftName'] ?? '';
                    $orm->save([
                        'saveVersion' => 1,
                        'draftName' => $draftName,
                    ]);
                    break;
                default:
                    $orm->save([
                        'saveVersion' => 1,
                    ]);
            }

            if ($callback) {
                call_user_func($callback, $form, $orm);
            }

            switch ($submit) {
                case 'Save as draft':
                    throw new RedirectException("{$listingUrl}/{$orm->_versionOrmId}/version/{$orm->_versionUuid}{$returnQuery}");
                case 'Save & exit':
                    throw new RedirectException($returnUrl);
                case 'Restore':
                case 'Publish draft':
                    throw new RedirectException("{$listingUrl}/{$orm->id}{$returnQuery}");
                case 'Apply':
                    if ($isNew) {
                        throw new RedirectException("{$listingUrl}/{$orm->id}{$returnQuery}");
                    }
            }

            $form = $this->container->get('form.factory')->create($formClass, $orm, [
                'model' => $model,
                'orm' => $orm,
                'connection' => $this->_connection,
                'cmsService' => $this->_cmsService,
            ]);
        }

        return $this->render($twig, array_merge($params, [
            'formView' => $form->createView(),
            'model' => $model,
            'orm' => $orm,
            'returnUrl' => $returnUrl,
            'returnQuery' => $returnQuery,
            'section' => $section,
            'submitted' => $submitted,
        ], $options));
    }

    /**
     * @param Request $request
     * @param $section
     * @param $className
     * @param $id
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws RedirectException
     */
    protected function _copyOrm(Request $request, $section, $className, $id, $options = [])
    {
        return $this->_orm($request, $section, $className, $id, null, array_merge([
            'copying' => 1,
            'existOrmCallback' => function ($orm) {
                $orm->id = null;
                $orm->_uniqid = Uuid::uuid4();
                $orm->_added = date('Y-m-d H:i:s');
                $orm->_modified = date('Y-m-d H:i:s');
                return $orm;
            },
        ], $options));
    }

    /**
     * @param $orm
     * @param Model $model
     * @param string[] $formats
     */
    protected function _convertDataFormat($orm, Model $model, $formats = [
        'Date picker' => 'Y-m-d 00:00:00',
        'Date & time picker' => 'Y-m-d H:i:s',
    ])
    {
        $objColumnJson = $model->objColumnsJson();

        foreach ($objColumnJson as $columnJson) {
            $field = $columnJson->field;

            if (isset($formats[$columnJson->widget])) {
                $dateStr = $orm->$field;
                if ($dateStr) {
                    $format = $formats[$columnJson->widget];
                    $orm->$field = date($format, strtotime($dateStr));
                }
            }
        }
    }
}
