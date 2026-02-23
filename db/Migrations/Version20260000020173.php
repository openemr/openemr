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
 * Medex outgoing table
 */
final class Version20260000020173 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create medex_outgoing table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('medex_outgoing');
        $table->addColumn('msg_uid', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('msg_pid', Types::INTEGER);
        $table->addColumn('msg_pc_eid', Types::STRING, ['length' => 11]);
        $table->addColumn('campaign_uid', Types::INTEGER, ['default' => 0]);
        $table->addColumn('msg_date', Types::DATETIME_MUTABLE);
        $table->addColumn('msg_type', Types::STRING, ['length' => 50]);
        $table->addColumn('msg_reply', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('msg_extra_text', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('medex_uid', Types::INTEGER, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('msg_uid')
                ->create()
        );
        $table->addUniqueIndex(['msg_uid', 'msg_pc_eid', 'medex_uid'], 'msg_eid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE medex_outgoing');
    }
}
