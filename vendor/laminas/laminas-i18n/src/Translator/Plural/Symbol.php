<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Translator\Plural;

use Closure;
use Laminas\I18n\Exception;

/**
 * Parser symbol.
 *
 * All properties in the symbol are defined as public for easier and faster
 * access from the applied closures. An exception are the closure properties
 * themselves, as they have to be accessed via the appropriate getter and
 * setter methods.
 */
class Symbol
{
    /**
     * Parser instance.
     *
     * @var Parser
     */
    public $parser;

    /**
     * Node or token type name.
     *
     * @var string
     */
    public $id;

    /**
     * Left binding power (precedence).
     *
     * @var int
     */
    public $leftBindingPower;

    /**
     * Getter for null denotation.
     *
     * @var callable
     */
    protected $nullDenotationGetter;

    /**
     * Getter for left denotation.
     *
     * @var callable
     */
    protected $leftDenotationGetter;

    /**
     * Value used by literals.
     *
     * @var mixed
     */
    public $value;

    /**
     * First node value.
     *
     * @var Symbol
     */
    public $first;

    /**
     * Second node value.
     *
     * @var Symbol
     */
    public $second;

    /**
     * Third node value.
     *
     * @var Symbol
     */
    public $third;

    /**
     * Create a new symbol.
     *
     * @param  Parser  $parser
     * @param  string  $id
     * @param  int $leftBindingPower
     */
    public function __construct(Parser $parser, $id, $leftBindingPower)
    {
        $this->parser               = $parser;
        $this->id                   = $id;
        $this->leftBindingPower     = $leftBindingPower;
    }

    /**
     * Set the null denotation getter.
     *
     * @param  Closure $getter
     * @return $this
     */
    public function setNullDenotationGetter(Closure $getter)
    {
        $this->nullDenotationGetter = $getter;
        return $this;
    }

    /**
     * Set the left denotation getter.
     *
     * @param  Closure $getter
     * @return $this
     */
    public function setLeftDenotationGetter(Closure $getter)
    {
        $this->leftDenotationGetter = $getter;
        return $this;
    }

    /**
     * Get null denotation.
     *
     * @throws Exception\ParseException
     * @return Symbol
     */
    public function getNullDenotation()
    {
        if ($this->nullDenotationGetter === null) {
            throw new Exception\ParseException(sprintf('Syntax error: %s', $this->id));
        }

        /** @var callable $function  */
        $function = $this->nullDenotationGetter;
        return $function($this);
    }

    /**
     * Get left denotation.
     *
     * @param  Symbol $left
     * @throws Exception\ParseException
     * @return Symbol
     */
    public function getLeftDenotation($left)
    {
        if ($this->leftDenotationGetter === null) {
            throw new Exception\ParseException(sprintf('Unknown operator: %s', $this->id));
        }

        /** @var callable $function  */
        $function = $this->leftDenotationGetter;
        return $function($this, $left);
    }
}
