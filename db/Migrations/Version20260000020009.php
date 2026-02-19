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
 * Categories table
 */
final class Version20260000020009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create categories table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('categories');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('value', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('parent', Types::INTEGER, ['default' => 0]);
        $table->addColumn('lft', Types::INTEGER, ['default' => 0]);
        $table->addColumn('rght', Types::INTEGER, ['default' => 0]);
        $table->addColumn('aco_spec', Types::STRING, ['length' => 63, 'default' => 'patients|docs']);
        $table->addColumn('codes', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Category codes for documents stored in this category',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['parent'], 'parent');
        $table->addIndex(['lft', 'rght'], 'lft');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('categories');
    }
}
