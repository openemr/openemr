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
 * Onsite signatures table
 */
final class Version20260000020082 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onsite_signatures table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onsite_signatures');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('status', Types::STRING, ['length' => 128, 'default' => 'waiting']);
        $table->addColumn('type', Types::STRING, ['length' => 128]);
        $table->addColumn('created', Types::INTEGER);
        $table->addColumn('lastmod', Types::DATETIME_MUTABLE);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('signator', Types::STRING, ['length' => 255]);
        $table->addColumn('sig_image', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('signature', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('sig_hash', Types::STRING, ['length' => 255]);
        $table->addColumn('ip', Types::STRING, ['length' => 46]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['encounter'], 'encounter');
        $table->addUniqueIndex(['pid', 'user'], 'pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onsite_signatures');
    }
}
