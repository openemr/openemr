<?php

/**
 * Facility save dialog status.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\TestCase;

class FacilitySaveDialogTest extends TestCase
{
    /**
     * The dialog body names saved or not saved.
     */
    public function testSaveDialogBodyNamesTheStatus(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, 'Stored'), true);
        $open = json_decode(FacilityService::facilitySaveDialogBody(false, ''), true);
        $this->assertIsArray($saved);
        $this->assertIsArray($open);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('Stored', $saved['message']);
        $this->assertSame('not_saved', $open['status']);
        $this->assertSame('', $open['message']);
    }

    /**
     * A sentence that is not valid text drops the notice and keeps the status.
     */
    public function testSaveDialogBodyKeepsTheStatusWhenTheSentenceCannotBeEncoded(): void
    {
        $saved = json_decode(FacilityService::facilitySaveDialogBody(true, "\xB1\x31"), true);
        $open = json_decode(FacilityService::facilitySaveDialogBody(false, "\xB1\x31"), true);
        $this->assertIsArray($saved);
        $this->assertIsArray($open);

        $this->assertSame('saved', $saved['status']);
        $this->assertSame('', $saved['message']);
        $this->assertSame('not_saved', $open['status']);
        $this->assertSame('', $open['message']);
    }
}
