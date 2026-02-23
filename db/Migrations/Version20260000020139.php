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
 * Esign signatures table
 */
final class Version20260000020139 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create esign_signatures table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('esign_signatures');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('tid', Types::INTEGER, ['comment' => 'Table row ID for signature']);
        $table->addColumn('table', Types::STRING, ['length' => 255, 'comment' => 'table name for the signature']);
        $table->addColumn('uid', Types::INTEGER, ['comment' => 'user id for the signing user']);
        $table->addColumn('datetime', Types::DATETIME_MUTABLE, ['comment' => 'datetime of the signature action']);
        $table->addColumn('is_lock', Types::SMALLINT, ['default' => 0, 'comment' => 'sig, lock or amendment']);
        $table->addColumn('amendment', Types::TEXT, ['length' => 65535, 'comment' => 'amendment text, if any']);
        $table->addColumn('hash', Types::STRING, ['length' => 255, 'comment' => 'hash of signed data']);
        $table->addColumn('signature_hash', Types::STRING, ['length' => 255, 'comment' => 'hash of signature itself']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['tid'], 'tid');
        $table->addIndex(['table'], 'table');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE esign_signatures');
    }
}
