<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Clinical plans table
 */
final class Version20260000020013 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create clinical_plans table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('clinical_plans');
        $table->addColumn('id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Unique and maps to list_options list clinical_plans',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0, 'comment' => '0 is default for all patients, while > 0 is id from patient_data table']);
        $this->addBooleanColumn($table, 'normal_flag', notnull: false, comment: 'Normal Activation Flag');
        $this->addBooleanColumn($table, 'cqm_flag', notnull: false, comment: 'Clinical Quality Measure flag (unable to customize per patient)');
        $this->addBooleanColumn($table, 'cqm_2011_flag', notnull: false, comment: '2011 Clinical Quality Measure flag (unable to customize per patient)');
        $this->addBooleanColumn($table, 'cqm_2014_flag', notnull: false, comment: '2014 Clinical Quality Measure flag (unable to customize per patient)');
        $table->addColumn('cqm_measure_group', Types::STRING, [
            'length' => 10,
            'default' => '',
            'comment' => 'Clinical Quality Measure Group Identifier',
        ]);
        $this->addPrimaryKey($table, 'id', 'pid');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE clinical_plans');
    }
}
