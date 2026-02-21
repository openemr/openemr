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
 * Patient settings table
 */
final class Version20260000020203 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_settings');
        $table->addColumn('setting_patient', Types::BIGINT, ['default' => 0]);
        $table->addColumn('setting_label', Types::STRING, ['length' => 100]);
        $table->addColumn('setting_value', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('setting_patient', 'setting_label')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_settings');
    }
}
