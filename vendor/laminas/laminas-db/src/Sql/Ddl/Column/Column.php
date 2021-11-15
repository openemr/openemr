<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Column;

use Laminas\Db\Sql\Ddl\Constraint\ConstraintInterface;

class Column implements ColumnInterface
{
    /**
     * @var null|string|int
     */
    protected $default;

    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var ConstraintInterface[]
     */
    protected $constraints = [];

    /**
     * @var string
     */
    protected $specification = '%s %s';

    /**
     * @var string
     */
    protected $type = 'INTEGER';

    /**
     * @param null|string $name
     * @param bool        $nullable
     * @param mixed|null  $default
     * @param mixed[]     $options
     */
    public function __construct($name = null, $nullable = false, $default = null, array $options = [])
    {
        $this->setName($name);
        $this->setNullable($nullable);
        $this->setDefault($default);
        $this->setOptions($options);
    }

    /**
     * @param  string $name
     * @return self Provides a fluent interface
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  bool $nullable
     * @return self Provides a fluent interface
     */
    public function setNullable($nullable)
    {
        $this->isNullable = (bool) $nullable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable()
    {
        return $this->isNullable;
    }

    /**
     * @param  null|string|int $default
     * @return self Provides a fluent interface
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return null|string|int
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param  array $options
     * @return self Provides a fluent interface
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param  string $name
     * @param  string $value
     * @return self Provides a fluent interface
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return self Provides a fluent interface
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec = $this->specification;

        $params   = [];
        $params[] = $this->name;
        $params[] = $this->type;

        $types = [self::TYPE_IDENTIFIER, self::TYPE_LITERAL];

        if (! $this->isNullable) {
            $spec .= ' NOT NULL';
        }

        if ($this->default !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = $this->default;
            $types[]  = self::TYPE_VALUE;
        }

        $data = [[
            $spec,
            $params,
            $types,
        ]];

        foreach ($this->constraints as $constraint) {
            $data[] = ' ';
            $data = array_merge($data, $constraint->getExpressionData());
        }

        return $data;
    }
}
