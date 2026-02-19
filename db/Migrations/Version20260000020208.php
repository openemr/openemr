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
 * Care team member table
 */
final class Version20260000020208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create care_team_member table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('care_team_member');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('care_team_id', Types::INTEGER);
        $table->addColumn('user_id', Types::BIGINT, ['comment' => 'fk to users.id represents a provider or staff member']);
        $table->addColumn('contact_id', Types::BIGINT, ['comment' => 'fk to contact.id which represents a contact person not in users or facility table']);
        $table->addColumn('role', Types::STRING, ['length' => 50, 'comment' => 'fk to list_options.option_id WHERE list_id=care_team_roles']);
        $table->addColumn('facility_id', Types::BIGINT, ['comment' => 'fk to facility.id represents an organization or location']);
        $table->addColumn('provider_since', Types::DATE_MUTABLE, ['notnull' => false]);
        $table->addColumn('status', Types::STRING, [
            'length' => 100,
            'default' => 'active',
            'comment' => 'fk to list_options.option_id where list_id=Care_Team_Status',
        ]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('date_updated', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, ['comment' => 'fk to users.id and is the user that added this team member']);
        $table->addColumn('updated_by', Types::BIGINT, ['comment' => 'fk to users.id and is the user that last updated this team member']);
        $table->addColumn('note', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['care_team_id', 'user_id', 'facility_id', 'contact_id'], 'care_team_member_unique');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('care_team_member');
    }
}
