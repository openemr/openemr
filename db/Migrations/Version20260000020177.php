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
 * Form eye hpi table
 */
final class Version20260000020177 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_hpi table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_hpi');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('CC1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('HPI1', Types::TEXT);
        $table->addColumn('QUALITY1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('TIMING1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DURATION1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('CONTEXT1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('SEVERITY1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('MODIFY1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ASSOCIATED1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LOCATION1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('CHRONIC1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('CHRONIC2', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('CHRONIC3', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('CC2', Types::TEXT);
        $table->addColumn('HPI2', Types::TEXT);
        $table->addColumn('QUALITY2', Types::TEXT);
        $table->addColumn('TIMING2', Types::TEXT);
        $table->addColumn('DURATION2', Types::TEXT);
        $table->addColumn('CONTEXT2', Types::TEXT);
        $table->addColumn('SEVERITY2', Types::TEXT);
        $table->addColumn('MODIFY2', Types::TEXT);
        $table->addColumn('ASSOCIATED2', Types::TEXT);
        $table->addColumn('LOCATION2', Types::TEXT);
        $table->addColumn('CC3', Types::TEXT);
        $table->addColumn('HPI3', Types::TEXT);
        $table->addColumn('QUALITY3', Types::TEXT);
        $table->addColumn('TIMING3', Types::TEXT);
        $table->addColumn('DURATION3', Types::TEXT);
        $table->addColumn('CONTEXT3', Types::TEXT);
        $table->addColumn('SEVERITY3', Types::TEXT);
        $table->addColumn('MODIFY3', Types::TEXT);
        $table->addColumn('ASSOCIATED3', Types::TEXT);
        $table->addColumn('LOCATION3', Types::TEXT);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_hpi');
    }
}
