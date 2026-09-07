<?php

/**
 * VocabularyExtractor isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\VocabularyExtractor;
use PHPUnit\Framework\TestCase;

final class VocabularyExtractorTest extends TestCase
{
    private const VOCAB_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<systems xmlns="http://www.lantanagroup.com/voc">
    <system valueSetOid="1.2.3" valueSetName="First">
        <code value="A"/>
        <code value="B"/>
    </system>
    <system valueSetOid="4.5.6" valueSetName="Second">
        <code value="X"/>
    </system>
    <system valueSetOid="7.8.9" valueSetName="Unused">
        <code value="Q"/>
    </system>
</systems>
XML;

    private const SCHEMATRON = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
    <sch:pattern>
        <sch:rule context="x">
            <sch:assert test="@a=document('voc.xml')/voc:systems/voc:system[@valueSetOid='1.2.3']/voc:code/@value">a</sch:assert>
            <sch:assert test="@b=document('voc.xml')/voc:systems/voc:system[@valueSetOid='4.5.6']/voc:code/@value">b</sch:assert>
            <sch:assert test="@c=document('voc.xml')/voc:systems/voc:system[@valueSetOid='9.9.9']/voc:code/@value">c missing</sch:assert>
        </sch:rule>
    </sch:pattern>
</sch:schema>
XML;

    public function testExtractsOnlyReferencedOids(): void
    {
        $result = (new VocabularyExtractor())->extract(self::SCHEMATRON, self::VOCAB_XML);

        self::assertSame(['1.2.3', '4.5.6', '9.9.9'], $result['oids']);
        self::assertSame(['A', 'B'], $result['resolved']['1.2.3']);
        self::assertSame(['X'], $result['resolved']['4.5.6']);
        self::assertArrayNotHasKey('7.8.9', $result['resolved']);
        self::assertSame(['9.9.9'], $result['missing']);
    }

    public function testRenderProducesLoadablePhpFile(): void
    {
        $extractor = new VocabularyExtractor();
        $result = $extractor->extract(self::SCHEMATRON, self::VOCAB_XML);
        $php = $extractor->renderPhpFile($result['resolved'], 'test.sch', 'test-voc.xml');

        $tmp = tempnam(sys_get_temp_dir(), 'vocab');
        try {
            file_put_contents($tmp, $php);
            $reloaded = require $tmp;
            self::assertSame($result['resolved'], $reloaded);
        } finally {
            unlink($tmp);
        }
    }
}
