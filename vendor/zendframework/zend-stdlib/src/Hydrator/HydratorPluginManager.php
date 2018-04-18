<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator;

use Zend\Hydrator\HydratorPluginManager as BaseHydratorPluginManager;

/**
 * Plugin manager implementation for hydrators.
 *
 * Enforces that adapters retrieved are instances of HydratorInterface
 *
 * @deprecated Use Zend\Hydrator\HydratorPluginManager from zendframework/zend-hydrator instead.
 */
class HydratorPluginManager extends BaseHydratorPluginManager
{
    /**
     * Default aliases
     *
     * @var array
     */
    protected $aliases = [
        'delegatinghydrator' => 'Zend\Stdlib\Hydrator\DelegatingHydrator',
    ];

    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = [
        'arrayserializable' => 'Zend\Stdlib\Hydrator\ArraySerializable',
        'classmethods'      => 'Zend\Stdlib\Hydrator\ClassMethods',
        'objectproperty'    => 'Zend\Stdlib\Hydrator\ObjectProperty',
        'reflection'        => 'Zend\Stdlib\Hydrator\Reflection'
    ];

    /**
     * Default factory-based adapters
     *
     * @var array
     */
    protected $factories = [
        'Zend\Stdlib\Hydrator\DelegatingHydrator' => 'Zend\Stdlib\Hydrator\DelegatingHydratorFactory',
        'zendstdlibhydratordelegatinghydrator'    => 'Zend\Stdlib\Hydrator\DelegatingHydratorFactory',
    ];
}
