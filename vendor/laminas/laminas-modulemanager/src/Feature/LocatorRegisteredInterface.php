<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Feature;

/**
 * LocatorRegistered
 *
 * By implementing this interface in a Module class, the instance of the Module
 * class will be automatically injected into any DI-configured object which has
 * a constructor or setter parameter which is type hinted with the Module class
 * name. Implementing this interface obviously does not require adding any
 * methods to your class.
 */
interface LocatorRegisteredInterface
{
}
