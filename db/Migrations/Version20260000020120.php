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
 * Gprelations table
 */
final class Version20260000020120 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create gprelations table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('gprelations');
        $table->addColumn('type1', Types::INTEGER);
        $table->addColumn('id1', Types::BIGINT);
        $table->addColumn('type2', Types::INTEGER);
        $table->addColumn('id2', Types::BIGINT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('type1', 'id1', 'type2', 'id2')
                ->create()
        );
        $table->addIndex(['type2', 'id2'], 'key2');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gprelations');
    }
}
