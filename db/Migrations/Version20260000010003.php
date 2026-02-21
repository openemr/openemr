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
 * Batchcom table
 */
final class Version20260000010003 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create batchcom table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('batchcom');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('patient_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('sent_by', Types::BIGINT, ['default' => 0]);
        $table->addColumn('msg_type', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('msg_subject', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('msg_text', Types::TEXT);
        $table->addColumn('msg_date_sent', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE batchcom');
    }
}
