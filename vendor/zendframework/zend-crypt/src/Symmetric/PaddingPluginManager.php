<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

use Interop\Container\ContainerInterface;

/**
 * Plugin manager implementation for the padding adapter instances.
 *
 * Enforces that padding adapters retrieved are instances of
 * Padding\PaddingInterface. Additionally, it registers a number of default
 * padding adapters available.
 */
class PaddingPluginManager implements ContainerInterface
{
    private $paddings = [
        'pkcs7'     => Padding\Pkcs7::class,
        'nopadding' => Padding\NoPadding::class,
        'null'      => Padding\NoPadding::class,
    ];

    /**
     * Do we have the padding plugin?
     *
     * @param  string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->paddings);
    }

    /**
     * Retrieve the padding plugin
     *
     * @param  string $id
     * @return Padding\PaddingInterface
     */
    public function get($id)
    {
        $class = $this->paddings[$id];
        return new $class();
    }
}
