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
 * Documents legal categories table
 */
final class Version20260000020029 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create documents_legal_categories table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('documents_legal_categories');
        $table->addColumn('dlc_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('dlc_category_type', Types::INTEGER, ['unsigned' => true, 'comment' => '1 category 2 subcategory']);
        $table->addColumn('dlc_category_name', Types::STRING, ['length' => 45]);
        $table->addColumn('dlc_category_parent', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dlc_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE documents_legal_categories');
    }
}
