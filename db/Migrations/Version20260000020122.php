<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Procedure type table
 */
final class Version20260000020122 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_type table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_type');
        $table->addColumn('procedure_type_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('parent', Types::BIGINT, ['default' => 0, 'comment' => 'references procedure_type.procedure_type_id']);
        $table->addColumn('name', Types::STRING, [
            'length' => 63,
            'default' => '',
            'comment' => 'name for this category, procedure or result type',
        ]);
        $table->addColumn('lab_id', Types::BIGINT, ['default' => 0, 'comment' => 'references procedure_providers.ppid, 0 means default to parent']);
        $table->addColumn('procedure_code', Types::STRING, [
            'length' => 64,
            'default' => '',
            'comment' => 'code identifying this procedure',
        ]);
        $table->addColumn('procedure_type', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'see list proc_type',
        ]);
        $table->addColumn('body_site', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'where to do injection, e.g. arm, buttock',
        ]);
        $table->addColumn('specimen', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'blood, urine, saliva, etc.',
        ]);
        $table->addColumn('route_admin', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'oral, injection',
        ]);
        $table->addColumn('laterality', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'left, right, ...',
        ]);
        $table->addColumn('description', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'descriptive text for procedure_code',
        ]);
        $table->addColumn('standard_code', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'industry standard code type and code (e.g. CPT4:12345)',
        ]);
        $table->addColumn('related_code', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'suggested code(s) for followup services if result is abnormal',
        ]);
        $table->addColumn('units', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'default for procedure_result.units',
        ]);
        $table->addColumn('range', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'default for procedure_result.range',
        ]);
        $table->addColumn('seq', Types::INTEGER, ['default' => 0, 'comment' => 'sequence number for ordering']);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 1, 'comment' => '1=active, 0=inactive']);
        $table->addColumn('notes', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'additional notes to enhance description',
        ]);
        $table->addColumn('transport', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('procedure_type_name', Types::STRING, ['length' => 64, 'notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_type_id')
                ->create()
        );
        $table->addIndex(['parent'], 'parent');
        $table->addIndex(['procedure_code'], 'ptype_procedure_code');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_type');
    }
}
