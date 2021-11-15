<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ReflectionClass;
use ReflectionParameter;

use function array_filter;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function implode;
use function sort;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strrpos;
use function substr;

class FactoryCreator
{
    const FACTORY_TEMPLATE = <<<'EOT'
        <?php
        
        declare(strict_types=1);
        
        namespace %s;
        
        %s
        
        class %sFactory implements FactoryInterface
        {
            /**
             * @param ContainerInterface $container
             * @param string $requestedName
             * @param null|array $options
             * @return %s
             */
            public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
            {
                return new %s(%s);
            }
        }
        
        EOT;

    private const IMPORT_ALWAYS = [
        FactoryInterface::class,
        ContainerInterface::class,
    ];

    /**
     * @param string $className
     * @return string
     */
    public function createFactory($className)
    {
        $class = $this->getClassName($className);

        return sprintf(
            self::FACTORY_TEMPLATE,
            str_replace('\\' . $class, '', $className),
            $this->createImportStatements($className),
            $class,
            $class,
            $class,
            $this->createArgumentString($className)
        );
    }

    /**
     * @param $className
     * @return string
     */
    private function getClassName($className)
    {
        $class = substr($className, strrpos($className, '\\') + 1);
        return $class;
    }

    /**
     * @param string $className
     * @return array
     */
    private function getConstructorParameters($className)
    {
        $reflectionClass = new ReflectionClass($className);

        if (! $reflectionClass || ! $reflectionClass->getConstructor()) {
            return [];
        }

        $constructorParameters = $reflectionClass->getConstructor()->getParameters();

        if (empty($constructorParameters)) {
            return [];
        }

        $constructorParameters = array_filter(
            $constructorParameters,
            function (ReflectionParameter $argument): bool {
                if ($argument->isOptional()) {
                    return false;
                }

                $type = $argument->getType();
                $class = null !== $type && ! $type->isBuiltin() ? $type->getName() : null;

                if (null === $class) {
                    throw new InvalidArgumentException(sprintf(
                        'Cannot identify type for constructor argument "%s"; '
                        . 'no type hint, or non-class/interface type hint',
                        $argument->getName()
                    ));
                }

                return true;
            }
        );

        if (empty($constructorParameters)) {
            return [];
        }

        return array_map(function (ReflectionParameter $parameter): ?string {
            $type = $parameter->getType();
            return null !== $type && ! $type->isBuiltin() ? $type->getName() : null;
        }, $constructorParameters);
    }

    /**
     * @param string $className
     * @return string
     */
    private function createArgumentString($className)
    {
        $arguments = array_map(function (string $dependency): string {
            return sprintf('$container->get(\\%s::class)', $dependency);
        }, $this->getConstructorParameters($className));

        switch (count($arguments)) {
            case 0:
                return '';
            case 1:
                return array_shift($arguments);
            default:
                $argumentPad = str_repeat(' ', 12);
                $closePad = str_repeat(' ', 8);
                return sprintf(
                    "\n%s%s\n%s",
                    $argumentPad,
                    implode(",\n" . $argumentPad, $arguments),
                    $closePad
                );
        }
    }

    private function createImportStatements(string $className): string
    {
        $imports = array_merge(self::IMPORT_ALWAYS, [$className]);
        sort($imports);
        return implode("\n", array_map(function (string $import): string {
            return sprintf('use %s;', $import);
        }, $imports));
    }
}
