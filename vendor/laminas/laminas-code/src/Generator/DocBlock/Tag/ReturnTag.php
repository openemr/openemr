<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

class ReturnTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @param ReflectionTagInterface $reflectionTag
     * @return ReturnTag
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag)
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'return';
    }

    /**
     * @param string $datatype
     * @return ReturnTag
     * @deprecated Deprecated in 2.3. Use setTypes() instead
     */
    public function setDatatype($datatype)
    {
        return $this->setTypes($datatype);
    }

    /**
     * @return string
     * @deprecated Deprecated in 2.3. Use getTypes() or getTypesAsString() instead
     */
    public function getDatatype()
    {
        return $this->getTypesAsString();
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@return '
        . $this->getTypesAsString()
        . (! empty($this->description) ? ' ' . $this->description : '');

        return $output;
    }
}
