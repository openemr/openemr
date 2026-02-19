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
 * Document templates table
 */
final class Version20260000020196 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create document_templates table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('document_templates');
        $table->addColumn('id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encounter', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('modified_date', Types::DATETIME_MUTABLE);
        $table->addColumn('profile', Types::STRING, ['length' => 63]);
        $table->addColumn('category', Types::STRING, ['length' => 63]);
        $table->addColumn('location', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('template_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('send_date', Types::DATETIME_MUTABLE);
        $table->addColumn('end_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('size', Types::INTEGER, ['default' => 0]);
        $table->addColumn('template_content', Types::BLOB, ['notnull' => false, 'default' => null]);
        $table->addColumn('mime', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['pid', 'profile', 'category', 'template_name'], 'location');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('document_templates');
    }
}
