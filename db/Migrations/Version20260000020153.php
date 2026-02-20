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
 * Valueset table
 */
final class Version20260000020153 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create valueset table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('valueset');
        $table->addColumn('nqf_code', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('code', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('code_system', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('code_type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('valueset', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('description', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('valueset_name', Types::STRING, [
            'length' => 500,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('nqf_code', 'code', 'valueset')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE valueset');
    }
}
