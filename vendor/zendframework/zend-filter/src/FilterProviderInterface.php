<?php
/**
 * @see       https://github.com/zendframework/zend-filter for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-filter/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Filter;

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
