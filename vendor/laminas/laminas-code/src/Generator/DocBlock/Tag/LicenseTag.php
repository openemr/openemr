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

class LicenseTag extends AbstractGenerator implements TagInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $licenseName;

    /**
     * @param string $url
     * @param string $licenseName
     */
    public function __construct($url = null, $licenseName = null)
    {
        if (! empty($url)) {
            $this->setUrl($url);
        }

        if (! empty($licenseName)) {
            $this->setLicenseName($licenseName);
        }
    }

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
        return 'license';
    }

    /**
     * @param string $url
     * @return LicenseTag
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param  string $name
     * @return LicenseTag
     */
    public function setLicenseName($name)
    {
        $this->licenseName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@license'
            . (! empty($this->url) ? ' ' . $this->url : '')
            . (! empty($this->licenseName) ? ' ' . $this->licenseName : '');

        return $output;
    }
}
