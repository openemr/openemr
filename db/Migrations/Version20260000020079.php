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
 * Onsite messages table
 */
final class Version20260000020079 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onsite_messages table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onsite_messages');
        $table->addOption('comment', 'Portal messages');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('username', Types::STRING, ['length' => 64]);
        $table->addColumn('message', Types::TEXT);
        $table->addColumn('ip', Types::STRING, ['length' => 15]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('sender_id', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'comment' => 'who sent id',
        ]);
        $table->addColumn('recip_id', Types::STRING, ['length' => 255, 'comment' => 'who to id array']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onsite_messages');
    }
}
