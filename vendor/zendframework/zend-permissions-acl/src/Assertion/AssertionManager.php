<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Permissions\Acl\Assertion;

use Zend\Permissions\Acl\Exception\InvalidArgumentException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;

class AssertionManager extends AbstractPluginManager
{
    /**
     * zend-servicemanager v3 compatibility
     * @var bool
     */
    protected $shareByDefault = true;

    /**
     * zend-servicemanager v2 compatibility
     * @var bool
     */
    protected $sharedByDefault = true;

    protected $instanceOf = AssertionInterface::class;

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($instance) ? get_class($instance) : gettype($instance))
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $instance
     * @throws InvalidArgumentException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
