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
 * Log comment encrypt table
 */
final class Version20260000020140 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create log_comment_encrypt table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('log_comment_encrypt');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('log_id', Types::INTEGER);
        $table->addColumn('encrypt', Types::ENUM, [
            'default' => 'No',
            'values' => ['Yes', 'No'],
        ]);
        $table->addColumn('checksum', Types::TEXT, ['notnull' => false]);
        $table->addColumn('checksum_api', Types::TEXT, ['notnull' => false]);
        $table->addColumn('version', Types::SMALLINT, ['default' => 0, 'comment' => '0 for mycrypt and 1 for openssl']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE log_comment_encrypt');
    }
}
