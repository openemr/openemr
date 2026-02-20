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
 * Lists medication table
 */
final class Version20260000020067 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lists_medication table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lists_medication');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('list_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'FK Reference to lists.id',
        ]);
        $table->addColumn('drug_dosage_instructions', Types::TEXT, [
            'comment' => 'Free text dosage instructions for taking the drug',
        ]);
        $table->addColumn('usage_category', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'option_id in list_options.list_id=medication-usage-category',
        ]);
        $table->addColumn('usage_category_title', Types::STRING, [
            'length' => 255,
            'comment' => 'title in list_options.list_id=medication-usage-category',
        ]);
        $table->addColumn('request_intent', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'option_id in list_options.list_id=medication-request-intent',
        ]);
        $table->addColumn('request_intent_title', Types::STRING, [
            'length' => 255,
            'comment' => 'title in list_options.list_id=medication-request-intent',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['usage_category'], 'lists_med_usage_category_idx');
        $table->addIndex(['request_intent'], 'lists_med_request_intent_idx');
        $table->addIndex(['list_id'], 'lists_medication_list_idx');
        $table->addOption('engine', 'InnoDB');
        $table->addOption('comment', 'Holds additional data about patient medications.');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lists_medication');
    }
}
