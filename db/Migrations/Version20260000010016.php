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
 * Forms table
 */
final class Version20260000010016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create forms table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('forms');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('form_name', Types::TEXT);
        $table->addColumn('form_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
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
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('deleted', Types::SMALLINT, ['default' => 0, 'comment' => 'flag indicates form has been deleted']);
        $table->addColumn('formdir', Types::TEXT);
        $table->addColumn('therapy_group_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('issue_id', Types::BIGINT, ['default' => 0, 'comment' => 'references lists.id to identify a case']);
        $table->addColumn('provider_id', Types::BIGINT, ['default' => 0, 'comment' => 'references users.id to identify a provider']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid', 'encounter'], 'pid_encounter');
        $table->addIndex(['form_id'], 'form_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('forms');
    }
}
