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
 * Form eye ros table
 */
final class Version20260000020178 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_ros table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_ros');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ROSGENERAL', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSHEENT', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSCV', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSPULM', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSGI', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSGU', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSDERM', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSNEURO', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSPSYCH', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSMUSCULO', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSIMMUNO', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSENDOCRINE', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ROSCOMMENTS', Types::TEXT, ['notnull' => false, 'length' => 65535]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_ros');
    }
}
