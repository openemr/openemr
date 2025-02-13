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
use Exception;

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

    /**
     * @return array
     */
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

    /**
     * @param $xmlFilePath
     * @return array
     * @throws Exception
     */
    public static function parseCcdaPatientRole($xmlFilePath): array
    {
        $patientData = [];

        $content = file_get_contents($xmlFilePath);
        // too me the most reliable way to parse an HL7 CDA is to use DOMXPath
        if (empty($content)) {
            return $patientData;
        }

        $dom = new DOMDocument();
        $dom->loadXML($content);
        $xpath = new DOMXPath($dom);

        //  a gotcha, HL7 uses a default namespace without a prefix
        $defaultNamespace = $dom->documentElement->lookupNamespaceURI(null);
        if ($defaultNamespace) {
            $xpath->registerNamespace('ns', $defaultNamespace); // Assign "ns" prefix to default namespace
        }

        // <patientRole> node
        $patientRoleNode = $xpath->query('//ns:recordTarget/ns:patientRole');
        if ($patientRoleNode->length === 0) {
            throw new Exception("No <patientRole> section found.");
        }
        $patientRole = $patientRoleNode->item(0);

        // patient CDA ID
        $idNode = $xpath->query('ns:id', $patientRole);
        $patientData['id'] = $idNode->length > 0 ? $idNode->item(0)->getAttribute('extension') : null;
        // Extract <patient> node
        $patientNode = $xpath->query('ns:patient', $patientRole);
        if ($patientNode->length === 0) {
            throw new Exception("No <patient> element found.");
        }
        $patientNode = $patientNode->item(0);

        // Names
        $nameNodes = $xpath->query('ns:name', $patientNode);
        foreach ($nameNodes as $nameNode) {
            $givenNames = [];
            foreach ($xpath->query('ns:given', $nameNode) as $given) {
                $givenNames[] = $given->nodeValue;
            }
            $patientData['names'][] = [
                'given' => $givenNames,
                'family' => $xpath->query('ns:family', $nameNode)->item(0)->nodeValue ?? ''
            ];
        }

        // DOB
        $birthTimeNode = $xpath->query('ns:birthTime', $patientNode);
        $patientData['dob'] = $birthTimeNode->length > 0 ? $birthTimeNode->item(0)->getAttribute('value') : null;

        $patientData['phones'] = [];
        foreach ($xpath->query('ns:telecom', $patientRole) as $telecom) {
            $patientData['phones'][] = $telecom->getAttribute('value');
        }

        // Extract address
        $addressNode = $xpath->query('ns:addr', $patientRole);
        if ($addressNode->length > 0) {
            $address = $addressNode->item(0);
            $streetLines = [];
            foreach ($xpath->query('ns:streetAddressLine', $address) as $street) {
                $streetLines[] = $street->nodeValue;
            }
            $patientData['address'] = [
                'street' => $streetLines,
                'city' => $xpath->query('ns:city', $address)->item(0)->nodeValue ?? '',
                'state' => $xpath->query('ns:state', $address)->item(0)->nodeValue ?? '',
                'zip' => $xpath->query('ns:postalCode', $address)->item(0)->nodeValue ?? '',
                'country' => $xpath->query('ns:country', $address)->item(0)->nodeValue ?? '',
            ];
        }

        return $patientData;
    }

    /**
     * @param $patientData
     * @return array
     */
    public static function checkDuplicatePatient($patientData): array
    {
        // Pretty strict duplicate check
        $firstName = $patientData['names'][0]['given'][0];
        $lastName = $patientData['names'][0]['family'];
        $dob = date("Y-m-d", strtotime($patientData['dob']));
        $phone = preg_replace('/\D/', '', $patientData['phones'][0]);
        $address = trim($patientData['address']['street'][0] ?? '');

        $sql = "SELECT pid, fname, lname, DOB, phone_home, phone_cell, phone_biz, street, city, state, postal_code
            FROM patient_data
            WHERE lname = ? 
            AND fname = ? 
            AND DOB = ? 
            AND (phone_home = ? OR phone_cell = ? OR phone_biz = ?)
            AND street LIKE ? 
            AND city = ? 
            AND state = ? 
            AND postal_code = ?";
        $sqlBindArray = [$lastName, $firstName, $dob, $phone, $phone, $phone, "%$address%",
            $patientData['address']['city'], $patientData['address']['state'], $patientData['address']['zip']];

        $result = sqlStatement($sql, $sqlBindArray);
        $duplicates = [];
        while ($row = sqlFetchArray($result)) {
            $duplicates[] = $row;
        }

        return $duplicates;
    }

    /**
     * @param $sourceFile
     * @param $duplicateDir
     * @return int|void
     * @throws Exception
     */
    public static function moveToDuplicateDir($sourceFile, $duplicateDir)
    {
        global $enableMoves;
        if (!$enableMoves) {
            return;
        }

        if (!file_exists($sourceFile)) {
            throw new Exception("Source file does not exist: $sourceFile");
        }

        if (!is_dir($duplicateDir)) {
            mkdir($duplicateDir, 0777, true);
        }

        $fileName = basename($sourceFile);
        $destinationFilePath = rtrim($duplicateDir, '/') . '/' . $fileName;
        // Move
        if (!rename($sourceFile, $destinationFilePath)) {
            throw new Exception("Failed to move file to: $destinationFilePath");
        }

        return 1;
    }
}
