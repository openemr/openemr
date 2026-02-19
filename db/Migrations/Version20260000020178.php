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
 * Form eye ros table
 */
final class Version20260000020178 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_eye_ros table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_eye_ros');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ROSGENERAL', Types::TEXT);
        $table->addColumn('ROSHEENT', Types::TEXT);
        $table->addColumn('ROSCV', Types::TEXT);
        $table->addColumn('ROSPULM', Types::TEXT);
        $table->addColumn('ROSGI', Types::TEXT);
        $table->addColumn('ROSGU', Types::TEXT);
        $table->addColumn('ROSDERM', Types::TEXT);
        $table->addColumn('ROSNEURO', Types::TEXT);
        $table->addColumn('ROSPSYCH', Types::TEXT);
        $table->addColumn('ROSMUSCULO', Types::TEXT);
        $table->addColumn('ROSIMMUNO', Types::TEXT);
        $table->addColumn('ROSENDOCRINE', Types::TEXT);
        $table->addColumn('ROSCOMMENTS', Types::TEXT);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_eye_ros');
    }
}
