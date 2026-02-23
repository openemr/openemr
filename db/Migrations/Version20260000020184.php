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
 * Form eye antseg table
 */
final class Version20260000020184 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_antseg table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_antseg');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODSCHIRMER1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSSCHIRMER1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODSCHIRMER2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSSCHIRMER2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODTBUT', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSTBUT', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSCONJ', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODCONJ', Types::TEXT, ['length' => 65535]);
        $table->addColumn('ODCORNEA', Types::TEXT, ['length' => 65535]);
        $table->addColumn('OSCORNEA', Types::TEXT, ['length' => 65535]);
        $table->addColumn('ODAC', Types::TEXT, ['length' => 65535]);
        $table->addColumn('OSAC', Types::TEXT, ['length' => 65535]);
        $table->addColumn('ODLENS', Types::TEXT, ['length' => 65535]);
        $table->addColumn('OSLENS', Types::TEXT, ['length' => 65535]);
        $table->addColumn('ODIRIS', Types::TEXT, ['length' => 65535]);
        $table->addColumn('OSIRIS', Types::TEXT, ['length' => 65535]);
        $table->addColumn('PUPIL_NORMAL', Types::STRING, ['length' => 2, 'default' => 1]);
        $table->addColumn('ODPUPILSIZE1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODPUPILSIZE2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODPUPILREACTIVITY', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODAPD', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSPUPILSIZE1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSPUPILSIZE2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSPUPILREACTIVITY', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSAPD', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMODPUPILSIZE1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMODPUPILSIZE2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMODPUPILREACTIVITY', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMOSPUPILSIZE1', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMOSPUPILSIZE2', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('DIMOSPUPILREACTIVITY', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('PUPIL_COMMENTS', Types::TEXT, ['length' => 65535]);
        $table->addColumn('ODKTHICKNESS', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSKTHICKNESS', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ODGONIO', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('OSGONIO', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ANTSEG_COMMENTS', Types::TEXT, ['length' => 65535]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_antseg');
    }
}
