<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Constraint;

class ForeignKey extends AbstractConstraint
{
    /**
     * @var string
     */
    protected $onDeleteRule = 'NO ACTION';

    /**
     * @var string
     */
    protected $onUpdateRule = 'NO ACTION';

    /**
     * @var string[]
     */
    protected $referenceColumn = [];

    /**
     * @var string
     */
    protected $referenceTable = '';

    /**
     * {@inheritDoc}
     */
    protected $columnSpecification = 'FOREIGN KEY (%s) ';

    /**
     * @var string[]
     */
    protected $referenceSpecification = [
        'REFERENCES %s ',
        'ON DELETE %s ON UPDATE %s'
    ];

    /**
     * @param null|string       $name
     * @param null|string|array $columns
     * @param string            $referenceTable
     * @param null|string|array $referenceColumn
     * @param null|string       $onDeleteRule
     * @param null|string       $onUpdateRule
     */
    public function __construct(
        $name,
        $columns,
        $referenceTable,
        $referenceColumn,
        $onDeleteRule = null,
        $onUpdateRule = null
    ) {
        $this->setName($name);
        $this->setColumns($columns);
        $this->setReferenceTable($referenceTable);
        $this->setReferenceColumn($referenceColumn);

        if ($onDeleteRule) {
            $this->setOnDeleteRule($onDeleteRule);
        }

        if ($onUpdateRule) {
            $this->setOnUpdateRule($onUpdateRule);
        }
    }

    /**
     * @param  string $referenceTable
     * @return self Provides a fluent interface
     */
    public function setReferenceTable($referenceTable)
    {
        $this->referenceTable = (string) $referenceTable;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * @param  null|string|array $referenceColumn
     * @return self Provides a fluent interface
     */
    public function setReferenceColumn($referenceColumn)
    {
        $this->referenceColumn = (array) $referenceColumn;

        return $this;
    }

    /**
     * @return array
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * @param  string $onDeleteRule
     * @return self Provides a fluent interface
     */
    public function setOnDeleteRule($onDeleteRule)
    {
        $this->onDeleteRule = (string) $onDeleteRule;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnDeleteRule()
    {
        return $this->onDeleteRule;
    }

    /**
     * @param  string $onUpdateRule
     * @return self Provides a fluent interface
     */
    public function setOnUpdateRule($onUpdateRule)
    {
        $this->onUpdateRule = (string) $onUpdateRule;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnUpdateRule()
    {
        return $this->onUpdateRule;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $data         = parent::getExpressionData();
        $colCount     = count($this->referenceColumn);
        $newSpecTypes = [self::TYPE_IDENTIFIER];
        $values       = [$this->referenceTable];

        $data[0][0] .= $this->referenceSpecification[0];

        if ($colCount) {
            $values       = array_merge($values, $this->referenceColumn);
            $newSpecParts = array_fill(0, $colCount, '%s');
            $newSpecTypes = array_merge($newSpecTypes, array_fill(0, $colCount, self::TYPE_IDENTIFIER));

            $data[0][0] .= sprintf('(%s) ', implode(', ', $newSpecParts));
        }

        $data[0][0] .= $this->referenceSpecification[1];

        $values[]       = $this->onDeleteRule;
        $values[]       = $this->onUpdateRule;
        $newSpecTypes[] = self::TYPE_LITERAL;
        $newSpecTypes[] = self::TYPE_LITERAL;

        $data[0][1] = array_merge($data[0][1], $values);
        $data[0][2] = array_merge($data[0][2], $newSpecTypes);

        return $data;
    }
}
