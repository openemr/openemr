<?php

/**
 * Isolated tests for the CodeImportSupportedTypeFilterEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events;

use OpenEMR\Events\Codes\CodeImportSupportedTypeFilterEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CodeImportSupportedTypeFilterEventTest extends TestCase
{
    public function testConstructorSeedsTheSupportedTypes(): void
    {
        $event = new CodeImportSupportedTypeFilterEvent(['RXCUI', 'LOINC']);

        $this->assertSame(['RXCUI', 'LOINC'], $event->getSupportedCodeTypes());
    }

    public function testAddAppendsInOrder(): void
    {
        $event = new CodeImportSupportedTypeFilterEvent(['RXCUI']);
        $event->addSupportedCodeType('LOINC');
        $event->addSupportedCodeType('ICPC2');

        $this->assertSame(['RXCUI', 'LOINC', 'ICPC2'], $event->getSupportedCodeTypes());
    }

    public function testAddIsIdempotent(): void
    {
        $event = new CodeImportSupportedTypeFilterEvent(['RXCUI']);
        $event->addSupportedCodeType('RXCUI');

        $this->assertSame(['RXCUI'], $event->getSupportedCodeTypes());
    }

    public function testRemoveReindexesTheList(): void
    {
        $event = new CodeImportSupportedTypeFilterEvent(['RXCUI', 'LOINC', 'ICPC2']);
        $event->removeSupportedCodeType('LOINC');

        // assertSame on the whole array compares keys as well as values, so a gappy array left
        // behind by array_filter() (keys 0 and 2) fails here. Callers rely on this being a list.
        $this->assertSame(['RXCUI', 'ICPC2'], $event->getSupportedCodeTypes());
    }

    public function testRemoveOfAnAbsentTypeIsANoOp(): void
    {
        $event = new CodeImportSupportedTypeFilterEvent(['RXCUI']);
        $event->removeSupportedCodeType('SNOMED');

        $this->assertSame(['RXCUI'], $event->getSupportedCodeTypes());
    }
}
