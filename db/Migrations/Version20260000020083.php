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
 * Patient access onsite table
 */
final class Version20260000020083 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_access_onsite table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('patient_access_onsite');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('portal_username', Types::STRING, ['length' => 100]);
        $table->addColumn('portal_pwd', Types::STRING, ['length' => 255]);
        $table->addColumn('portal_pwd_status', Types::SMALLINT, ['default' => 1, 'comment' => '0=>Password Created Through Demographics by The provider or staff. Patient Should Change it at first time it.1=>Pwd updated or created by patient itself']);
        $table->addColumn('portal_login_username', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'User entered username',
        ]);
        $table->addColumn('portal_onetime', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['pid'], 'pid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('patient_access_onsite');
    }
}
