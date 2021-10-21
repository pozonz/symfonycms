<?php

namespace ExWife\Engine\Cms\Core\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\ModelService;
use ExWife\Engine\Cms\Core\Model\Form\ModelForm;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @route("/install")
 * Class ModelController
 * @package ExWife\Engine\Cms\Model\Controller
 */
class InstallController extends AbstractController
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
     * InstallController constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     */
    public function __construct(Connection $connection, KernelInterface $kernel)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
    }

    /**
     * @route("/models/sync")
     * @param Request $request
     * @param CmsService $cmsService
     * @return JsonResponse
     */
    public function models(Request $request, CmsService $cmsService, ModelService $modelService)
    {
        /** @var Model[] $data */
        $data = $cmsService->getModels();
        foreach ($data as $itm) {
            $itm->save([
                'modelService' => $modelService,
            ]);
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/model/extra/zones")
     * @return JsonResponse
     */
    public function extraZones()
    {
        ini_set('max_execution_time', 9999);
        ini_set('memory_limit', '9999M');

        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        $data = $fullClass::data($this->_connection);
        foreach ($data as $itm) {
            $itm->delete();
        }

        $phpDir = __DIR__ . '/../../../../Migrations/php';

        $countries = require "$phpDir/countries.php";
        $states = require "$phpDir/states.php";

        $count = 0;
        foreach ($countries as $countryIdx => $countryVal) {
            $ormCountry = new $fullClass($this->_connection);
            $ormCountry->title = $countryVal[0];
            $ormCountry->code = $countryIdx;
            $ormCountry->_closed = 1;
            $ormCountry->_rank = $count;
            $ormCountry->save();
            $count++;

            if (isset($states[$countryIdx]) && gettype($states[$countryIdx]) == 'array' && count($states[$countryIdx]) > 0) {
                foreach ($states[$countryIdx] as $stateIdx => $stateVal) {
                    $ormState = new $fullClass($this->_connection);
                    $ormState->title = $stateVal[0];
                    $ormState->code = $stateIdx;
                    $ormState->parentId = $ormCountry->id;
                    $ormState->_rank = $count;
                    $ormState->save();
                    $count++;
                }
            }
        }

        return new JsonResponse([
            $count
        ]);
    }
}
