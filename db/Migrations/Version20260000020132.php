<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Code types table
 */
final class Version20260000020132 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create code_types table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('code_types');
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
        $this->addBooleanColumn($table, 'ct_fee', default: false, comment: '1 if fees are used');
        $this->addBooleanColumn($table, 'ct_rel', default: false, comment: '1 if can relate to other code types');
        $this->addBooleanColumn($table, 'ct_nofs', default: false, comment: '1 if to be hidden in the fee sheet');
        $this->addBooleanColumn($table, 'ct_diag', default: false, comment: '1 if this is a diagnosis type');
        $this->addBooleanColumn($table, 'ct_active', default: true, comment: '1 if this is active');
        $table->addColumn('ct_label', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'label of this code type',
        ]);
        $this->addBooleanColumn($table, 'ct_external', default: false, comment: '0 if stored codes in codes tables, 1 or greater if codes stored in external tables');
        $this->addBooleanColumn($table, 'ct_claim', default: false, comment: '1 if this is used in claims');
        $this->addBooleanColumn($table, 'ct_proc', default: false, comment: '1 if this is a procedure type');
        $this->addBooleanColumn($table, 'ct_term', default: false, comment: '1 if this is a clinical term');
        $this->addBooleanColumn($table, 'ct_problem', default: false, comment: '1 if this code type is used as a medical problem');
        $this->addBooleanColumn($table, 'ct_drug', default: false, comment: '1 if this code type is used as a medication');
        $this->addPrimaryKey($table, 'ct_key');
        $table->addUniqueIndex(['ct_id'], 'ct_id');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE code_types');
    }
}
