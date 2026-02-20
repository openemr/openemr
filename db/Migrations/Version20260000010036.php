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
 * Gacl axo map table
 */
final class Version20260000010036 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create gacl_axo_map table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('gacl_axo_map');
        $table->addColumn('acl_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('section_value', Types::STRING, ['length' => 150, 'default' => 0]);
        $table->addColumn('value', Types::STRING, ['length' => 150]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('acl_id', 'section_value', 'value')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gacl_axo_map');
    }
}
