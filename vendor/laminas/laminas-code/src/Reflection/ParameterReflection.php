<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use ReflectionParameter;

use function method_exists;

class ParameterReflection extends ReflectionParameter implements ReflectionInterface
{
    /**
     * @var bool
     */
    protected $isFromMethod = false;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $phpReflection  = parent::getDeclaringClass();
        $laminasReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get class reflection object
     *
     * @return null|ClassReflection
     */
    public function getClass()
    {
        $phpReflectionType = parent::getType();
        if ($phpReflectionType === null) {
            return null;
        }

        $laminasReflection = new ClassReflection($phpReflectionType->getName());
        unset($phpReflectionType);

        return $laminasReflection;
    }

    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    public function getDeclaringFunction()
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof \ReflectionMethod) {
            $laminasReflection = new MethodReflection($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $laminasReflection = new FunctionReflection($phpReflection->getName());
        }
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get parameter type
     *
     * @return string|null
     */
    public function detectType()
    {
        if (method_exists($this, 'getType')
            && null !== ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (null !== $type && $type->getName() === 'self') {
            return $this->getDeclaringClass()->getName();
        }

        if (($class = $this->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->getDeclaringFunction()->getDocBlock();

        if (! $docBlock instanceof DocBlockReflection) {
            return null;
        }

        $params = $docBlock->getTags('param');

        if (isset($params[$this->getPosition()])) {
            return $params[$this->getPosition()]->getType();
        }

        return null;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return parent::__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}
