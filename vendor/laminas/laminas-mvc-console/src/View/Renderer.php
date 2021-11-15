<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use Laminas\Filter\FilterChain;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Renderer\TreeRendererInterface;
use Laminas\View\Resolver\ResolverInterface;

/**
 * Render console view models.
 */
class Renderer implements RendererInterface, TreeRendererInterface
{
    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * Constructor.
     *
     * @param array $config Configuration key-value pairs.
     */
    public function __construct(FilterChain $filterChain = null)
    {
        if ($filterChain) {
            $this->setFilterChain($filterChain);
        }
    }

    /**
     * Set the script resolver.
     *
     * No-op. Required by RendererInterface.
     *
     * @param ResolverInterface $resolver
     * @return void
     */
    public function setResolver(ResolverInterface $resolver)
    {
    }

    /**
     * Return the template engine object.
     *
     * Returns the object instance, as it is its own template engine.
     *
     * @return self
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set filter chain     o use for post-filtering script content.
     *
     * @param FilterChain $filters
     */
    public function setFilterChain(FilterChain $filters)
    {
        $this->filterChain = $filters;
    }

    /**
     * Retrieve filter chain for post-filtering script content.
     *
     * @return null|FilterChain
     */
    public function getFilterChain()
    {
        return $this->filterChain;
    }

    /**
     * Recursively processes all ViewModels and returns output.
     *
     * @param string|ModelInterface $model A ViewModel instance.
     * @param null|array|\Traversable $values Values to use when rendering. If
     *     none provided, uses those in the composed variables container.
     * @return string Console output.
     */
    public function render($model, $values = null)
    {
        $result = '';

        if (! $model instanceof ModelInterface) {
            // View model is required by this renderer
            return $result;
        }

        // If option keys match setters, pass values to those methods.
        foreach ($model->getOptions() as $setting => $value) {
            $method = 'set' . $setting;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        // Render children first
        if ($model->hasChildren()) {
            // recursively render all children
            foreach ($model->getChildren() as $child) {
                $result .= $this->render($child, $values);
            }
        }

        // Render the result, if present.
        $values = $model->getVariables();

        if (isset($values['result']) && ! isset($this->filterChain)) {
            // append the result verbatim
            $result .= $values['result'];
        }

        if (isset($values['result']) && isset($this->filterChain)) {
            // filter and append the result
            $result .= $this->getFilterChain()->filter($values['result']);
        }

        return $result;
    }

    /**
     * @see Laminas\View\Renderer\TreeRendererInterface
     * @return bool
     */
    public function canRenderTrees()
    {
        return true;
    }
}
