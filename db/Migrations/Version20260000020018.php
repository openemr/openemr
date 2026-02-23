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
 * Contact address table
 */
final class Version20260000020018 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create contact_address table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('contact_address');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('contact_id', Types::BIGINT);
        $table->addColumn('address_id', Types::BIGINT);
        $table->addColumn('priority', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'FK to list_options.option_id for list_id address-types',
        ]);
        $table->addColumn('use', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'FK to list_options.option_id for list_id address-uses',
        ]);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('status', Types::STRING, ['fixed' => true, 
            'length' => 1,
            'notnull' => false,
            'comment' => 'A=active,I=inactive',
        ]);
        $table->addColumn('is_primary', Types::STRING, ['fixed' => true, 
            'length' => 1,
            'notnull' => false,
            'comment' => 'Y=yes,N=no',
        ]);
        $table->addColumn('period_start', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Date the address became active']);
        $table->addColumn('period_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Date the address became deactivated']);
        $table->addColumn('inactivated_reason', Types::STRING, [
            'length' => 45,
            'notnull' => false,
            'default' => null,
            'comment' => '[Values: Moved, Mail Returned, etc]',
        ]);
        $table->addColumn('created_date', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addColumn('updated_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['contact_id'], null);
        $table->addIndex(['address_id'], null);
        $table->addIndex(['contact_id', 'address_id'], 'contact_address_idx');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_address');
    }
}
