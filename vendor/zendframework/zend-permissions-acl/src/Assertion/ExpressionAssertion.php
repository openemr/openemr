<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-acl for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-acl/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Permissions\Acl\Assertion;

use ReflectionProperty;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\Exception\InvalidAssertionException;
use Zend\Permissions\Acl\Exception\RuntimeException;

/**
 * Create an assertion based on expression rules.
 *
 * Each of the constructor, fromProperties, and fromArray methods allow you to
 * define expression rules, and these include the left hand side, operator, and
 * right hand side of the expression.
 *
 * The left and right hand sides of the expression are the values to compare.
 * These values can be either an exact value to match, or an array with the key
 * OPERAND_CONTEXT_PROPERTY pointing to one of two value types.
 *
 * First, it can be a string value matching one of "acl", "privilege", "role",
 * or "resource", with the latter two values being the most common. In those
 * cases, the matching value passed to `assert()` will be used in the
 * comparison.
 *
 * Second, it can be a dot-separated property path string of the format
 * "object.property", representing the associated object (role, resource, acl,
 * or privilege) and its property to test against.  The property may refer to a
 * public property, or a public `get` or `is` method (following
 * canonicalization of the property by replacing underscore separated values
 * with camelCase values).
 */
final class ExpressionAssertion implements AssertionInterface
{
    const OPERAND_CONTEXT_PROPERTY = '__context';

    const OPERATOR_EQ     = '=';
    const OPERATOR_NEQ    = '!=';
    const OPERATOR_LT     = '<';
    const OPERATOR_LTE    = '<=';
    const OPERATOR_GT     = '>';
    const OPERATOR_GTE    = '>=';
    const OPERATOR_IN     = 'in';
    const OPERATOR_NIN    = '!in';
    const OPERATOR_REGEX  = 'regex';
    const OPERATOR_NREGEX = '!regex';
    const OPERATOR_SAME   = '===';
    const OPERATOR_NSAME  = '!==';

    /**
     * @var array
     */
    private static $validOperators = [
        self::OPERATOR_EQ,
        self::OPERATOR_NEQ,
        self::OPERATOR_LT,
        self::OPERATOR_LTE,
        self::OPERATOR_GT,
        self::OPERATOR_GTE,
        self::OPERATOR_IN,
        self::OPERATOR_NIN,
        self::OPERATOR_REGEX,
        self::OPERATOR_NREGEX,
        self::OPERATOR_SAME,
        self::OPERATOR_NSAME,
    ];

    /**
     * @var mixed
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var mixed
     */
    private $right;

