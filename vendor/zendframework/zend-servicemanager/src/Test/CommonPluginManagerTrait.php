<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Test;

use ReflectionClass;
use ReflectionProperty;
use Zend\ServiceManager\Exception\InvalidServiceException;

/**
 * Trait for testing plugin managers for v2-v3 compatibility
 *
 * To use this trait:
 *   * implement the `getPluginManager()` method to return your plugin manager
 *   * implement the `getV2InvalidPluginException()` method to return the class `validatePlugin()` throws under v2
 */
trait CommonPluginManagerTrait
{
    public function testInstanceOfMatches()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'instanceOf');
        $reflection->setAccessible(true);
        $this->assertEquals($this->getInstanceOf(), $reflection->getValue($manager), 'instanceOf does not match');
    }

    public function testShareByDefaultAndSharedByDefault()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionClass($manager);
        $shareByDefault = $sharedByDefault = true;

        foreach ($reflection->getProperties() as $prop) {
            if ($prop->getName() == 'shareByDefault') {
                $prop->setAccessible(true);
                $shareByDefault = $prop->getValue($manager);
            }
            if ($prop->getName() == 'sharedByDefault') {
                $prop->setAccessible(true);
                $sharedByDefault = $prop->getValue($manager);
            }
        }

        $this->assertTrue(
            $shareByDefault == $sharedByDefault,
            'Values of shareByDefault and sharedByDefault do not match'
        );
    }

    public function testRegisteringInvalidElementRaisesException()
    {
        $this->setExpectedException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    public function testLoadingInvalidElementRaisesException()
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', get_class($this));
        $this->setExpectedException($this->getServiceNotFoundException());
        $manager->get('test');
    }

    /**
     * @dataProvider aliasProvider
     */
    public function testPluginAliasesResolve($alias, $expected)
    {
        $this->assertInstanceOf($expected, $this->getPluginManager()->get($alias), "Alias '$alias' does not resolve'");
    }

    public function aliasProvider()
    {
        $manager = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'aliases');
        $reflection->setAccessible(true);
        $data = [];
        foreach ($reflection->getValue($manager) as $alias => $expected) {
            $data[] = [$alias, $expected];
        }
        return $data;
    }

    protected function getServiceNotFoundException()
    {
        $manager = $this->getPluginManager();
        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }
        return $this->getV2InvalidPluginException();
    }

    /**
     * Returns the plugin manager to test
     * @return \Zend\ServiceManager\AbstractPluginManager
     */
    abstract protected function getPluginManager();

    /**
     * Returns the FQCN of the exception thrown under v2 by `validatePlugin()`
     * @return mixed
     */
    abstract protected function getV2InvalidPluginException();

    /**
     * Returns the value the instanceOf property has been set to
     * @return string
     */
    abstract protected function getInstanceOf();
}
