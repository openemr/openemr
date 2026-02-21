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
 * Patient birthday alert table
 */
final class Version20260000020171 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_birthday_alert table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_birthday_alert');
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('user_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('turned_off_on', Types::DATE_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pid', 'user_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_birthday_alert');
    }
}
