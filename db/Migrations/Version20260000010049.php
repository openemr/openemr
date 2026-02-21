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
 * Lists table
 */
final class Version20260000010049 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lists table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lists');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('subtype', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('title', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('udi', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('udi_data', Types::TEXT);
        $table->addColumn('begdate', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('enddate', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('returndate', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('occurrence', Types::INTEGER, ['default' => 0]);
        $table->addColumn('classification', Types::INTEGER, ['default' => 0]);
        $table->addColumn('referredby', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('extrainfo', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('diagnosis', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('comments', Types::TEXT);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('outcome', Types::INTEGER, ['default' => 0]);
        $table->addColumn('destination', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('reinjury_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('injury_part', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('injury_type', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('injury_grade', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('reaction', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('verification', Types::STRING, [
            'length' => 36,
            'default' => '',
            'comment' => 'Reference to list_options option_id = allergyintolerance-verification',
        ]);
        $table->addColumn('external_allergyid', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('erx_source', Types::ENUM, [
            'default' => '0',
            'values' => ['0', '1'],
            'comment' => '0-OpenEMR 1-External',
        ]);
        $table->addColumn('erx_uploaded', Types::ENUM, [
            'default' => '0',
            'values' => ['0', '1'],
            'comment' => '0-Pending NewCrop upload 1-Uploaded TO NewCrop',
        ]);
        $table->addColumn('modifydate', Types::DATETIME_MUTABLE);
        $table->addColumn('severity_al', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('list_option_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'Reference to list_options table',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addIndex(['type'], 'type');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lists');
    }
}
