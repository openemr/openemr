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
 * Contact telecom table
 */
final class Version20260000020019 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create contact_telecom table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('contact_telecom');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('contact_id', Types::BIGINT);
        $table->addColumn('rank', Types::INTEGER, ['notnull' => false, 'comment' => 'Specify preferred order of use (1 = highest)']);
        $table->addColumn('system', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'FK to list_options.option_id for list_id telecom_systems [phone, fax, email, pager, url, sms, other]',
        ]);
        $table->addColumn('use', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'FK to list_options.option_id for list_id telecom_uses [home, work, temp, old, mobile]',
        ]);
        $table->addColumn('value', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
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
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('period_start', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Date the telecom became active']);
        $table->addColumn('period_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Date the telecom became deactivated']);
        $table->addColumn('inactivated_reason', Types::STRING, [
            'length' => 45,
            'notnull' => false,
            'default' => null,
            'comment' => '[Values: ???, etc]',
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
        $table->addIndex(['contact_id'], 'contact_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_telecom');
    }
}
