<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

/**
 * Mark an input as able to be replaced by another when merging input filters.
 *
 */
interface ReplaceableInputInterface
{
    /**
     * @param $input
     * @param $name
     * @return self
     */
    public function replace($input, $name);
}
