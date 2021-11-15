<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Soap\AutoDiscover\DiscoveryStrategy;

use Laminas\Server\Reflection\AbstractFunction;
use Laminas\Server\Reflection\Prototype;
use Laminas\Server\Reflection\ReflectionParameter;

/**
 * Describes how types, return values and method details are detected during
 * AutoDiscovery of a WSDL.
 */
class ReflectionDiscovery implements DiscoveryStrategyInterface
{
    /**
     * Returns description from phpdoc block
     *
     * @param  AbstractFunction $function
     * @return string
     */
    public function getFunctionDocumentation(AbstractFunction $function)
    {
        return $function->getDescription();
    }

    /**
     * Return parameter type
     *
     * @param  ReflectionParameter $param
     * @return string
     */
    public function getFunctionParameterType(ReflectionParameter $param)
    {
        return $param->getType();
    }

    /**
     * Return function return type
     *
     * @param  AbstractFunction $function
     * @param  Prototype        $prototype
     * @return string
     */
    public function getFunctionReturnType(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType();
    }

    /**
     * Return true if function is one way (return nothing)
     *
     * @param  AbstractFunction $function
     * @param  Prototype        $prototype
     * @return bool
     */
    public function isFunctionOneWay(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType() == 'void';
    }
}
