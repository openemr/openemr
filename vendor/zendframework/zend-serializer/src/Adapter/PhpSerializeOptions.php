<?php
/**
 * @see       https://github.com/zendframework/zend-serializer for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-serializer/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Serializer\Adapter;

use Zend\Json\Json as ZendJson;
use Zend\Serializer\Exception;

class PhpSerializeOptions extends AdapterOptions
{
    /**
     * The list of allowed classes for unserialization (PHP 7.0+).
     *
     * Possible values:
     *
     * - `array` of class names that are allowed to be unserialized
     * - `true` if all classes should be allowed (behavior pre-PHP 7.0)
     * - `false` if no classes should be allowed
     *
     * @var string[]|bool
     */
    protected $unserializeClassWhitelist = true;

    /**
     * @param string[]|bool $unserializeClassWhitelist
     * @return void
     */
    public function setUnserializeClassWhitelist($unserializeClassWhitelist)
    {
        if ($unserializeClassWhitelist !== true && PHP_MAJOR_VERSION < 7) {
            throw new Exception\InvalidArgumentException(
                'Class whitelist for unserialize() is only available on PHP versions 7.0 or higher.'
            );
        }

        $this->unserializeClassWhitelist = $unserializeClassWhitelist;
    }

    /**
     * @return string[]|bool
     */
    public function getUnserializeClassWhitelist()
    {
        return $this->unserializeClassWhitelist;
    }
}
