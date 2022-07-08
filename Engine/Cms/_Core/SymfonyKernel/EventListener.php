<?php

namespace SymfonyCMS\Engine\Cms\_Core\SymfonyKernel;

use BlueM\Tree\Node;
use Doctrine\DBAL\Connection;


use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
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
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof RedirectException) && !($exception instanceof NotFoundHttpException) && !($exception instanceof MethodNotAllowedHttpException)) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof RedirectException) {
            $event->setResponse(
                new RedirectResponse($exception->getUrl())
            );
            return;
        }

        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        if (strpos($pathInfo, '/manage') === 0) {
            if ($exception instanceof NotFoundHttpException) {
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

        if ($exception instanceof NotFoundHttpException) {
            $fullClass = UtilsService::getFullClassFromName('Page');
            $page = $fullClass::getByField($this->_connection, 'url', '/404');
            if ($page) {
                $event->setResponse(
                    new Response(
                        $this->_environment->render('404.twig', [
                            'theNode' => new Node(uniqid(), uniqid(), [
                                'page' => $page,
                            ])
                        ])
                    )
                );
                return;
            }
        }
    }
}
