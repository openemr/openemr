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
 * Form eye locking table
 */
final class Version20260000010067 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_locking table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_locking');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('IMP', Types::TEXT, ['length' => 65535]);
        $table->addColumn('PLAN', Types::TEXT, ['length' => 65535]);
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

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_locking');
    }
}
