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
 * Background services table
 */
final class Version20260000010002 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create background_services table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('background_services');
        $table->addColumn('name', Types::STRING, ['length' => 31]);
        $table->addColumn('title', Types::STRING, ['length' => 127, 'comment' => 'name for reports']);
        $table->addColumn('active', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('running', Types::BOOLEAN, ['default' => -1, 'comment' => 'True indicates managed service is busy. Skip this interval']);
        $table->addColumn('next_run', Types::DATETIME_MUTABLE);
        $table->addColumn('execute_interval', Types::INTEGER, ['default' => 0, 'comment' => 'minimum number of minutes between function calls,0=manual mode']);
        $table->addColumn('function', Types::STRING, ['length' => 127, 'comment' => 'name of background service function']);
        $table->addColumn('require_once', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'include file (if necessary)',
        ]);
        $table->addColumn('sort_order', Types::INTEGER, ['default' => 100, 'comment' => 'lower numbers will be run first']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('name')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE background_services');
    }
}
