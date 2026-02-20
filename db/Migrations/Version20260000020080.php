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
 * Onsite online table
 */
final class Version20260000020080 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onsite_online table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onsite_online');
        $table->addColumn('hash', Types::STRING, ['length' => 32]);
        $table->addColumn('ip', Types::STRING, ['length' => 15]);
        $table->addColumn('last_update', Types::DATETIME_MUTABLE);
        $table->addColumn('username', Types::STRING, ['length' => 64]);
        $table->addColumn('userid', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('hash')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onsite_online');
    }
}
