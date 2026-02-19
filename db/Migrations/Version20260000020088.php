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
 * Patient tracker element table
 */
final class Version20260000020088 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_tracker_element table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('patient_tracker_element');
        $table->addColumn('pt_tracker_id', Types::BIGINT, ['default' => 0, 'comment' => 'maps to id column in patient_tracker table']);
        $table->addColumn('start_datetime', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('room', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('status', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('seq', Types::STRING, [
            'length' => 4,
            'default' => '',
            'comment' => 'This is a numerical sequence for this pt_tracker_id events',
        ]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'This is the user that created this element',
        ]);

        $table->addIndex(['pt_tracker_id', 'seq'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('patient_tracker_element');
    }
}
