<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

use function explode;
use function preg_match;
use function rtrim;

class MethodTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * Return value type
     *
     * @var array
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $description;

    /**
     * Is static method
     *
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'method';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = [];

        if (! preg_match('#^(static[\s]+)?(.+[\s]+)?(.+\(\))[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->isStatic = true;
        }

        if ($match[2] !== '') {
            $this->types = explode('|', rtrim($match[2]));
        }

        $this->methodName = $match[3];

        if ($match[4] !== '') {
            $this->description = $match[4];
        }
    }

    /**
     * Get return value type
     *
     * @return null|string
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getReturnType()
    {
        if (empty($this->types)) {
            return;
        }

        return $this->types[0];
    }

    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}
