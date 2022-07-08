<?php

namespace SymfonyCMS\Engine\Cms\_Core\Base\Controller;

use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;

use BlueM\Tree;
use Doctrine\DBAL\Connection;

use SymfonyCMS\Engine\Cms\_Core\SymfonyKernel\RedirectException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class BaseController
 * @package SymfonyCMS\Core
 */
abstract class BaseController extends AbstractController
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
     * @var Environment
     */
    protected $_environment;

    /**
     * @var CmsService
     */
    protected $_cmsService;

    /**
     * @var Security
     */
    protected $_security;

    /**
     * @var SessionInterface 
     */
    protected $_session;

    /** @var array|false|string $_theme */
    protected $_theme;

    /**
     * BaseController constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     * @param Security $security
     */
    public function __construct(Connection $connection, KernelInterface $kernel, Environment $environment, Security $security, SessionInterface $session)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
        $this->_environment = $environment;
        $this->_security = $security;
        $this->_session = $session;
        $this->_environment->getLoader()->addPath(__DIR__ . '/../../../../../Resources/views');
    }

    /**
     * @param Request $request
     * @param $options
     * @return array
     */
    protected function getParamsByRequest(Request $request, $options = [])
    {
        return $this->getParamsByUrl($request->getPathInfo(), $options);
    }

    /**
     * @param $requestUri
     * @param array $options
     * @return array
     */
    protected function getParamsByUrl($requestUri, $options = [])
    {
        $allowNodeNotFound = $options['allowNodeNotFound'] ?? 0;

        $requestUri = rtrim($requestUri, '/');
        $urlFragments = explode('/', trim($requestUri, '/'));
        $urlParams = [];

        $node = $this->getNodeByUrl($requestUri);

        if (!$node) {
            for ($i = count($urlFragments), $il = 0; $i > $il; $i--) {
                $parts = array_slice($urlFragments, 0, $i);
                $node = $this->getNodeByUrl('/' . implode('/', $parts) . '/');
                if (!$node) {
                    $node = $this->getNodeByUrl('/' . implode('/', $parts));
                }
                if ($node && isset($node->allowExtra) && isset($node->maxParams)) {
                    if ((!$node->allowExtra && (count($urlFragments) - count($parts) == 0)) ||
                        ($node->allowExtra && $node->maxParams >= (count($urlFragments) - count($parts)))) {
                        $urlParams = array_values(array_diff($urlFragments, $parts));
                        break;
                    } else {
                        throw new NotFoundHttpException();
                    }
                }
            }
        }

        if (!$node && !$allowNodeNotFound) {
            throw new NotFoundHttpException();
        }

        if ($node && isset($node->type) && $node->type == 2 && $node->redirectTo) {
            throw new RedirectException($node->redirectTo);
        }

        return [
            'urlParams' => $urlParams,
            'urlFragments' => $urlFragments,
            'theNode' => $node,
        ];
    }

    /**
     * @param $url
     * @return mixed|null
     */
    protected function getNodeByUrl($url)
    {
        $nodes = $this->getNodes();
        foreach ($nodes as $node) {
            if ($node->url == $url) {
                return $node;
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    abstract protected function getNodes();
}
