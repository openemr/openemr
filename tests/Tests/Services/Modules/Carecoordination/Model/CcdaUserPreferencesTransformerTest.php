<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\CareCoordination\Model;

use Carecoordination\Model\CcdaUserPreferencesTransformer;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

class CcdaUserPreferencesTransformerTest extends TestCase
{
    private const NS = 'urn:hl7-org:v3';
    private const DOC_OID = '2.16.840.1.113883.10.20.22.1.2';
    private const ALLERGIES_OID = '2.16.840.1.113883.10.20.22.2.6.1';
    private const MEDS_OID = '2.16.840.1.113883.10.20.22.2.1.1';
    private const PROBLEMS_OID = '2.16.840.1.113883.10.20.22.2.5.1';

    public function testTransformReordersSectionsByPreference(): void
    {
        $xml = $this->buildCcda([self::ALLERGIES_OID, self::MEDS_OID, self::PROBLEMS_OID]);

        $transformer = new CcdaUserPreferencesTransformer(
            maxSections: 0,
            sortPreferences: [self::DOC_OID => [self::PROBLEMS_OID, self::MEDS_OID]],
        );

        $this->assertSame(
            [self::PROBLEMS_OID, self::MEDS_OID, self::ALLERGIES_OID],
            $this->extractSectionOrder($transformer->transform($xml)),
        );
    }

    public function testTransformTruncatesToMaxSections(): void
    {
        $xml = $this->buildCcda([self::ALLERGIES_OID, self::MEDS_OID, self::PROBLEMS_OID]);

        $transformer = new CcdaUserPreferencesTransformer(maxSections: 2);

        $this->assertCount(2, $this->extractSectionOrder($transformer->transform($xml)));
    }

    public function testTransformAppliesDefaultSortWhenDocTypeUnknown(): void
    {
        $xml = $this->buildCcda([self::ALLERGIES_OID, self::MEDS_OID]);

        $transformer = new CcdaUserPreferencesTransformer(
            maxSections: 0,
            sortPreferences: ['default' => [self::MEDS_OID]],
        );

        $this->assertSame(
            [self::MEDS_OID, self::ALLERGIES_OID],
            $this->extractSectionOrder($transformer->transform($xml)),
        );
    }

    public function testGettersAndSettersRoundTrip(): void
    {
        $transformer = new CcdaUserPreferencesTransformer();

        $transformer->setMaxSections(7)->setSortPreferences(['a' => ['b']]);

        $this->assertSame(7, $transformer->getMaxSections());
        $this->assertSame(['a' => ['b']], $transformer->getSortPreferences());
    }

    /**
     * @param list<string> $sectionOids
     */
    private function buildCcda(array $sectionOids): string
    {
        $sections = '';
        foreach ($sectionOids as $oid) {
            $sections .= "<component><section><templateId root=\"$oid\"/></section></component>";
        }

        $doc = self::DOC_OID;
        $ns = self::NS;
        return <<<XML
            <?xml version="1.0"?>
            <ClinicalDocument xmlns="$ns">
                <templateId root="$doc"/>
                <component>
                    <structuredBody>$sections</structuredBody>
                </component>
            </ClinicalDocument>
            XML;
    }

    /**
     * @return list<string>
     */
    private function extractSectionOrder(mixed $xml): array
    {
        $this->assertIsString($xml);

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('n1', self::NS);

        $nodes = $xpath->query('//n1:structuredBody/n1:component/n1:section/n1:templateId');
        $this->assertNotFalse($nodes);

        $oids = [];
        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) {
                $oids[] = $node->getAttribute('root');
            }
        }
        return $oids;
    }
}
