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
 * Lang languages table
 */
final class Version20260000010048 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lang_languages table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lang_languages');
        $table->addColumn('lang_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('lang_code', Types::STRING, ['fixed' => true, 'length' => 2, 'default' => '']);
        $table->addColumn('lang_description', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lang_is_rtl', Types::SMALLINT, ['notnull' => false, 'default' => 0, 'comment' => 'Set this to 1 for RTL languages Arabic, Farsi, Hebrew, Urdu etc.']);

        $table->addUniqueIndex(['lang_id'], 'lang_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lang_languages');
    }
}
