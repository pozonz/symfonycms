<?php

namespace ExWife\Engine\Cms\Core\Base\Controller;

use ExWife\Engine\Cms\Core\Base\Controller\Traits\WebControllerTratis;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class WebController
 * @package ExWife\Engine\Cms\Core\Controller
 */
class WebController extends BaseController
{
    use WebControllerTratis;

    /**
     * @route("/sitemap.xml")
     * @return Response
     */
    public function sitemap(Request $request, CmsService $cmsService)
    {
        $sitemap = [];
        /** @var Model[] $models */
        $models = $cmsService->getModels();
        foreach ($models as $model) {
            if ($model->frontendUrl && $model->className !== 'Redirect') {
                $fullClass = UtilsService::getFullClassFromName($model->className);
                $orms = $fullClass::active($this->_connection);
                foreach ($orms as $orm) {
                    $sitemap[] = [
                        'url' => $request->getSchemeAndHttpHost() . $orm->getSiteMapUrl(),
                    ];
                }
            }
        }

        return $this->render('cms/sitemap/sitemap.xml.twig', [
            'sitemap' => $sitemap,
        ]);
    }

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
