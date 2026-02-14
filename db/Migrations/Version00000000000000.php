<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * This is a special migration that exists to bootstrap the migration-tracking
 * table (which doctrine/migrations manages).
 *
 * It intentionally does not make other changes.
 */
final class Version00000000000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration; establish migrations table';
    }

    public function up(Schema $schema): void
    {
        // Intentionally left blank
    }

    public function down(Schema $schema): void
    {
        // Intentionally left blank
    }
}
