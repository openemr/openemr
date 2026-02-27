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
 * Enc category map table
 */
final class Version20260000020037 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create enc_category_map table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('enc_category_map');
        $table->addColumn('rule_enc_id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'encounter id from rule_enc_types list in list_options',
        ]);
        $table->addColumn('main_cat_id', Types::INTEGER, ['default' => 0, 'comment' => 'category id from event category in openemr_postcalendar_categories']);

        $table->addIndex(['rule_enc_id', 'main_cat_id'], 'rule_enc_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE enc_category_map');
    }
}
