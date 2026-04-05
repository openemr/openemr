<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\SchemaVersion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaVersionTest extends TestCase
{
    #[Test]
    public function fromDatabaseRowCastsTypes(): void
    {
        $row = [
            'v_major' => '8',
            'v_minor' => '0',
            'v_patch' => '1',
            'v_database' => '535',
            'v_acl' => '12',
        ];

        $v = SchemaVersion::fromDatabaseRow($row);

        $this->assertSame(8, $v->major);
        $this->assertSame(0, $v->minor);
        $this->assertSame(1, $v->patch);
        $this->assertSame(535, $v->database);
        $this->assertSame(12, $v->acl);
    }

    #[Test]
    public function fromDatabaseRowHandlesMissingKeys(): void
    {
        $v = SchemaVersion::fromDatabaseRow([]);

        $this->assertSame(0, $v->major);
        $this->assertSame(0, $v->minor);
        $this->assertSame(0, $v->patch);
        $this->assertSame(0, $v->database);
        $this->assertSame(0, $v->acl);
    }
}
