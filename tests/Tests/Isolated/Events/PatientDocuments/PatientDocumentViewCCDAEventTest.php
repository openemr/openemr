<?php

/**
 * Isolated tests for PatientDocumentViewCCDAEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events\PatientDocuments;

use OpenEMR\Events\PatientDocuments\PatientDocumentViewCCDAEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientDocumentViewCCDAEventTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $event = new PatientDocumentViewCCDAEvent();

        $this->assertSame('html', $event->getFormat());
        $this->assertFalse($event->shouldIgnoreUserPreferences());
        $this->assertSame('', $event->getStylesheetPath());
        $this->assertSame(0, $event->getCcdaId());
        $this->assertSame(0, $event->getDocumentId());
    }

    public function testSettersReturnSelf(): void
    {
        $event = new PatientDocumentViewCCDAEvent();

        $this->assertSame($event, $event->setCcdaId(1));
        $this->assertSame($event, $event->setDocumentId(2));
        $this->assertSame($event, $event->setCcdaType('CCD'));
        $this->assertSame($event, $event->setContent('<xml/>'));
        $this->assertSame($event, $event->setFormat('xml'));
        $this->assertSame($event, $event->setStylesheetPath('/path'));
        $this->assertSame($event, $event->setIgnoreUserPreferences(true));
    }

    public function testBuilderPatternChaining(): void
    {
        $event = (new PatientDocumentViewCCDAEvent())
            ->setCcdaId(10)
            ->setDocumentId(20)
            ->setCcdaType('Referral')
            ->setContent('<doc/>')
            ->setFormat('xml')
            ->setStylesheetPath('/style.xsl')
            ->setIgnoreUserPreferences(true);

        $this->assertSame(10, $event->getCcdaId());
        $this->assertSame(20, $event->getDocumentId());
        $this->assertSame('Referral', $event->getCcdaType());
        $this->assertSame('<doc/>', $event->getContent());
        $this->assertSame('xml', $event->getFormat());
        $this->assertSame('/style.xsl', $event->getStylesheetPath());
        $this->assertTrue($event->shouldIgnoreUserPreferences());
    }

    public function testShouldIgnoreUserPreferencesDefaultIsFalse(): void
    {
        $event = new PatientDocumentViewCCDAEvent();
        $this->assertFalse($event->shouldIgnoreUserPreferences());
    }
}
