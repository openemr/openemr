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
 * Form eye external table
 */
final class Version20260000020183 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_external table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_external');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('RUL', Types::TEXT);
        $table->addColumn('LUL', Types::TEXT);
        $table->addColumn('RLL', Types::TEXT);
        $table->addColumn('LLL', Types::TEXT);
        $table->addColumn('RBROW', Types::TEXT);
        $table->addColumn('LBROW', Types::TEXT);
        $table->addColumn('RMCT', Types::TEXT);
        $table->addColumn('LMCT', Types::TEXT);
        $table->addColumn('RADNEXA', Types::TEXT);
        $table->addColumn('LADNEXA', Types::TEXT);
        $table->addColumn('RMRD', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LMRD', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('RLF', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LLF', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('RVFISSURE', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LVFISSURE', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODHERTEL', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSHERTEL', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('HERTELBASE', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('RCAROTID', Types::TEXT);
        $table->addColumn('LCAROTID', Types::TEXT);
        $table->addColumn('RTEMPART', Types::TEXT);
        $table->addColumn('LTEMPART', Types::TEXT);
        $table->addColumn('RCNV', Types::TEXT);
        $table->addColumn('LCNV', Types::TEXT);
        $table->addColumn('RCNVII', Types::TEXT);
        $table->addColumn('LCNVII', Types::TEXT);
        $table->addColumn('EXT_COMMENTS', Types::TEXT);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_external');
    }
}
