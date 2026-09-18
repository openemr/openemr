<?php

/**
 * Isolated MissingSiteIdException Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\System;

use OpenEMR\Common\System\MissingSiteException;
use OpenEMR\Common\System\MissingSiteIdException;
use PHPUnit\Framework\TestCase;

class MissingSiteIdExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $exception = new MissingSiteIdException();
        $this->assertSame('Site ID is missing from session data.', $exception->getMessage());
    }

    public function testCustomMessageAndPrevious(): void
    {
        $previous = new \RuntimeException('underlying');
        $exception = new MissingSiteIdException('custom', 0, $previous);
        $this->assertSame('custom', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCallersCanCatchAsMissingSiteException(): void
    {
        try {
            throw new MissingSiteIdException();
        } catch (MissingSiteException $caught) {
            $this->assertSame('Site ID is missing from session data.', $caught->getMessage());
        }
    }

    public function testProducesBadRequestHttpStatus(): void
    {
        $exception = new MissingSiteIdException();
        $this->assertSame(400, $exception->getStatusCode());
    }
}
