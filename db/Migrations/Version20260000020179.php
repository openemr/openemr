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
 * Form eye vitals table
 */
final class Version20260000020179 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_vitals table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_vitals');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('alert', Types::STRING, ['notnull' => false, 'length' => 3, 'default' => 'yes']);
        $table->addColumn('oriented', Types::STRING, ['notnull' => false, 'length' => 3, 'default' => 'TPP']);
        $table->addColumn('confused', Types::STRING, ['notnull' => false, 'length' => 3, 'default' => 'nml']);
        $table->addColumn('ODIOPAP', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSIOPAP', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODIOPTPN', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSIOPTPN', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODIOPFTN', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSIOPFTN', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('IOPTIME', Types::TIME_MUTABLE);
        $table->addColumn('ODIOPPOST', Types::STRING, ['length' => 10]);
        $table->addColumn('OSIOPPOST', Types::STRING, ['length' => 10]);
        $table->addColumn('IOPPOSTTIME', Types::TIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODIOPTARGET', Types::STRING, ['length' => 10]);
        $table->addColumn('OSIOPTARGET', Types::STRING, ['length' => 10]);
        $table->addColumn('AMSLEROD', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('AMSLEROS', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODVF1', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODVF2', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODVF3', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODVF4', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('OSVF1', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('OSVF2', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('OSVF3', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('OSVF4', Types::SMALLINT, ['notnull' => false, 'default' => null]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_vitals');
    }
}
