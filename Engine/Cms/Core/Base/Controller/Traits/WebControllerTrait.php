<?php

namespace ExWife\Engine\Cms\Core\Base\Controller\Traits;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

trait WebControllerTrait
{
    /**
     * @return mixed
     */
    protected function getNodes()
    {
        $fullClass = UtilsService::getFullClassFromName('Page');
        return $fullClass::active($this->_connection, [
            'ignorePreview' => 1,
        ]);
    }

    /**
     * @param ContainerInterface $container
     * @return ContainerInterface|null
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $dir = __DIR__ . '/../../../../../../Resources/views/web';
        if (file_exists($dir)) {
            $loader = $container->get('twig')->getLoader();
            $loader->addPath($dir);
        }

        return parent::setContainer($container);
    }

    /**
     * @param $requestUri
     * @param array $options
     * @return array
     */
    protected function getParamsByUrl($requestUri, $options = [])
    {
        $params = parent::getParamsByUrl($requestUri, $options);
        $fullClass = UtilsService::getFullClassFromName('Page');
        $params['theNode']->page = $fullClass::getById($this->_connection, $params['theNode']->id);
        return $params;
    }

}
