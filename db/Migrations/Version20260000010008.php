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
 * Facility table
 */
final class Version20260000010008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create facility table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('facility');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
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
        $table->addColumn('street', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('city', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('state', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('postal_code', Types::STRING, [
            'length' => 11,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('country_code', Types::STRING, ['length' => 30, 'default' => '']);
        $table->addColumn('federal_ein', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('website', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('email', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('service_location', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('billing_location', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('accepts_assignment', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('pos_code', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('x12_sender_id', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('attn', Types::STRING, [
            'length' => 65,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('domain_identifier', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('facility_npi', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('facility_taxonomy', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('tax_id_type', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('color', Types::STRING, ['length' => 7, 'default' => '']);
        $table->addColumn('primary_business_entity', Types::INTEGER, ['default' => 1, 'comment' => '0-Not Set as business entity 1-Set as business entity']);
        $table->addColumn('facility_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('extra_validation', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('mail_street', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mail_street2', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mail_city', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mail_state', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mail_zip', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('oid', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'HIEs CCDA and FHIR an OID is required/wanted',
        ]);
        $table->addColumn('iban', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('info', Types::TEXT);
        $table->addColumn('weno_id', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('inactive', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addColumn('organization_type', Types::STRING, [
            'length' => 50,
            'default' => 'prov',
            'comment' => 'Organization type as defined by HL7 Value Set: OrganizationType',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('facility');
    }
}
