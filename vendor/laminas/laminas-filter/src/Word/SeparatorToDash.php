<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Word;

class SeparatorToDash extends SeparatorToSeparator
{
    /**
     * Constructor
     *
     * @param string $searchSeparator Separator to search for change
     */
    public function __construct($searchSeparator = ' ')
    {
        parent::__construct($searchSeparator, '-');
    }
}
