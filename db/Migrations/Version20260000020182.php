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
 * Form eye biometrics table
 */
final class Version20260000020182 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_eye_biometrics table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_eye_biometrics');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODK1', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODK2', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODK2AXIS', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSK1', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSK2', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSK2AXIS', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODAXIALLENGTH', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSAXIALLENGTH', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODPDMeasured', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSPDMeasured', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODACD', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSACD', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODW2W', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSW2W', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODLT', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSLT', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['id', 'pid'], 'id_pid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_eye_biometrics');
    }
}
