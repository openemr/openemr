<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface as Request;
use Traversable;

/**
 * Literal route.
 */
class Literal implements RouteInterface
{
    /**
     * RouteInterface to match.
     *
     * @var string
     */
    protected $route;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Create a new literal route.
     *
     * @param  string $route
     * @param  array  $defaults
     */
    public function __construct($route, array $defaults = [])
    {
        $this->route    = $route;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Literal
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of options',
                __METHOD__
            ));
        }

        if (! isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (! isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['route'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::match()
     * @param  Request      $request
     * @param  integer|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (! method_exists($request, 'getUri')) {
            return;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            if ($pathOffset >= 0 && strlen((string) $path) >= $pathOffset && ! empty($this->route)) {
                if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
                    return new RouteMatch($this->defaults, strlen($this->route));
                }
            }

            return;
        }

        if ($path === $this->route) {
            return new RouteMatch($this->defaults, strlen($this->route));
        }

        return;
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = [])
    {
        return $this->route;
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return [];
    }
}
