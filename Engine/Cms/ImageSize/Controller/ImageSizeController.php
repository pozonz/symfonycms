<?php

namespace ExWife\Engine\Cms\ImageSize\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;

use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @route("/manage")
 * Class ImageSizeController
 * @package ExWife\Engine\Cms\ImageSize\Controller
 */
class ImageSizeController extends BaseController
{
    use ManageControllerTratis;

    /**
     * @route("/section/{section}/orms/ImageSize", requirements={"section" = ".*"})
     * @param Request $request
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function imageSizes(Request $request, $section)
    {
        $className = 'ImageSize';
        $model = UtilsService::getModelFromName($className, $this->_connection);
        $result = $this->_ormsPaginationWithDataType($request, $model, $section, $section);
        $params = array_merge($this->getParamsByRequest($request), $result);
        return $this->render("/cms/{$this->_theme}/core/orms-pagination-with-datatype.twig", $params);
    }
}
