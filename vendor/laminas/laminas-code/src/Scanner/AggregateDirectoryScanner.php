<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Scanner;

use Laminas\Code\Exception;

class AggregateDirectoryScanner extends DirectoryScanner
{
    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @param  bool $returnScannerClass
     * @todo not implemented
     */
    public function getNamespaces($returnScannerClass = false)
    {
        // @todo
    }

    public function getIncludes($returnScannerClass = false)
    {
    }

    public function getClasses($returnScannerClass = false, $returnDerivedScannerClass = false)
    {
        $classes = [];
        foreach ($this->directories as $scanner) {
            $classes += $scanner->getClasses();
        }
        if ($returnScannerClass) {
            foreach ($classes as $index => $class) {
                $classes[$index] = $this->getClass($class, $returnScannerClass, $returnDerivedScannerClass);
            }
        }

        return $classes;
    }

    /**
     * @param  string $class
     * @return bool
     */
    public function hasClass($class)
    {
        foreach ($this->directories as $scanner) {
            if ($scanner->hasClass($class)) {
                break;
            } else {
                unset($scanner);
            }
        }

        return isset($scanner);
    }

    /**
     * @param  string $class
     * @param  bool $returnScannerClass
     * @param  bool $returnDerivedScannerClass
     * @return ClassScanner|DerivedClassScanner
     * @throws Exception\RuntimeException
     */
    public function getClass($class, $returnScannerClass = true, $returnDerivedScannerClass = false)
    {
        foreach ($this->directories as $scanner) {
            if ($scanner->hasClass($class)) {
                break;
            } else {
                unset($scanner);
            }
        }

        if (! isset($scanner)) {
            throw new Exception\RuntimeException('Class by that name was not found.');
        }

        $classScanner = $scanner->getClass($class);

        return new DerivedClassScanner($classScanner, $this);
    }

    /**
     * @param bool $returnScannerClass
     */
    public function getFunctions($returnScannerClass = false)
    {
        $this->scan();

        if (! $returnScannerClass) {
            $functions = [];
            foreach ($this->infos as $info) {
                if ($info['type'] == 'function') {
                    $functions[] = $info['name'];
                }
            }

            return $functions;
        }
        $scannerClass = new FunctionScanner();
        // @todo
    }
}
