<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Address;

interface AddressInterface
{
    /**
     * Retrieve email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName();

    /**
     * String representation of address
     *
     * @return string
     */
    public function toString();
}
