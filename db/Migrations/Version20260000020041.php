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
 * Facility user ids table
 */
final class Version20260000020041 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create facility_user_ids table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('facility_user_ids');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('facility_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'comment' => 'references layout_options.field_id']);
        $table->addColumn('field_value', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['uid', 'facility_id', 'field_id'], 'uid');
        $table->addIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE facility_user_ids');
    }
}
