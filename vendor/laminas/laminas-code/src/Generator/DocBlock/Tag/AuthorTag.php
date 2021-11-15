<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\AbstractGenerator;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

class AuthorTag extends AbstractGenerator implements TagInterface
{
    /**
     * @var string
     */
    protected $authorName;

    /**
     * @var string
     */
    protected $authorEmail;

    /**
     * @param string $authorName
     * @param string $authorEmail
     */
    public function __construct($authorName = null, $authorEmail = null)
    {
        if (! empty($authorName)) {
            $this->setAuthorName($authorName);
        }

        if (! empty($authorEmail)) {
            $this->setAuthorEmail($authorEmail);
        }
    }

    /**
     * @param ReflectionTagInterface $reflectionTag
     * @return AuthorTag
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
        return 'author';
    }

    /**
     * @param string $authorEmail
     * @return AuthorTag
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * @param string $authorName
     * @return AuthorTag
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@author'
            . (! empty($this->authorName) ? ' ' . $this->authorName : '')
            . (! empty($this->authorEmail) ? ' <' . $this->authorEmail . '>' : '');

        return $output;
    }
}
