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
 * Users table
 */
final class Version20260000010064 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('users');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('username', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('password', Types::TEXT, ['notnull' => false]);
        $table->addColumn('authorized', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addColumn('info', Types::TEXT, ['notnull' => false]);
        $table->addColumn('source', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('fname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('suffix', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('federaltaxid', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('federaldrugid', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('upin', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('facility', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('facility_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('see_auth', Types::INTEGER, ['default' => 1]);
        $table->addColumn('active', Types::BOOLEAN, ['default' => 1]);
        $table->addColumn('npi', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('title', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('specialty', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('billname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('email', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('email_direct', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('google_signin_email', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('url', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('assistant', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('organization', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('valedictory', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('street', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('streetb', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('city', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('state', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('zip', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('country_code', Types::STRING, ['notnull' => false, 'length' => 255, 'comment' => 'ISO 3166-1 alpha-2 country code for address but can take entire country name for now']);
        $table->addColumn('street2', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('streetb2', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('city2', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('state2', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('zip2', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('country_code2', Types::STRING, ['notnull' => false, 'length' => 255, 'comment' => 'ISO 3166-1 alpha-2 country code for address but can take entire country name for now']);
        $table->addColumn('phone', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fax', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('phonew1', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('phonew2', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('phonecell', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('cal_ui', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('taxonomy', Types::STRING, ['length' => 30, 'default' => '207Q00000X']);
        $table->addColumn('calendar', Types::BOOLEAN, ['default' => 0, 'comment' => '1 = appears in calendar']);
        $table->addColumn('abook_type', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('default_warehouse', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('irnpool', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('state_license_number', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('weno_prov_id', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('newcrop_user_role', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cpoe', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addColumn('physician_type', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('main_menu_role', Types::STRING, ['length' => 50, 'default' => 'standard']);
        $table->addColumn('patient_menu_role', Types::STRING, ['length' => 50, 'default' => 'standard']);
        $table->addColumn('portal_user', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('supervisor_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('billing_facility', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('billing_facility_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['abook_type'], 'abook_type');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
