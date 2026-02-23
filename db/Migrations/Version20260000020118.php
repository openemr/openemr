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
 * Lbf data table
 */
final class Version20260000020118 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lbf_data table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lbf_data');
        $table->addOption('comment', 'contains all data from layout-based forms');
        $table->addColumn('form_id', Types::INTEGER, ['autoincrement' => true, 'comment' => 'references forms.form_id']);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'comment' => 'references layout_options.field_id']);
        $table->addColumn('field_value', Types::TEXT, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('form_id', 'field_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lbf_data');
    }
}
