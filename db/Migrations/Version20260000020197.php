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
 * Document template profiles table
 */
final class Version20260000020197 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create document_template_profiles table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('document_template_profiles');
        $table->addColumn('id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('template_id', Types::BIGINT, ['unsigned' => true]);
        $table->addColumn('profile', Types::STRING, ['length' => 64]);
        $table->addColumn('template_name', Types::STRING, ['length' => 255]);
        $table->addColumn('category', Types::STRING, ['length' => 64]);
        $table->addColumn('provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('modified_date', Types::DATETIME_MUTABLE);
        $table->addColumn('member_of', Types::STRING, ['length' => 64]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('recurring', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('event_trigger', Types::STRING, ['length' => 31]);
        $table->addColumn('period', Types::INTEGER);
        $table->addColumn('notify_trigger', Types::STRING, ['length' => 31]);
        $table->addColumn('notify_period', Types::INTEGER);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['profile', 'template_id', 'member_of'], 'location');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE document_template_profiles');
    }
}
