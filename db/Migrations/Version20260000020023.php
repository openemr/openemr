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
 * Syndromic surveillance table
 */
final class Version20260000020023 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create syndromic_surveillance table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('syndromic_surveillance');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('lists_id', Types::BIGINT);
        $table->addColumn('submission_date', Types::DATETIME_MUTABLE);
        $table->addColumn('filename', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['lists_id'], 'lists_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE syndromic_surveillance');
    }
}
