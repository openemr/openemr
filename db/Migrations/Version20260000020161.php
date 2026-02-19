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
 * Form taskman table
 */
final class Version20260000020161 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_taskman table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_taskman');
        $table->addColumn('ID', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('REQ_DATE', Types::DATETIME_MUTABLE);
        $table->addColumn('FROM_ID', Types::BIGINT);
        $table->addColumn('TO_ID', Types::BIGINT);
        $table->addColumn('PATIENT_ID', Types::BIGINT, ['default' => null]);
        $table->addColumn('DOC_ID', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ENC_ID', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('METHOD', Types::STRING, [
            'length' => 20,
            'default' => null,
            'comment' => '1 = completed',
        ]);
        $table->addColumn('COMPLETED_DATE', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('COMMENT', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('USERFIELD_1', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ID')
                ->create()
        );

        $table->addOption('engine', 'INNODB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_taskman');
    }
}
