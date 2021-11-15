<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

use function ltrim;

class ParamTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string
     */
    protected $variableName;

    /**
     * @param string $variableName
     * @param array $types
     * @param string $description
     */
    public function __construct($variableName = null, $types = [], $description = null)
    {
        if (! empty($variableName)) {
            $this->setVariableName($variableName);
        }

        parent::__construct($types, $description);
    }

    /**
     * @param ReflectionTagInterface $reflectionTag
     * @return ParamTag
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
        return 'param';
    }

    /**
     * @param string $variableName
     * @return ParamTag
     */
    public function setVariableName($variableName)
    {
        $this->variableName = ltrim($variableName, '$');
        return $this;
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @param string $datatype
     * @return ParamTag
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
     * @param  string $paramName
     * @return ParamTag
     * @deprecated Deprecated in 2.3. Use setVariableName() instead
     */
    public function setParamName($paramName)
    {
        return $this->setVariableName($paramName);
    }

    /**
     * @return string
     * @deprecated Deprecated in 2.3. Use getVariableName() instead
     */
    public function getParamName()
    {
        return $this->getVariableName();
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@param'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->variableName) ? ' $' . $this->variableName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');

        return $output;
    }
}
