<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\TableGateway\Feature;

use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\Metadata\Object\TableObject;
use Laminas\Db\Metadata\Source\Factory as SourceFactory;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\TableGateway\Exception;

class MetadataFeature extends AbstractFeature
{
    /**
     * @var MetadataInterface
     */
    protected $metadata = null;

    /**
     * Constructor
     *
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata = null)
    {
        if ($metadata) {
            $this->metadata = $metadata;
        }
        $this->sharedData['metadata'] = [
            'primaryKey' => null,
            'columns' => []
        ];
    }

    public function postInitialize()
    {
        if ($this->metadata === null) {
            $this->metadata = SourceFactory::createSourceFromAdapter($this->tableGateway->adapter);
        }

        // localize variable for brevity
        $t = $this->tableGateway;
        $m = $this->metadata;

        $tableGatewayTable = is_array($t->table) ? current($t->table) : $t->table;

        if ($tableGatewayTable instanceof TableIdentifier) {
            $table = $tableGatewayTable->getTable();
            $schema = $tableGatewayTable->getSchema();
        } else {
            $table = $tableGatewayTable;
            $schema = null;
        }

        // get column named
        $columns = $m->getColumnNames($table, $schema);
        $t->columns = $columns;

        // set locally
        $this->sharedData['metadata']['columns'] = $columns;

        // process primary key only if table is a table; there are no PK constraints on views
        if (! ($m->getTable($table, $schema) instanceof TableObject)) {
            return;
        }

        $pkc = null;

        foreach ($m->getConstraints($table, $schema) as $constraint) {
            /** @var $constraint \Laminas\Db\Metadata\Object\ConstraintObject */
            if ($constraint->getType() == 'PRIMARY KEY') {
                $pkc = $constraint;
                break;
            }
        }

        if ($pkc === null) {
            throw new Exception\RuntimeException('A primary key for this column could not be found in the metadata.');
        }

        $pkcColumns = $pkc->getColumns();
        if (count($pkcColumns) === 1) {
            $primaryKey = $pkcColumns[0];
        } else {
            $primaryKey = $pkcColumns;
        }

        $this->sharedData['metadata']['primaryKey'] = $primaryKey;
    }
}
