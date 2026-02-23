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
 * Layout group properties table
 */
final class Version20260000020064 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create layout_group_properties table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('layout_group_properties');
        $table->addColumn('grp_form_id', Types::STRING, ['length' => 31]);
        $table->addColumn('grp_group_id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'empty when representing the whole form',
        ]);
        $table->addColumn('grp_title', Types::STRING, [
            'length' => 63,
            'default' => '',
            'comment' => 'descriptive name of the form or group',
        ]);
        $table->addColumn('grp_subtitle', Types::STRING, [
            'length' => 63,
            'default' => '',
            'comment' => 'for display under the title',
        ]);
        $table->addColumn('grp_mapping', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'the form category',
        ]);
        $table->addColumn('grp_seq', Types::INTEGER, ['default' => 0, 'comment' => 'optional order within mapping']);
        $table->addColumn('grp_activity', Types::BOOLEAN, ['default' => 1]);
        $table->addColumn('grp_repeats', Types::INTEGER, ['default' => 0]);
        $table->addColumn('grp_columns', Types::INTEGER, ['default' => 0]);
        $table->addColumn('grp_size', Types::INTEGER, ['default' => 0]);
        $table->addColumn('grp_issue_type', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('grp_aco_spec', Types::STRING, ['length' => 63, 'default' => '']);
        $table->addColumn('grp_save_close', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('grp_init_open', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('grp_referrals', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('grp_unchecked', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('grp_services', Types::STRING, ['length' => 4095, 'default' => '']);
        $table->addColumn('grp_products', Types::STRING, ['length' => 4095, 'default' => '']);
        $table->addColumn('grp_diags', Types::STRING, ['length' => 4095, 'default' => '']);
        $table->addColumn('grp_last_update', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('grp_form_id', 'grp_group_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE layout_group_properties');
    }
}
