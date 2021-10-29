<?php

namespace ExWife\Engine\Web\_Core\Base\Controller;

use ExWife\Engine\Web\_Core\Base\Controller\Traits\WebControllerTrait;
use ExWife\Engine\Cms\_Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\_Core\Model\Model;
use ExWife\Engine\Cms\_Core\Service\CmsService;
use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class WebController
 * @package ExWife\Engine\Cms\_Core\Controller
 */
class WebController extends BaseController
{
    use WebControllerTrait;

    /**
     * @route("/{page}", requirements={"page" = ".*"})
     * @return Response
     */
    public function web(Request $request)
    {
        $params = $this->getParamsByRequest($request);
        return $this->render($params['theNode']->objPageTemplate()->fileName, $params);
    }
}
