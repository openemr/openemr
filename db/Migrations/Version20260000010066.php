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
 * Form eye neuro table
 */
final class Version20260000010066 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_eye_neuro table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_eye_neuro');
        $table->addColumn('id', Types::BIGINT, ['comment' => 'Links to forms.form_id']);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ACT', Types::STRING, ['length' => 3, 'default' => 'on']);
        $table->addColumn('ACT5CCDIST', Types::TEXT);
        $table->addColumn('ACT1CCDIST', Types::TEXT);
        $table->addColumn('ACT2CCDIST', Types::TEXT);
        $table->addColumn('ACT3CCDIST', Types::TEXT);
        $table->addColumn('ACT4CCDIST', Types::TEXT);
        $table->addColumn('ACT6CCDIST', Types::TEXT);
        $table->addColumn('ACT7CCDIST', Types::TEXT);
        $table->addColumn('ACT8CCDIST', Types::TEXT);
        $table->addColumn('ACT9CCDIST', Types::TEXT);
        $table->addColumn('ACT10CCDIST', Types::TEXT);
        $table->addColumn('ACT11CCDIST', Types::TEXT);
        $table->addColumn('ACT1SCDIST', Types::TEXT);
        $table->addColumn('ACT2SCDIST', Types::TEXT);
        $table->addColumn('ACT3SCDIST', Types::TEXT);
        $table->addColumn('ACT4SCDIST', Types::TEXT);
        $table->addColumn('ACT5SCDIST', Types::TEXT);
        $table->addColumn('ACT6SCDIST', Types::TEXT);
        $table->addColumn('ACT7SCDIST', Types::TEXT);
        $table->addColumn('ACT8SCDIST', Types::TEXT);
        $table->addColumn('ACT9SCDIST', Types::TEXT);
        $table->addColumn('ACT10SCDIST', Types::TEXT);
        $table->addColumn('ACT11SCDIST', Types::TEXT);
        $table->addColumn('ACT1SCNEAR', Types::TEXT);
        $table->addColumn('ACT2SCNEAR', Types::TEXT);
        $table->addColumn('ACT3SCNEAR', Types::TEXT);
        $table->addColumn('ACT4SCNEAR', Types::TEXT);
        $table->addColumn('ACT5CCNEAR', Types::TEXT);
        $table->addColumn('ACT6CCNEAR', Types::TEXT);
        $table->addColumn('ACT7CCNEAR', Types::TEXT);
        $table->addColumn('ACT8CCNEAR', Types::TEXT);
        $table->addColumn('ACT9CCNEAR', Types::TEXT);
        $table->addColumn('ACT10CCNEAR', Types::TEXT);
        $table->addColumn('ACT11CCNEAR', Types::TEXT);
        $table->addColumn('ACT5SCNEAR', Types::TEXT);
        $table->addColumn('ACT6SCNEAR', Types::TEXT);
        $table->addColumn('ACT7SCNEAR', Types::TEXT);
        $table->addColumn('ACT8SCNEAR', Types::TEXT);
        $table->addColumn('ACT9SCNEAR', Types::TEXT);
        $table->addColumn('ACT10SCNEAR', Types::TEXT);
        $table->addColumn('ACT11SCNEAR', Types::TEXT);
        $table->addColumn('ACT1CCNEAR', Types::TEXT);
        $table->addColumn('ACT2CCNEAR', Types::TEXT);
        $table->addColumn('ACT3CCNEAR', Types::TEXT);
        $table->addColumn('MOTILITYNORMAL', Types::STRING, ['length' => 3, 'default' => 'on']);
        $table->addColumn('MOTILITY_RS', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_RI', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_RR', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_RL', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_LS', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_LI', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_LR', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_LL', Types::STRING, ['length' => 1, 'default' => '0']);
        $table->addColumn('MOTILITY_RRSO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_RLSO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_RRIO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_RLIO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_LRSO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_LLSO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_LRIO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('MOTILITY_LLIO', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('NEURO_COMMENTS', Types::TEXT);
        $table->addColumn('STEREOPSIS', Types::STRING, ['length' => 25, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODNPA', Types::TEXT);
        $table->addColumn('OSNPA', Types::TEXT);
        $table->addColumn('VERTFUSAMPS', Types::TEXT);
        $table->addColumn('DIVERGENCEAMPS', Types::TEXT);
        $table->addColumn('NPC', Types::STRING, ['length' => 10, 'notnull' => false, 'default' => null]);
        $table->addColumn('DACCDIST', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('DACCNEAR', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('CACCDIST', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('CACCNEAR', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('ODCOLOR', Types::TEXT);
        $table->addColumn('OSCOLOR', Types::TEXT);
        $table->addColumn('ODCOINS', Types::TEXT);
        $table->addColumn('OSCOINS', Types::TEXT);
        $table->addColumn('ODREDDESAT', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);
        $table->addColumn('OSREDDESAT', Types::STRING, ['length' => 20, 'notnull' => false, 'default' => null]);

        $table->addUniqueIndex(['id', 'pid'], 'id_pid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_eye_neuro');
    }
}
