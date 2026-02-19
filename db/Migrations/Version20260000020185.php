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
 * Login mfa registrations table
 */
final class Version20260000020185 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create login_mfa_registrations table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('login_mfa_registrations');
        $table->addColumn('user_id', Types::BIGINT);
        $table->addColumn('name', Types::STRING, ['length' => 30]);
        $table->addColumn('last_challenge', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('method', Types::STRING, ['length' => 31, 'comment' => 'Q&A, U2F, TOTP etc.']);
        $table->addColumn('var1', Types::STRING, [
            'length' => 4096,
            'default' => '',
            'comment' => 'Question, U2F registration etc.',
        ]);
        $table->addColumn('var2', Types::STRING, [
            'length' => 256,
            'default' => '',
            'comment' => 'Answer etc.',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('user_id', 'name')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('login_mfa_registrations');
    }
}
