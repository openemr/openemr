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
 * Lang languages table
 */
final class Version20260000010048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lang_languages table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lang_languages');
        $table->addColumn('lang_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('lang_code', Types::STRING, ['length' => 2, 'default' => '']);
        $table->addColumn('lang_description', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lang_is_rtl', Types::SMALLINT, ['default' => 0, 'comment' => 'Set this to 1 for RTL languages Arabic, Farsi, Hebrew, Urdu etc.']);

        $table->addUniqueIndex(['lang_id'], 'lang_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lang_languages');
    }
}
