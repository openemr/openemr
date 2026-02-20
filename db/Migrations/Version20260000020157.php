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
 * Form eye mag prefs table
 */
final class Version20260000020157 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_mag_prefs table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_mag_prefs');
        $table->addColumn('PEZONE', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LOCATION', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('LOCATION_text', Types::STRING, ['length' => 25]);
        $table->addColumn('id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('selection', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ZONE_ORDER', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('GOVALUE', Types::STRING, ['length' => 10, 'default' => 0]);
        $table->addColumn('ordering', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('FILL_ACTION', Types::STRING, ['length' => 10, 'default' => 'ADD']);
        $table->addColumn('GORIGHT', Types::STRING, ['length' => 50]);
        $table->addColumn('GOLEFT', Types::STRING, ['length' => 50]);
        $table->addColumn('UNSPEC', Types::STRING, ['length' => 50]);

        $table->addUniqueIndex(['id', 'PEZONE', 'LOCATION', 'selection'], 'id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_mag_prefs');
    }
}
