<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Ddl\Index;

class Index extends AbstractIndex
{
    /**
     * @var string
     */
    protected $specification = 'INDEX %s(...)';

    /**
     * @var array
     */
    protected $lengths;

    /**
     * @param  string|array|null $columns
     * @param  null|string $name
     * @param array $lengths
     */
    public function __construct($columns, $name = null, array $lengths = [])
    {
        $this->setColumns($columns);

        $this->name    = null === $name ? null : (string) $name;
        $this->lengths = $lengths;
    }

    /**
     *
     * @return array of array|string should return an array in the format:
     *
     * array (
     *    // a sprintf formatted string
     *    string $specification,
     *
     *    // the values for the above sprintf formatted string
     *    array $values,
     *
     *    // an array of equal length of the $values array, with either TYPE_IDENTIFIER or TYPE_VALUE for each value
     *    array $types,
     * )
     *
     */
    public function getExpressionData()
    {
        $colCount     = count($this->columns);
        $values       = [];
        $values[]     = $this->name ?: '';
        $newSpecTypes = [self::TYPE_IDENTIFIER];
        $newSpecParts = [];

        for ($i = 0; $i < $colCount; $i++) {
            $specPart = '%s';

            if (isset($this->lengths[$i])) {
                $specPart .= "({$this->lengths[$i]})";
            }

            $newSpecParts[] = $specPart;
            $newSpecTypes[] = self::TYPE_IDENTIFIER;
        }

        $newSpec = str_replace('...', implode(', ', $newSpecParts), $this->specification);

        return [[
            $newSpec,
            array_merge($values, $this->columns),
            $newSpecTypes,
        ]];
    }
}
