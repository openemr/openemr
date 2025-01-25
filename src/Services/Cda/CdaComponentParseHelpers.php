<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use DOMDocument;
use DOMXPath;

class CdaComponentParseHelpers
{
    private $dom;
    private $xpath;

    public function __construct(string $rawXmlContent)
    {
        $this->dom = new DOMDocument();
        $this->dom->loadXML($rawXmlContent);
        $this->xpath = new DOMXPath($this->dom);

        $namespaces = $this->dom->documentElement->lookupNamespaceURI(null);
        if ($namespaces) {
            $this->xpath->registerNamespace('ns', $namespaces);
        }
    }

    public function parseGuardianParticipant(): array
    {
        $guardianData = [];

        // Locate <participant> element with associatedEntity classCode="GUAR"
        $participant = $this->xpath->query("//ns:participant[@typeCode='IND']/ns:associatedEntity[@classCode='GUAR']")->item(0);
        if (!$participant) {
            return $guardianData; // Return empty if no guardian found
        }

        $addressNode = $this->xpath->query(".//ns:addr", $participant)->item(0);
        if ($addressNode) {
            $guardianData['address'] = [
                'street' => $this->xpath->query(".//ns:streetAddressLine", $addressNode)->item(0)?->nodeValue ?? '',
                'city' => $this->xpath->query(".//ns:city", $addressNode)->item(0)?->nodeValue ?? '',
                'state' => $this->xpath->query(".//ns:state", $addressNode)->item(0)?->nodeValue ?? '',
                'postalCode' => $this->xpath->query(".//ns:postalCode", $addressNode)->item(0)?->nodeValue ?? '',
                'country' => $this->xpath->query(".//ns:country", $addressNode)->item(0)?->nodeValue ?? '',
            ];
        }

        $telecomNodes = $this->xpath->query(".//ns:telecom", $participant);
        $guardianData['contact'] = [];
        foreach ($telecomNodes as $telecom) {
            $use = $telecom->getAttribute('use');
            $value = str_replace('tel:', '', $telecom->getAttribute('value'));
            $guardianData['contact'][$use] = $value;
        }

        $nameNode = $this->xpath->query(".//ns:associatedPerson/ns:name", $participant)->item(0);
        if ($nameNode) {
            $guardianData['name'] = [
                'given' => array_map(
                    fn($node) => $node->nodeValue,
                    iterator_to_array($this->xpath->query(".//ns:given", $nameNode))
                ),
                'family' => $this->xpath->query(".//ns:family", $nameNode)->item(0)?->nodeValue ?? '',
            ];
        }

        return $guardianData;
    }
}
