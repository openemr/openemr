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
 * Shared attributes table
 */
final class Version20260000020141 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create shared_attributes table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('shared_attributes');
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('encounter', Types::BIGINT, ['comment' => '0 if patient attribute, else encounter attribute']);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'comment' => 'references layout_options.field_id']);
        $table->addColumn('last_update', Types::DATETIME_MUTABLE, ['comment' => 'time of last update']);
        $table->addColumn('user_id', Types::BIGINT, ['comment' => 'user who last updated']);
        $table->addColumn('field_value', Types::TEXT, ['length' => 65535]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pid', 'encounter', 'field_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE shared_attributes');
    }
}
