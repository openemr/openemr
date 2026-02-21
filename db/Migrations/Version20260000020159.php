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
 * Form eye mag impplan table
 */
final class Version20260000020159 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_mag_impplan table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_mag_impplan');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('form_id', Types::BIGINT);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('title', Types::STRING, ['length' => 255]);
        $table->addColumn('code', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('codetype', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('codedesc', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('codetext', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('plan', Types::STRING, [
            'length' => 3000,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('PMSFH_link', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('IMPPLAN_order', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['form_id', 'pid', 'title', 'plan'], 'second_index', ['lengths' => [null, null, null, 20]]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_mag_impplan');
    }
}