    /**
     * Constructor
     *
     * Note that the constructor is marked private; use `fromProperties()` or
     * `fromArray()` to create an instance.
     *
     * @param mixed|array $left See the class description for valid values.
     * @param string $operator One of the OPERATOR constants (or their values)
     * @param mixed|array $right See the class description for valid values.
     */
    private function __construct($left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * @param mixed|array $left See the class description for valid values.
     * @param string $operator One of the OPERATOR constants (or their values)
     * @param mixed|array $right See the class description for valid values.
     * @return self
     * @throws InvalidAssertionException if either operand is invalid.
     * @throws InvalidAssertionException if the operator is not supported.
     */
    public static function fromProperties($left, $operator, $right)
    {
        $operator = strtolower($operator);

        self::validateOperand($left);
        self::validateOperator($operator);
        self::validateOperand($right);

        return new self($left, $operator, $right);
    }

    /**
     * @param array $expression Must contain the following keys:
     *     - left: the left-hand side of the expression
     *     - operator: the operator to use for the comparison
     *     - right: the right-hand side of the expression
     *     See the class description for valid values for the left and right
     *     hand side values.
     * @return self
     * @throws InvalidAssertionException if missing one of the required keys.
     * @throws InvalidAssertionException if either operand is invalid.
     * @throws InvalidAssertionException if the operator is not supported.
     */
    public static function fromArray(array $expression)
    {
        $required = ['left', 'operator', 'right'];

        if (count(array_intersect_key($expression, array_flip($required))) < count($required)) {
            throw new InvalidAssertionException(
                "Expression assertion requires 'left', 'operator' and 'right' to be supplied"
            );
        }

        return self::fromProperties(
            $expression['left'],
            $expression['operator'],
            $expression['right']
        );
    }

    /**
     * @param mixed|array $operand
     * @throws InvalidAssertionException if the operand is invalid.
     */
    private static function validateOperand($operand)
    {
        if (is_array($operand) && isset($operand[self::OPERAND_CONTEXT_PROPERTY])) {
            if (! is_string($operand[self::OPERAND_CONTEXT_PROPERTY])) {
                throw new InvalidAssertionException('Expression assertion context operand must be string');
            }
        }
    }

    /**
     * @param string $operand
     * @throws InvalidAssertionException if the operator is not supported.
     */
    private static function validateOperator($operator)
    {
        if (! in_array($operator, self::$validOperators, true)) {
            throw new InvalidAssertionException('Provided expression assertion operator is not supported');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        return $this->evaluate([
            'acl'       => $acl,
            'role'      => $role,
            'resource'  => $resource,
            'privilege' => $privilege,
        ]);
    }

    /**
     * @param array $context Contains the acl, privilege, role, and resource
     *     being tested currently.
     * @return bool
     */
    private function evaluate(array $context)
    {
        $left = $this->getLeftValue($context);
        $right = $this->getRightValue($context);

        return static::evaluateExpression($left, $this->operator, $right);
    }

    /**
     * @param array $context Contains the acl, privilege, role, and resource
     *     being tested currently.
     * @return mixed
     */
    private function getLeftValue(array $context)
    {
        return $this->resolveOperandValue($this->left, $context);
    }

    /**
     * @param array $context Contains the acl, privilege, role, and resource
     *     being tested currently.
     * @return mixed
     */
    private function getRightValue(array $context)
    {
        return $this->resolveOperandValue($this->right, $context);
    }

    /**
     * @param mixed|array
     * @param array $context Contains the acl, privilege, role, and resource
     *     being tested currently.
     * @return mixed
     * @throws RuntimeException if object cannot be resolved in context.
     * @throws RuntimeException if property cannot be resolved.
     */
    private function resolveOperandValue($operand, array $context)
    {
        if (! is_array($operand) || ! isset($operand[self::OPERAND_CONTEXT_PROPERTY])) {
            return $operand;
        }

        $contextProperty = $operand[self::OPERAND_CONTEXT_PROPERTY];

        if (strpos($contextProperty, '.') !== false) { // property path?
            list($objectName, $objectField) = explode('.', $contextProperty, 2);
            return $this->getObjectFieldValue($context, $objectName, $objectField);
        }

        if (! isset($context[$contextProperty])) {
            throw new RuntimeException(sprintf(
                "'%s' is not available in the assertion context",
                $contextProperty
            ));
        }

        return $context[$contextProperty];
    }

    /**
     * @param array $context Contains the acl, privilege, role, and resource
     *     being tested currently.
     * @param string $objectName Name of object in context to use.
     * @param string $field
     * @return mixed
     * @throws RuntimeException if object cannot be resolved in context.
     * @throws RuntimeException if property cannot be resolved.
     */
    private function getObjectFieldValue(array $context, $objectName, $field)
    {
        if (! isset($context[$objectName])) {
            throw new RuntimeException(sprintf(
                "'%s' is not available in the assertion context",
                $objectName
            ));
        }

        $object = $context[$objectName];
        $accessors = ['get', 'is'];
        $fieldAccessor = false === strpos($field, '_')
            ? $field
            : str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

        foreach ($accessors as $accessor) {
            $accessor .= $fieldAccessor;

            if (method_exists($object, $accessor)) {
                return $object->$accessor();
            }
        }

        if (! $this->propertyExists($object, $field)) {
            throw new RuntimeException(sprintf(
                "'%s' property cannot be resolved on the '%s' object",
                $field,
                $objectName
            ));
        }

        return $object->$field;
    }

    /**
     * @param mixed $left
     * @param string $right
     * @param mixed $right
     * @throws RuntimeException if operand is not supported.
     */
    private static function evaluateExpression($left, $operator, $right)
    {
        switch ($operator) {
            case self::OPERATOR_EQ:
                return $left == $right;
            case self::OPERATOR_NEQ:
                return $left != $right;
            case self::OPERATOR_LT:
                return $left < $right;
            case self::OPERATOR_LTE:
                return $left <= $right;
            case self::OPERATOR_GT:
                return $left > $right;
            case self::OPERATOR_GTE:
                return $left >= $right;
            case self::OPERATOR_IN:
                return in_array($left, $right);
            case self::OPERATOR_NIN:
                return ! in_array($left, $right);
            case self::OPERATOR_REGEX:
                return (bool) preg_match($right, $left);
            case self::OPERATOR_NREGEX:
                return ! (bool) preg_match($right, $left);
            case self::OPERATOR_SAME:
                return $left === $right;
            case self::OPERATOR_NSAME:
                return $left !== $right;
        }
    }

    /**
     * @param object $object
     * @param string $field
     * @return mixed
     */
    private function propertyExists($object, $property)
    {
        if (! property_exists($object, $property)) {
            return false;
        }

        $r = new ReflectionProperty($object, $property);
        return $r->isPublic();
    }
}
