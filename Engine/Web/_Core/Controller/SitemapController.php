<?php

namespace SymfonyCMS\Engine\Web\_Core\Controller;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\BaseController;
use SymfonyCMS\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;

use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SitemapController extends AbstractController
{
    /** @var Connection $_connection */
    protected $_connection;

    /**
     * SitemapController constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }

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
}
