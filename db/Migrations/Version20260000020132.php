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
 * Code types table
 */
final class Version20260000020132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create code_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('code_types');
        $table->addColumn('ct_key', Types::STRING, ['length' => 15, 'comment' => 'short alphanumeric name']);
        $table->addColumn('ct_id', Types::INTEGER, ['comment' => 'numeric identifier']);
        $table->addColumn('ct_seq', Types::INTEGER, ['default' => 0, 'comment' => 'sort order']);
        $table->addColumn('ct_mod', Types::INTEGER, ['default' => 0, 'comment' => 'length of modifier field']);
        $table->addColumn('ct_just', Types::STRING, [
            'length' => 15,
            'default' => '',
            'comment' => 'ct_key of justify type, if any',
        ]);
        $table->addColumn('ct_mask', Types::STRING, [
            'length' => 9,
            'default' => '',
            'comment' => 'formatting mask for code values',
        ]);
        $table->addColumn('ct_fee', Types::SMALLINT, ['default' => 0, 'comment' => '1 if fees are used']);
        $table->addColumn('ct_rel', Types::SMALLINT, ['default' => 0, 'comment' => '1 if can relate to other code types']);
        $table->addColumn('ct_nofs', Types::SMALLINT, ['default' => 0, 'comment' => '1 if to be hidden in the fee sheet']);
        $table->addColumn('ct_diag', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this is a diagnosis type']);
        $table->addColumn('ct_active', Types::SMALLINT, ['default' => 1, 'comment' => '1 if this is active']);
        $table->addColumn('ct_label', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'label of this code type',
        ]);
        $table->addColumn('ct_external', Types::SMALLINT, ['default' => 0, 'comment' => '0 if stored codes in codes tables, 1 or greater if codes stored in external tables']);
        $table->addColumn('ct_claim', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this is used in claims']);
        $table->addColumn('ct_proc', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this is a procedure type']);
        $table->addColumn('ct_term', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this is a clinical term']);
        $table->addColumn('ct_problem', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this code type is used as a medical problem']);
        $table->addColumn('ct_drug', Types::SMALLINT, ['default' => 0, 'comment' => '1 if this code type is used as a medication']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ct_key')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('code_types');
    }
}
