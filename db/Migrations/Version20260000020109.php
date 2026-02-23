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
 * Voids table
 */
final class Version20260000020109 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create voids table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('voids');
        $table->addColumn('void_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('patient_id', Types::BIGINT, ['comment' => 'references patient_data.pid']);
        $table->addColumn('encounter_id', Types::BIGINT, ['default' => 0, 'comment' => 'references form_encounter.encounter']);
        $table->addColumn('what_voided', Types::STRING, ['length' => 31, 'comment' => 'checkout,receipt and maybe other options later']);
        $table->addColumn('date_original', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'time of original action that is now voided',
        ]);
        $table->addColumn('date_voided', Types::DATETIME_MUTABLE, ['comment' => 'time of void action']);
        $table->addColumn('user_id', Types::BIGINT, ['comment' => 'references users.id']);
        $table->addColumn('amount1', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0,
            'comment' => 'for checkout,receipt total voided adjustments',
        ]);
        $table->addColumn('amount2', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0,
            'comment' => 'for checkout,receipt total voided payments',
        ]);
        $table->addColumn('other_info', Types::TEXT, ['length' => 65535, 'comment' => 'for checkout,receipt the old invoice refno']);
        $table->addColumn('reason', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('notes', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('void_id')
                ->create()
        );
        $table->addIndex(['date_voided'], 'datevoided');
        $table->addIndex(['patient_id', 'encounter_id'], 'pidenc');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE voids');
    }
}
