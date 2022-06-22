<?php

/**
 * MedicalDevice class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\MedicalDevice;

use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Common\Logging\SystemLogger;

class MedicalDevice
{
    private $udi_data;

    // if we have other issuing agencies we can add them here.
    const ISSUING_AGENCY_FDA_AUTHORITY = ['GS1', 'HIBCC', 'ICCBBA'];

    public function __construct($udi_data)
    {
        $this->udi_data = json_decode($udi_data, true);
    }

    // This function returns the GMDN PT Name
    //  Not used yet, but an example of a getter call
    public function getGMDNPTName()
    {
        return $this->udi_data['standard_elements']['deviceName'];
    }

    public function fullOutputHtml($showUdi = true)
    {
        $html = '';
        if (!empty($this->udi_data['standard_elements']['deviceName'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Name (GMDN PT Name)') . ': </span>' . text($this->udi_data['standard_elements']['deviceName']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['deviceDescription'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Description') . ': </span>' . text($this->udi_data['standard_elements']['deviceDescription']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['brandName'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Brand Name') . ': </span>' . text($this->udi_data['standard_elements']['brandName']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['companyName'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Company Name') . ': </span>' . text($this->udi_data['standard_elements']['companyName']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['versionModelNumber'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Version/Model Number') . ': </span>' . text($this->udi_data['standard_elements']['versionModelNumber']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['di'])) {
            $html .= '<span class="font-weight-bold">' . xlt('DI (Device Identifier)') . ': </span>' . text($this->udi_data['standard_elements']['di']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['serialNumber'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Serial Number') . ': </span>' . text($this->udi_data['standard_elements']['serialNumber']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['lotNumber'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Lot Number') . ': </span>' . text($this->udi_data['standard_elements']['lotNumber']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['donationId'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Donation ID') . ': </span>' . text($this->udi_data['standard_elements']['donationId']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['expirationDate'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Expiration Date') . ': </span>' . text($this->udi_data['standard_elements']['expirationDate']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['manufacturingDate'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Manufacturing Date') . ': </span>' . text($this->udi_data['standard_elements']['manufacturingDate']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['MRISafetyStatus'])) {
            $html .= '<span class="font-weight-bold">' . xlt('MRI Safety Status') . ': </span>' . text($this->udi_data['standard_elements']['MRISafetyStatus']) . '<br>';
        }
        if (!empty($this->udi_data['standard_elements']['labeledContainsNRL'])) {
            $html .= '<span class="font-weight-bold">' . xlt('This device is required to be labeled as containing natural rubber latex or dry natural rubber.') . '</span><br>';
        }
        if (!empty($this->udi_data['standard_elements']['deviceHCTP'])) {
            $html .= '<span class="font-weight-bold">' . xlt('This device is labeled as a Human Cell, Tissue or Cellular or Tissue-Based Product (HCT/P).') . '</span><br>';
        }
        if (!empty($this->udi_data['standard_elements']['issuingAgency'])) {
            $html .= '<span class="font-weight-bold">' . xlt('Issuing Agency') . ': </span>' . text($this->udi_data['standard_elements']['issuingAgency']);
            $html .= '<br>';

            if (in_array($this->udi_data['standard_elements']['issuingAgency'], self::ISSUING_AGENCY_FDA_AUTHORITY)) {
                // we don't translate the FDA as its a US agency.
                $html .= '<span class="font-weight-bold">' . xlt('Assigning Authority') . ': </span>FDA<br />';
            }
        }
        if ($showUdi && !empty($this->udi_data['standard_elements']['udi'])) {
            $html .= '<span class="font-weight-bold">' . xlt('UDI (Unique Device Identifier)') . ': </span>' . text($this->udi_data['standard_elements']['udi']) . '<br>';
        }
        return $html;
    }

    public static function fullOutputJavascript($jsVar, $jsVal, $showUdi = true)
    {
        $js = '';
        $js .= 'if (' . $jsVal . '.standard_elements.deviceName) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Name (GMDN PT Name)")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.deviceName) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.deviceDescription) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Description")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.deviceDescription) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.brandName) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Brand Name")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.brandName) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.companyName) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Company Name")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.companyName) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.versionModelNumber) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Version/Model Number")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.versionModelNumber) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.di) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("DI (Device Identifier)")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.di) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.serialNumber) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Serial Number")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.serialNumber) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.lotNumber) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Lot Number")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.lotNumber) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.donationId) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Donation ID")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.donationId) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.expirationDate) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Expiration Date")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.expirationDate) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.manufacturingDate) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Manufacturing Date")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.manufacturingDate) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.MRISafetyStatus) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("MRI Safety Status")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.MRISafetyStatus) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.labeledContainsNRL) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("This device is required to be labeled as containing natural rubber latex or dry natural rubber.")) + \'</span><br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.deviceHCTP) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("This device is labeled as a Human Cell, Tissue or Cellular or Tissue-Based Product (HCT/P).")) + \'</span><br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.issuingAgency) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Issuing Agency")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.issuingAgency) + \'<br>\';}';
        $js .= 'if (' . $jsVal . '.standard_elements.issuingAuthority) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("Assigning Authority")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.issuingAuthority) + \'<br>\';}';
        if ($showUdi) {
            $js .= 'if (' . $jsVal . '.standard_elements.udi) {' . $jsVar . ' += \'<span class="font-weight-bold">\' + jsText(xl("UDI (Unique Device Identifier)")) + \': </span>\' + jsText(' . $jsVal . '.standard_elements.udi) + \'<br>\';}';
        }
        return $js;
    }

    public static function createStandardJson($udi)
    {
        $logger = new SystemLogger();

        # Request with udi and return result
        $logger->debug("MedicalDevice::createStandardJson will collect information for udi", ['udi' => $udi]);
        $response = oeHttp::get('https://accessgudid.nlm.nih.gov/api/v2/devices/lookup.json', ['udi' => $udi]);
        $data = $response->body();
        $udiData = json_decode($data, true);

        # Create standardized results and return this along with raw results
        $results = [
            'standard_elements' => [
                'udi' => $udiData['udi']['udi'],
                'di' => $udiData['udi']['di'],
                'serialNumber' => $udiData['udi']['serialNumber'],
                'lotNumber' => $udiData['udi']['lotNumber'],
                'donationId' => $udiData['udi']['donationId'],
                'expirationDate' => $udiData['udi']['expirationDate'],
                'manufacturingDate' => $udiData['udi']['manufacturingDate'],
                'deviceName' => $udiData['gudid']['device']['gmdnTerms']['gmdn'][0]['gmdnPTName'],
                'deviceDescription' => $udiData['gudid']['device']['deviceDescription'],
                'brandName' => $udiData['gudid']['device']['brandName'],
                'versionModelNumber' => $udiData['gudid']['device']['versionModelNumber'],
                'companyName' => $udiData['gudid']['device']['companyName'],
                'MRISafetyStatus' => $udiData['gudid']['device']['MRISafetyStatus'],
                'labeledContainsNRL' => $udiData['gudid']['device']['labeledContainsNRL'],
                'deviceHCTP' => $udiData['gudid']['device']['deviceHCTP'],
                'issuingAgency' => $udiData['udi']['issuingAgency']
                ,'issuingAuthority' => in_array($udiData['udi']['issuingAgency'], self::ISSUING_AGENCY_FDA_AUTHORITY) ? "FDA" : ""
            ],
            'raw_search' => $udiData
        ];
        $logger->debug("MedicalDevice::createStandardJson has collected information for udi and packaged it", ['package' => $results]);
        return json_encode($results);
    }
}
