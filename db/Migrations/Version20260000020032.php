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
 * Drug templates table
 */
final class Version20260000020032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create drug_templates table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('drug_templates');
        $table->addColumn('drug_id', Types::INTEGER);
        $table->addColumn('selector', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('dosage', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('period', Types::INTEGER, ['default' => 0]);
        $table->addColumn('quantity', Types::INTEGER, ['default' => 0]);
        $table->addColumn('refills', Types::INTEGER, ['default' => 0]);
        $table->addColumn('taxrates', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pkgqty', Types::FLOAT, ['default' => 1.0, 'comment' => 'Number of product items per template item']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('drug_id', 'selector')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('drug_templates');
    }
}
