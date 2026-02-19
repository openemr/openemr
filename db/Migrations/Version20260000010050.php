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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Log table
 */
final class Version20260000010050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create log table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('log');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('event', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('category', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
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
        $table->addColumn('comments', Types::TEXT);
        $table->addColumn('user_notes', Types::TEXT);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('success', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('checksum', Types::TEXT);
        $table->addColumn('crt_user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('log_from', Types::STRING, ['length' => 20, 'default' => 'open-emr']);
        $table->addColumn('menu_item_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ccda_doc_id', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'CCDA document id from ccda',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['patient_id'], 'patient_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('log');
    }
}
