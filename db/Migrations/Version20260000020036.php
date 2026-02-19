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
 * Email queue table
 */
final class Version20260000020036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create email_queue table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('email_queue');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('sender', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('recipient', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('subject', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('body', Types::TEXT);
        $table->addColumn('datetime_queued', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('sent', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('datetime_sent', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('error', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('error_message', Types::TEXT);
        $table->addColumn('datetime_error', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('template_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'The folder prefix and base filename (w/o extension) of the twig template file to use for this email',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['sent'], 'sent');
        $table->addOption('engine', 'InnoDb');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('email_queue');
    }
}
