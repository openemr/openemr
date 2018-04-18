<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt;

use Interop\Container\ContainerInterface;

/**
 * Plugin manager implementation for the symmetric adapter instances.
 *
 * Enforces that symmetric adapters retrieved are instances of
 * Symmetric\SymmetricInterface. Additionally, it registers a number of default
 * symmetric adapters available.
 */
class SymmetricPluginManager implements ContainerInterface
{
    /**
     * Default set of symmetric adapters
     *
     * @var array
     */
    protected $symmetric = [
        'mcrypt' => Symmetric\Mcrypt::class,
    ];

    /**
     * Do we have the symmetric plugin?
     *
     * @param  string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->symmetric);
    }

    /**
     * Retrieve the symmetric plugin
     *
     * @param  string $id
     * @return Symmetric\SymmetricInterface
     */
    public function get($id)
    {
        $class = $this->symmetric[$id];
        return new $class();
    }
}
