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
 * Form eye postseg table
 */
final class Version20260000010065 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_eye_postseg table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_eye_postseg');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ODDISC', Types::TEXT);
        $table->addColumn('OSDISC', Types::TEXT);
        $table->addColumn('ODCUP', Types::TEXT);
        $table->addColumn('OSCUP', Types::TEXT);
        $table->addColumn('ODMACULA', Types::TEXT);
        $table->addColumn('OSMACULA', Types::TEXT);
        $table->addColumn('ODVESSELS', Types::TEXT);
        $table->addColumn('OSVESSELS', Types::TEXT);
        $table->addColumn('ODVITREOUS', Types::TEXT);
        $table->addColumn('OSVITREOUS', Types::TEXT);
        $table->addColumn('ODPERIPH', Types::TEXT);
        $table->addColumn('OSPERIPH', Types::TEXT);
        $table->addColumn('ODCMT', Types::TEXT);
        $table->addColumn('OSCMT', Types::TEXT);
        $table->addColumn('RETINA_COMMENTS', Types::TEXT);
        $table->addColumn('DIL_RISKS', Types::STRING, ['length' => 2, 'default' => 'on']);
        $table->addColumn('DIL_MEDS', Types::TEXT);
        $table->addColumn('WETTYPE', Types::STRING, ['length' => 10]);
        $table->addColumn('ATROPINE', Types::STRING, ['length' => 25]);
        $table->addColumn('CYCLOMYDRIL', Types::STRING, ['length' => 25]);
        $table->addColumn('TROPICAMIDE', Types::STRING, ['length' => 25]);
        $table->addColumn('CYCLOGYL', Types::STRING, ['length' => 25]);
        $table->addColumn('NEO25', Types::STRING, ['length' => 25]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_eye_postseg');
    }
}
