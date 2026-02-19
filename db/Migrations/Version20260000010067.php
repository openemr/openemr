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
 * Form eye locking table
 */
final class Version20260000010067 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_eye_locking table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_eye_locking');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('IMP', Types::TEXT);
        $table->addColumn('PLAN', Types::TEXT);
        $table->addColumn('Resource', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('Technician', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LOCKED', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LOCKEDBY', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_eye_locking');
    }
}
