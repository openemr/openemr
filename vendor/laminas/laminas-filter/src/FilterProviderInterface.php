<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

/**
 * Implement this interface within Module classes to indicate that your module
 * provides filter configuration for the FilterPluginManager.
 */
interface FilterProviderInterface
{
    /**
     * Provide plugin manager configuration for filters.
     *
     * @return array
     */
    public function getFilterConfig();
}
