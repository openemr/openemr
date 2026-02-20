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
 * Procedure order relationships table
 */
final class Version20260000020130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create procedure_order_relationships table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('procedure_order_relationships');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('procedure_order_id', Types::BIGINT, ['comment' => 'Links to procedure_order.procedure_order_id']);
        $table->addColumn('resource_type', Types::STRING, ['length' => 50, 'comment' => 'FHIR resource type (Observation, Condition, etc.)']);
        $table->addColumn('resource_uuid', Types::BINARY, ['length' => 16, 'comment' => 'UUID of the related resource']);
        $table->addColumn('relationship', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'Type of relationship',
        ]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'User who created this link',
        ]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['procedure_order_id'], 'idx_order_id');
        $table->addIndex(['resource_type', 'resource_uuid'], 'idx_resource');
        $table->addIndex(['created_at'], 'idx_created_at');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('procedure_order_relationships');
    }
}
