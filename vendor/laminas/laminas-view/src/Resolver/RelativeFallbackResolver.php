<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Resolver;

use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\RendererInterface;

/**
 * Relative fallback resolver - resolves to view templates in a sub-path of the
 * currently set view model's template (if the current renderer has the `view_model` plugin set).
 *
 * This allows for usage of partial template paths such as `some/partial`, resolving to
 * `my/module/script/path/some/partial.phtml`, while rendering template `my/module/script/path/my-view`
 */
class RelativeFallbackResolver implements ResolverInterface
{
    const NS_SEPARATOR = '/';

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        $plugin = [$renderer, 'plugin'];

        if (! is_callable($plugin)) {
            return false;
        }

        $helper = call_user_func($plugin, 'view_model');

        if (! $helper instanceof ViewModelHelper) {
            return false;
        }

        $currentModel = $helper->getCurrent();

        if (! $currentModel instanceof ModelInterface) {
            return false;
        }

        $currentTemplate = $currentModel->getTemplate();
        $position        = strrpos($currentTemplate, self::NS_SEPARATOR);

        if (! $position) {
            return false;
        }

        return $this->resolver->resolve(substr($currentTemplate, 0, $position) . self::NS_SEPARATOR . $name, $renderer);
    }
}
