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
 * Oauth clients table
 */
final class Version20260000020187 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create oauth_clients table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('oauth_clients');
        $table->addColumn('client_id', Types::STRING, ['length' => 80]);
        $table->addColumn('client_role', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('client_name', Types::STRING, ['length' => 80]);
        $table->addColumn('client_secret', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('registration_token', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('registration_uri_path', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('register_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('revoke_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('contacts', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('redirect_uri', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('grant_types', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('scope', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('user_id', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('site_id', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('is_confidential', Types::BOOLEAN, ['default' => 1]);
        $table->addColumn('logout_redirect_uris', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('jwks_uri', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('jwks', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('initiate_login_uri', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('endorsements', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('policy_uri', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('tos_uri', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('is_enabled', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('skip_ehr_launch_authorization_flow', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('dsi_type', Types::SMALLINT, [
            'unsigned' => true,
            'default' => 1,
            'comment' => '0=none, 1=evidence-based,2=predictive',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('client_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE oauth_clients');
    }
}
