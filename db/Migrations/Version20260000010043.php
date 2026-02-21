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
 * Insurance companies table
 */
final class Version20260000010043 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create insurance_companies table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('insurance_companies');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('attn', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cms_id', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ins_type_code', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('x12_receiver_id', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_default_partner_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('alt_cms_id', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('inactive', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('eligibility_id', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_default_eligibility_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('cqm_sop', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'HL7 Source of Payment for eCQMs',
        ]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE insurance_companies');
    }
}
