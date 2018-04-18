<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\View\Console\ExceptionStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleExceptionStrategyFactory implements FactoryInterface
{
    use ConsoleViewManagerConfigTrait;

    /**
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ExceptionStrategy
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $strategy = new ExceptionStrategy();
        $config   = $this->getConfig($container);

        $this->injectDisplayExceptions($strategy, $config);
        $this->injectExceptionMessage($strategy, $config);

        return $strategy;
    }

    /**
     * Create and return ExceptionStrategy instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ExceptionStrategy
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ExceptionStrategy::class);
    }

    /**
     * Inject strategy with configured display_exceptions flag.
     *
     * @param ExceptionStrategy $strategy
     * @param array $config
     */
    private function injectDisplayExceptions(ExceptionStrategy $strategy, array $config)
    {
        $flag = array_key_exists('display_exceptions', $config) ? $config['display_exceptions'] : true;
        $strategy->setDisplayExceptions($flag);
    }

    /**
     * Inject strategy with configured exception_message
     *
     * @param ExceptionStrategy $strategy
     * @param array $config
     */
    private function injectExceptionMessage(ExceptionStrategy $strategy, array $config)
    {
        if (isset($config['exception_message'])) {
            $strategy->setMessage($config['exception_message']);
        }
    }
}
