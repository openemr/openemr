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
 * Gacl groups axo map table
 */
final class Version20260000010039 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create gacl_groups_axo_map table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('gacl_groups_axo_map');
        $table->addColumn('group_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('axo_id', Types::INTEGER, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id', 'axo_id')
                ->create()
        );
        $table->addIndex(['axo_id'], 'gacl_axo_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gacl_groups_axo_map');
    }
}
