<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer;

use Zend\Feed\Writer\Exception\InvalidArgumentException;

class StandaloneExtensionManager implements ExtensionManagerInterface
{
    private $extensions = [
        'Atom\Renderer\Feed'           => Extension\Atom\Renderer\Feed::class,
        'Content\Renderer\Entry'       => Extension\Content\Renderer\Entry::class,
        'DublinCore\Renderer\Entry'    => Extension\DublinCore\Renderer\Entry::class,
        'DublinCore\Renderer\Feed'     => Extension\DublinCore\Renderer\Feed::class,
        'GooglePlayPodcast\Entry'          => Extension\GooglePlayPodcast\Entry::class,
        'GooglePlayPodcast\Feed'           => Extension\GooglePlayPodcast\Feed::class,
        'GooglePlayPodcast\Renderer\Entry' => Extension\GooglePlayPodcast\Renderer\Entry::class,
        'GooglePlayPodcast\Renderer\Feed'  => Extension\GooglePlayPodcast\Renderer\Feed::class,
        'ITunes\Entry'                 => Extension\ITunes\Entry::class,
        'ITunes\Feed'                  => Extension\ITunes\Feed::class,
        'ITunes\Renderer\Entry'        => Extension\ITunes\Renderer\Entry::class,
        'ITunes\Renderer\Feed'         => Extension\ITunes\Renderer\Feed::class,
        'Slash\Renderer\Entry'         => Extension\Slash\Renderer\Entry::class,
        'Threading\Renderer\Entry'     => Extension\Threading\Renderer\Entry::class,
        'WellFormedWeb\Renderer\Entry' => Extension\WellFormedWeb\Renderer\Entry::class,
    ];

    /**
     * Do we have the extension?
     *
     * @param  string $extension
     * @return bool
     */
    public function has($extension)
    {
        return array_key_exists($extension, $this->extensions);
    }

    /**
     * Retrieve the extension
     *
     * @param  string $extension
     * @return mixed
     */
    public function get($extension)
    {
        $class = $this->extensions[$extension];
        return new $class();
    }

    /**
     * Add an extension.
     *
     * @param string $name
     * @param string $class
     */
    public function add($name, $class)
    {
        if (is_string($class)
            && ((
                is_a($class, Extension\AbstractRenderer::class, true)
                || 'Feed' === substr($class, -4)
                || 'Entry' === substr($class, -5)
            ))
        ) {
            $this->extensions[$name] = $class;

            return;
        }

        throw new InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Extension\RendererInterface '
            . 'or the classname must end in "Feed" or "Entry"',
            $class,
            __NAMESPACE__
        ));
    }

    /**
     * Remove an extension.
     *
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->extensions[$name]);
    }
}
