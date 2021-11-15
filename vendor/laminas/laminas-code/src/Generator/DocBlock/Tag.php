<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock;

use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

/**
 * @deprecated Deprecated in 2.3. Use GenericTag instead
 */
class Tag extends GenericTag
{
    /**
     * @param  ReflectionTagInterface $reflectionTag
     * @return Tag
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag)
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    /**
     * @param  string $description
     * @return Tag
     * @deprecated Deprecated in 2.3. Use GenericTag::setContent() instead
     */
    public function setDescription($description)
    {
        return $this->setContent($description);
    }

    /**
     * @return string
     * @deprecated Deprecated in 2.3. Use GenericTag::getContent() instead
     */
    public function getDescription()
    {
        return $this->getContent();
    }
}
