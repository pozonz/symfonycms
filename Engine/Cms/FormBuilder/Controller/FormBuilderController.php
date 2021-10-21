<?php

namespace ExWife\Engine\Cms\FormBuilder\Controller;

use BlueM\Tree;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Model\Form\OrmForm;
use ExWife\Engine\Cms\Core\ORM\Page;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use MillenniumFalcon\Core\Service\ModelService;
use MillenniumFalcon\Core\Tree\RawData;
use MillenniumFalcon\Core\Twig\Extension;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/manage")
 * Class OrmController
 * @package ExWife\Engine\Cms\Core\Controller
 */
class FormBuilderController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/section/{section}/orms/FormBuilder/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/FormBuilder/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function formBuilder(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'FormBuilder';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/formbuilder/formbuilder.twig",
        ]);
    }

    /**
     * @route("/section/{section}/orms/FormBuilder/copy/{id}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $ormId
     * @return mixed
     */
    public function copyFormBuilder(Request $request, $section, $id)
    {
        $className = 'FormBuilder';
        return $this->_copyOrm($request, $section, $className, $id, [
            'twig' => "/cms/{$this->_theme}/formbuilder/formbuilder.twig",
        ]);
    }

    /**
     * @route("/section/{section}/orms/FormSubmission", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function formSubmissions(Request $request, $section)
    {
        $className = 'FormSubmission';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPagination($request, $model, $section, function ($request, $whereSql, $whereParams) {
            $params = [];

            $formFullClass = UtilsService::getFullClassFromName('FormBuilder');
            $params['forms'] = $formFullClass::data($this->_connection, [
                'sort' => 'm.title',
            ]);

            $filterForm = $request->get('form') ?: 'all';
            $filterDateStart = $request->get('dateStart');
            $filterDateEnd = $request->get('dateEnd');
            $params['filterForm'] = $filterForm;
            $params['filterDateStart'] = $filterDateStart;
            $params['filterDateEnd'] = $filterDateEnd;

            if ($filterForm && $filterForm != 'all') {
                $whereSql .= ($whereSql ? ' AND ' : '') . "(m.formDescriptorId = ?)";
                $whereParams[] = $filterForm;
            }

            if ($filterDateStart) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m._added >= ?)';
                $whereParams[] = date('Y-m-d 00:00:00', strtotime($filterDateStart));
            }

            if ($filterDateEnd) {
                $whereSql .= ($whereSql ? ' AND ' : '') . '(m._added <= ?)';
                $whereParams[] = date('Y-m-d 23:59:59', strtotime($filterDateEnd));
            }

            return array_merge($params, [
                'whereSql' => $whereSql,
                'whereParams' => $whereParams,
            ]);
        });

        $export = $request->get('export') ?: null;
        if ($export == 1) {
            $fullClass = UtilsService::getFullClassFromName('FormBuilder');
            $formBuilder = $fullClass::getById($this->_connection, $result['filterForm']);
            if ($formBuilder) {
                $formFileds = json_decode($formBuilder->formFields);
                $filename = $formBuilder->_slug . '-export-' . date('Y-m-d-H-i-s');

                $header = [];
                $header[] = 'ID';
                $header[] = 'Added';
                foreach ($formFileds as $idx => $itm) {
                    $header[] = $itm->label;
                }
                $data[] = $header;

                foreach ($result['data'] as $itm) {
                    $row = [];
                    $row[] = $itm->title;
                    $row[] = date('d M Y H:i:s', strtotime($itm->_added));

                    $jsonContent = json_decode($itm->content);
                    foreach ($jsonContent as $idx => $itm) {
                        if ($itm[0] == 'antispam') {
                            continue;
                        }
                        if (gettype($itm[1]) == 'array' || gettype($itm[1]) == 'object') {
                            $itm[1] = (array)$itm[1];
                            $itm[1] = implode(',', $itm[1]);
                        }
                        $row[] = $itm[1];
                    }
                    $data[] = $row;
                }

                $this->download_send_headers($filename . ".csv");
                echo $this->array2csv($data);
                die();
            }
        }

        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/formbuilder/formsubmissions.twig", $params);
    }

    /**
     * @route("/section/{section}/orms/FormSubmission/{id}", requirements={"section" = ".*"})
     * @route("/section/{section}/orms/FormSubmission/{id}/version/{versionUuid}", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @param $id
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function formSubmission(Request $request, $section, $id, $versionUuid = null)
    {
        $className = 'FormSubmission';
        return $this->_orm($request, $section, $className, $id, $versionUuid, [
            'twig' => "/cms/{$this->_theme}/formbuilder/formsubmission.twig",
        ]);
    }

    /**
     * @param $filename
     */
    protected function download_send_headers($filename) {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }

    /**
     * @param array $array
     * @return false|string|null
     */
    protected function array2csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }
}
