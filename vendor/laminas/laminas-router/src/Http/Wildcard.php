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
 * Wildcard route.
 *
 * @deprecated since version 2.3.
 * Misuse of this route type can lead to potential security issues.
 * Use the `Segment` route type instead.
 */
class Wildcard implements RouteInterface
{
    /**
     * Delimiter between keys and values.
     *
     * @var string
     */
    protected $keyValueDelimiter;

    /**
     * Delimiter before parameters.
     *
     * @var array
     */
    protected $paramDelimiter;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = [];

    /**
     * Create a new wildcard route.
     *
     * @param  string $keyValueDelimiter
     * @param  string $paramDelimiter
     * @param  array  $defaults
     */
    public function __construct($keyValueDelimiter = '/', $paramDelimiter = '/', array $defaults = [])
    {
        $this->keyValueDelimiter = $keyValueDelimiter;
        $this->paramDelimiter    = $paramDelimiter;
        $this->defaults          = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Wildcard
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

        if (! isset($options['key_value_delimiter'])) {
            $options['key_value_delimiter'] = '/';
        }

        if (! isset($options['param_delimiter'])) {
            $options['param_delimiter'] = '/';
        }

        if (! isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['key_value_delimiter'], $options['param_delimiter'], $options['defaults']);
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
        $path = $uri->getPath() ?: '';

        if ($path === '/') {
            $path = '';
        }

        if ($pathOffset !== null) {
            $path = substr($path, $pathOffset) ?: '';
        }

        $matches = [];
        $params  = explode($this->paramDelimiter, $path);

        if (count($params) > 1 && ($params[0] !== '' || end($params) === '')) {
            return;
        }

        if ($this->keyValueDelimiter === $this->paramDelimiter) {
            $count = count($params);

            for ($i = 1; $i < $count; $i += 2) {
                if (isset($params[$i + 1])) {
                    $matches[rawurldecode($params[$i])] = rawurldecode($params[$i + 1]);
                }
            }
        } else {
            array_shift($params);

            foreach ($params as $param) {
                $param = explode($this->keyValueDelimiter, $param, 2);

                if (isset($param[1])) {
                    $matches[rawurldecode($param[0])] = rawurldecode($param[1]);
                }
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), strlen($path));
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
        $elements              = [];
        $mergedParams          = array_merge($this->defaults, $params);
        $this->assembledParams = [];

        if ($mergedParams) {
            foreach ($mergedParams as $key => $value) {
                if (! is_scalar($value)) {
                    continue;
                }

                $elements[] = rawurlencode($key) . $this->keyValueDelimiter . rawurlencode((string) $value);
                $this->assembledParams[] = $key;
            }

            return $this->paramDelimiter . implode($this->paramDelimiter, $elements);
        }

        return '';
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
