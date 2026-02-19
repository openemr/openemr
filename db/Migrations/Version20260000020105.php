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
 * User settings table
 */
final class Version20260000020105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user_settings');
        $table->addColumn('setting_user', Types::BIGINT, ['default' => 0]);
        $table->addColumn('setting_label', Types::STRING, ['length' => 100]);
        $table->addColumn('setting_value', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('setting_user', 'setting_label')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user_settings');
    }
}
