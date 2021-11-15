<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

use function explode;
use function implode;
use function preg_match;

class ThrowsTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getName()
    {
        return 'throws';
    }

    /**
     * @param  string $tagDocBlockLine
     * @return void
     */
    public function initialize($tagDocBlockLine)
    {
        $matches = [];
        preg_match('#([\w|\\\]+)(?:\s+(.*))?#', $tagDocBlockLine, $matches);

        $this->types = explode('|', $matches[1]);

        if (isset($matches[2])) {
            $this->description = $matches[2];
        }
    }

    /**
     * Get return variable type
     *
     * @return string
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getType()
    {
        return implode('|', $this->getTypes());
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
