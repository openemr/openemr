<?php

/**
 * @see       https://github.com/laminas/laminas-json for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json;

/**
 * Encode a string to a native JavaScript expression.
 *
 * This class simply holds a string with a native JavaScript expression,
 * so objects or arrays to be encoded with Laminas\Json\Json can contain native
 * JavaScript expressions.
 *
 * Example:
 *
 * <code>
 * $foo = array(
 *     'integer'  => 9,
 *     'string'   => 'test string',
 *     'function' => Laminas\Json\Expr(
 *         'function () { window.alert("javascript function encoded by Laminas\Json\Json") }'
 *     ),
 * );
 *
 * echo Laminas\Json\Json::encode($foo, false, ['enableJsonExprFinder' => true]);
 * </code>
 *
 * The above returns the following JSON (formatted for readability):
 *
 * <code>
 * {
 *   "integer": 9,
 *   "string": "test string",
 *   "function": function () {window.alert("javascript function encoded by Laminas\Json\Json")}
 * }
 * </code>
 */
class Expr
{
    /**
     * Storage for javascript expression.
     *
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression The expression to represent.
     */
    public function __construct($expression)
    {
        $this->expression = (string) $expression;
    }

    /**
     * Cast to string
     *
     * @return string holded javascript expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
