<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

/**
 * Ensures Inputs store unfiltered data and are capable of returning it
 */
interface UnfilteredDataInterface
{
    /**
     * @return array|object
     */
    public function getUnfilteredData();

    /**
     * @param array|object $data
     * @return $this
     */
    public function setUnfilteredData($data);
}
