<?php

namespace SymfonyCMS\Engine\Cms\_Core\Twig;

use BlueM\Tree;
use Doctrine\DBAL\Connection;
use MillenniumFalcon\Core\ORM\_Model;
use MillenniumFalcon\Core\SymfonyKernel\RedirectException;

use MillenniumFalcon\Core\Tree\RawData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension
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
     * Extension constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     */
    public function __construct(Connection $connection, KernelInterface $kernel, Environment $environment)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
        $this->_environment = $environment;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            'getenv' => new TwigFunction('getenv', [$this, 'getenv']),
            'redirect' => new TwigFunction('redirect', [$this, 'throwRedirectException']),
            'not_found' => new TwigFunction('not_found', [$this, 'throwNotFoundException']),
            'http_exception' => new TwigFunction('http_exception', [$this, 'throwHttpException']),
        );
    }

    /**
     * @param $arg
     * @return array|false|string
     */
    public function getenv($arg)
    {
        return $_ENV[$arg];
    }

    /**
     * @param $status
     * @param $location
     */
    public function throwRedirectException($status = Response::HTTP_FOUND, $location)
    {
        throw new RedirectException($location, $status);
    }

    /**
     * @param $message
     */
    public function throwNotFoundException($message = '')
    {
        throw new NotFoundHttpException($message);
    }

    /**
     * @param $status
     * @param $message
     */
    public function throwHttpException($status = Response::HTTP_INTERNAL_SERVER_ERROR, $message)
    {
        throw new HttpException($status, $message);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'json_decode' => new TwigFilter('json_decode', array($this, 'json_decode')),
            'highlight' => new TwigFilter('highlight', array($this, 'highlight')),
            'sections' => new TwigFilter('sections', [$this, 'sections'], ['needs_environment' => true, 'needs_context' => true]),
            'section' => new TwigFilter('section', [$this, 'section'], ['needs_environment' => true, 'needs_context' => true]),
            'block' => new TwigFilter('block', [$this, 'block'], ['needs_environment' => true, 'needs_context' => true]),
        );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function json_decode($value)
    {
        return json_decode($value);
    }

    /**
     * @param $haystack
     * @param $needle
     * @return mixed|string|string[]
     */
    public function highlight($haystack, $needle)
    {
        // return $haystack if there is no highlight color or strings given, nothing to do.
        if (strlen($haystack) < 1 || strlen($needle) < 1) {
            return $haystack;
        }
        $needle = preg_quote($needle);
        preg_match_all("/$needle+/i", $haystack, $matches);
        if (is_array($matches[0]) && count($matches[0]) >= 1) {
            foreach (array_unique($matches[0]) as $match) {
                $haystack = str_replace($match, '<span style="background-color:lightyellow;">' . $match . '</span>', $haystack);
            }
        }
        return $haystack;
    }

    /**
     * @param $block
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function block(Environment $env, $context, $block)
    {
        if (!isset($block->status) || !$block->status || $block->status == 0) {
            return '';
        }
        if (file_exists(__DIR__ . "/../../../../../../../templates/fragments/{$block->twig}")) {
            return $this->_environment->render("fragments/{$block->twig}", array_merge($context, array_merge((array)$block->values, [
                '__block' => $block,
            ])));
        }
        return '';
    }

    /**
     * @param $section
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function section(Environment $env, $context, $section)
    {
        if (!isset($section->status) || !$section->status || $section->status == 0) {
            return '';
        }
        $html = '';
        foreach ($section->blocks as $block) {
            $html .= $this->block($env, $context, $block);
        }
        return $html;
    }

    /**
     * @param $sections
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sections(Environment $env, $context, $sections)
    {
        if (!$sections) {
            $sections = [];
        }

        if (gettype($sections) == 'string') {
            $sections = json_decode($sections);
        }

        $html = '';
        foreach ($sections as $section) {
            $html .= $this->section($env, $context, $section);
        }
        return $html;
    }
}