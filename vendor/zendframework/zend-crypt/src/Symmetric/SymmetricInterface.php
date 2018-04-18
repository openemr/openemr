<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

interface SymmetricInterface
{
    /**
     * @param string $data
     */
    public function encrypt($data);

    /**
     * @param string $data
     */
    public function decrypt($data);

    /**
     * @param string $key
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getKey();

    /**
     * @return integer
     */
    public function getKeySize();

    /**
     * @return string
     */
    public function getAlgorithm();

    /**
     * @param  string $algo
     */
    public function setAlgorithm($algo);

    /**
     * @return array
     */
    public function getSupportedAlgorithms();

    /**
     * @param string $salt
     */
    public function setSalt($salt);

    /**
     * @return string
     */
    public function getSalt();

    /**
     * @return integer
     */
    public function getSaltSize();

    /**
     * @return integer
     */
    public function getBlockSize();

    /**
     * @param string $mode
     */
    public function setMode($mode);

    /**
     * @return string
     */
    public function getMode();

    /**
     * @return array
     */
    public function getSupportedModes();

    /**
     * @param array $options
     */
    public function setOptions($options);
}
