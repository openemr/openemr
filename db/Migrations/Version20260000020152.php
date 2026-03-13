<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use OpenEMR\Core\Database\Types\CustomTypes;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Form clinical instructions table
 */
final class Version20260000020152 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_clinical_instructions table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_clinical_instructions');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('instruction', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('date', 'datetime', ['columnDefinition' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']);
        $table->addColumn('activity', CustomTypes::TINYINT, ['notnull' => false, 'default' => 1]);
        $this->addPrimaryKey($table, 'id');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_clinical_instructions');
    }
}
