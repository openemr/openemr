<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SmtpPluginManagerFactory implements FactoryInterface
{
    /**
     * zend-servicemanager v2 support for invocation options.
     *
     * @param array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return SmtpPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new SmtpPluginManager($container, $options ?: []);
    }

    /**
     * {@inheritDoc}
     *
     * @return SmtpPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: SmtpPluginManager::class, $this->creationOptions);
    }

    /**
     * zend-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
