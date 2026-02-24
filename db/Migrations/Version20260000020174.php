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
 * Medex prefs table
 */
final class Version20260000020174 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create medex_prefs table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('medex_prefs');
        $table->addColumn('MedEx_id', Types::INTEGER, ['notnull' => false, 'default' => 0]);
        $table->addColumn('ME_username', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ME_api_key', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ME_facilities', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ME_providers', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ME_hipaa_default_override', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('PHONE_country_code', Types::INTEGER, ['default' => 1]);
        $table->addColumn('MSGS_default_yes', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('POSTCARDS_local', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('POSTCARDS_remote', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LABELS_local', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LABELS_choice', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('combine_time', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('postcard_top', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('status', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('MedEx_lastupdated', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);

        $table->addUniqueIndex(['ME_username'], 'ME_username');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE medex_prefs');
    }
}
