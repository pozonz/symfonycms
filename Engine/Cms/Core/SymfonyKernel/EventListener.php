<?php

namespace ExWife\Engine\Cms\Core\SymfonyKernel;

use BlueM\Tree\Node;
use Doctrine\DBAL\Connection;


use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class EventListener
{
    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var array|false|string
     */
    protected $_theme;

    public function __construct(Connection $connection, Environment $environment)
    {
        $this->_connection = $connection;
        $this->_environment = $environment;
        $this->_theme = CmsService::getTheme();
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelController(RequestEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        $fullClass = UtilsService::getFullClassFromName('Redirect');
        $redirect = $fullClass::getByField($this->_connection, 'title', $pathInfo);
        if ($redirect && $redirect->_status == 1) {
            return $event->setResponse(new RedirectResponse($redirect->to));
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof RedirectException) && !($exception instanceof NotFoundHttpException) && !($exception instanceof MethodNotAllowedHttpException)) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof RedirectException) {
            $event->setResponse(
                new RedirectResponse($exception->getUrl())
            );
            return;
        }

        if ($exception instanceof NotFoundHttpException) {
            $request = $event->getRequest();
            $pathInfo = $request->getPathInfo();
            if (strpos($pathInfo, '/manage') === 0) {
                $event->setResponse(
                    new Response(
                        $this->_environment->render("/cms/{$this->_theme}/404.twig", [
                            '_theme' => $this->_theme,
                            'cmsMenuItem' => null,
                        ])
                    )
                );
                return;
            }

            $fullClass = UtilsService::getFullClassFromName('Page');
            $page = $fullClass::getByField($this->_connection, 'url', '/404');
            if ($page) {
                $event->setResponse(
                    new Response(
                        $this->_environment->render($page->objPageTemplate()->fileName, [
                            'theNode' => new Node(uniqid(), uniqid(), [
                                'page' => $page,
                            ])
                        ])
                    )
                );
            }
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            $event->setResponse(
                new Response(
                    $this->_environment->render("/cms/{$this->_theme}/405.twig", [
                        '_theme' => $this->_theme,
                        'cmsMenuItem' => null,
                    ])
                )
            );
        }
    }
}
