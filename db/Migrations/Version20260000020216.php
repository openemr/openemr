<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * eRx TTL touch table
 */
final class Version20260000020216 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create erx_ttl_touch table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('erx_ttl_touch');
        $table->addColumn('patient_id', Types::BIGINT, [
            'unsigned' => true,
            'comment' => 'Patient record Id',
        ]);
        $table->addColumn('process', Types::ENUM, [
            'values' => ['allergies', 'medications'],
            'comment' => 'NewCrop eRx SOAP process',
        ]);
        $table->addColumn('updated', Types::DATETIME_MUTABLE, [
            'comment' => 'Date and time of last process update for patient',
        ]);
        $table->setComment('Store records last update per patient data process');

        $this->addPrimaryKey($table, 'patient_id', 'process');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE erx_ttl_touch');
    }
}
