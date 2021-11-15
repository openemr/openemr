<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Exception;

/**
 * View helper plugin to fetch asset from resource map.
 */
class Asset extends AbstractHelper
{
    /**
     * @var array
     */
    protected $resourceMap = [];

    /**
     * @param string $asset
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke($asset)
    {
        if (! array_key_exists($asset, $this->resourceMap)) {
            throw new Exception\InvalidArgumentException('Asset is not defined.');
        }

        return $this->resourceMap[$asset];
    }

    /**
     * @param array $resourceMap
     * @return $this
     */
    public function setResourceMap(array $resourceMap)
    {
        $this->resourceMap = $resourceMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getResourceMap()
    {
        return $this->resourceMap;
    }
}
