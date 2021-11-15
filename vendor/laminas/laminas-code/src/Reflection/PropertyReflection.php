<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Scanner\AnnotationScanner;
use Laminas\Code\Scanner\CachingFileScanner;
use ReflectionProperty as PhpReflectionProperty;

/**
 * @todo       implement line numbers
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface
{
    /**
     * @var AnnotationScanner
     */
    protected $annotations;

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
     * Get DocBlock comment
     *
     * @return string|false False if no DocBlock defined
     */
    public function getDocComment()
    {
        return parent::getDocComment();
    }

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock()
    {
        if (! ($docComment = $this->getDocComment())) {
            return false;
        }

        $docBlockReflection = new DocBlockReflection($docComment);

        return $docBlockReflection;
    }

    /**
     * @param  AnnotationManager $annotationManager
     * @return AnnotationScanner|false
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        if (null !== $this->annotations) {
            return $this->annotations;
        }

        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        $class              = $this->getDeclaringClass();
        $cachingFileScanner = $this->createFileScanner($class->getFileName());
        $nameInformation    = $cachingFileScanner->getClassNameInformation($class->getName());

        if (! $nameInformation) {
            return false;
        }

        $this->annotations  = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Creates a new FileScanner instance.
     *
     * By having this as a separate method it allows the method to be overridden
     * if a different FileScanner is needed.
     *
     * @param  string $filename
     *
     * @return CachingFileScanner
     */
    protected function createFileScanner($filename)
    {
        return new CachingFileScanner($filename);
    }
}
