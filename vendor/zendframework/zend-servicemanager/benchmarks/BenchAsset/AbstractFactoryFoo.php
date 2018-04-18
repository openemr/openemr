<?php
namespace ZendBench\ServiceManager\BenchAsset;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractFactoryFoo implements AbstractFactoryInterface
{
  public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
  {
      if ($name != 'foo') {
          return false;
      }
      return true;
  }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new Foo();
    }
}
