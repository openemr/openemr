<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
require_once("verysimple/Phreeze/IRenderEngine.php");
require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

/**
 * TwigRenderEngine is an implementation of IRenderEngine that uses
 * the Twig template engine to render views
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2011 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class TwigRenderEngine implements IRenderEngine
{
    /** @var Twig_Environment */
    public $twig;

    /** @var Twig_Loader_Filesystem */
    private $loader;
    private $assignments =  [];

    /**
     *
     * @param string $templatePath
     * @param string $compilePath
     */
    function __construct($templatePath = '', $compilePath = '')
    {
        $this->loader = new Twig_Loader_Filesystem($templatePath);
        $this->twig = new Twig_Environment($this->loader, [
                'cache' => $compilePath
        ]);
    }

    /**
     *
     * @see IRenderEngine::assign()
     */
    function assign($key, $value)
    {
        return $this->assignments [$key] = $value;
    }

    /**
     *
     * @see IRenderEngine::display()
     */
    function display($template)
    {
        if (!str_contains('.', $template)) {
            $template .= '.html';
        }

        return $this->twig->display($template, $this->assignments);
    }

    /**
     *
     * @see IRenderEngine::fetch()
     */
    function fetch($template)
    {
        if (!str_contains('.', $template)) {
            $template .= '.html';
        }

        return $this->twig->render($template, $this->assignments);
    }

    /**
     *
     * @see IRenderEngine::clear()
     */
    function clear($key)
    {
        unset($this->assignments [$key]);
    }

    /**
     *
     * @see IRenderEngine::clearAll()
     */
    function clearAll()
    {
        $this->assignments =  [];
    }

    /**
     *
     * @see IRenderEngine::getAll()
     */
    function getAll()
    {
        return $this->assignments;
    }
}
