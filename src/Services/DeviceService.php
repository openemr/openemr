<?php
/**
 * DeviceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;


use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class DeviceService extends BaseService
{
    private const DEVICE_TABLE = "lists";
    private $uuidRegistry;

    public function __construct()
    {
        parent::__construct('lists');
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::DEVICE_TABLE]);
        $this->uuidRegistry->createMissingUuids();
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "
            select l.*
            , patients.*
            from
            (
                SELECT 
                    `udi`,
                    `uuid`, `date`, `title`,`udi_data`, `begdate`, `diagnosis`, `user`, `pid`
                FROM lists WHERE `type` = 'medical_device'
            ) l
            JOIN (
                SELECT `pid`,`uuid` AS `puuid`
                from patient_data
            ) patients ON l.pid = patients.pid";

        $search = is_array($search) ? $search : [];

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults))
        {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        // handle any uuids
        $record = parent::createResultRecordFromDatabaseResult($row);

        $json = $record['udi_data'] ?? '{}';
        if (!empty($record['diagnosis']))
        {
            $record['code'] = $this->addCoding($record['diagnosis']);
            $record['code_full'] = $record['diagnosis'];
        }
        /**
         * {"standard_elements":{"udi":"(01)00643169007222(17)160128(21)BLC200461H","di":"00643169007222","serialNumber":"BLC200461H","lotNumber":null,"donationId":null,"expirationDate":"2016-01-28","manufacturingDate":null,"deviceName":"Cardiac resynchronization therapy implantable defibrillator","deviceDescription":"CRT-D DTBA1QQ VIVA QUAD XT US IS4/DF4","brandName":"Viva™ Quad XT CRT-D","versionModelNumber":"DTBA1QQ","companyName":"MEDTRONIC, INC.","MRISafetyStatus":"Labeling does not contain MRI Safety Information","labeledContainsNRL":false,"deviceHCTP":false,"issuingAgency":"GS1"},"raw_search":{"gudid":{"device":{"publicDeviceRecordKey":"cc0840a9-fc79-4cec-8f32-d07f0a3ea294","publicVersionStatus":"Update","deviceRecordStatus":"Published","publicVersionNumber":6,"publicVersionDate":"2021-02-05T00:00:00.000Z","devicePublishDate":"2014-09-23T00:00:00.000Z","deviceCommDistributionEndDate":null,"deviceCommDistributionStatus":"In Commercial Distribution","identifiers":{"identifier":[{"deviceId":"00643169007222","deviceIdType":"Primary","deviceIdIssuingAgency":"GS1","containsDINumber":null,"pkgQuantity":null,"pkgDiscontinueDate":null,"pkgStatus":null,"pkgType":null}]},"brandName":"Viva™ Quad XT CRT-D","versionModelNumber":"DTBA1QQ","catalogNumber":null,"dunsNumber":"006261481","companyName":"MEDTRONIC, INC.","deviceCount":1,"deviceDescription":"CRT-D DTBA1QQ VIVA QUAD XT US IS4/DF4","DMExempt":false,"premarketExempt":false,"deviceHCTP":false,"deviceKit":false,"deviceCombinationProduct":false,"singleUse":true,"lotBatch":false,"serialNumber":true,"manufacturingDate":false,"expirationDate":false,"donationIdNumber":false,"labeledContainsNRL":false,"labeledNoNRL":false,"MRISafetyStatus":"Labeling does not contain MRI Safety Information","rx":true,"otc":false,"contacts":{"customerContact":[{"phone":"+1(800)633-8766","phoneExtension":null,"email":"Corporate.UDI@medtronic.com"}]},"gmdnTerms":{"gmdn":[{"gmdnPTName":"Cardiac resynchronization therapy implantable defibrillator","gmdnPTDefinition":"An implantable, battery-powered device consisting of a hermetically-sealed pacing pulse generator and an integrated defibrillation pulse generator with leads in the right ventricle, in a coronary vein over the left ventricle, and often in the right atrium (triple chamber). In addition to conventional pacing and defibrillation functions, the device is intended to provide cardiac resynchronization therapy (CRT) through biventricular electrical stimulation to synchronize right and left ventricular contractions for more effective blood pumping to treat symptoms of heart failure (e.g., shortness of breath, easy fatigue) and serious heart-rhythm problems [CRT defibrillator (CRT-D)]."}]},"productCodes":{"fdaProductCode":[{"productCode":"NIK","productCodeName":"Defibrillator, automatic implantable cardioverter, with cardiac resynchronization (CRT-D)"},{"productCode":"KRG","productCodeName":"Programmer, pacemaker"}]},"deviceSizes":null,"environmentalConditions":{"storageHandling":[{"storageHandlingType":"Handling Environment Temperature","storageHandlingHigh":{"unit":"Degrees Celsius","value":"55"},"storageHandlingLow":{"unit":"Degrees Celsius","value":"-18"},"storageHandlingSpecialConditionText":null},{"storageHandlingType":"Handling Environment Temperature","storageHandlingHigh":{"unit":"Degrees Fahrenheit","value":"131"},"storageHandlingLow":{"unit":"Degrees Fahrenheit","value":"0"},"storageHandlingSpecialConditionText":null}]},"sterilization":{"deviceSterile":true,"sterilizationPriorToUse":false,"methodTypes":null}}},"productCodes":[{"productCode":"NIK","physicalState":"These devices will be indicated for patients needing an ICD who also have moderate to severe heart failure and are indicated for cardiac resynchronization therapy.","deviceClass":"3","thirdPartyFlag":"N","definition":"These devices will be indicated for patients needing an ICD who also have moderate to severe heart failure and are indicated for cardiac resynchronization therapy.","submissionTypeID":"2","reviewPanel":"CV","gmpExemptFlag":"N","technicalMethod":"These devices will be indicated for patients needing an ICD who also have moderate to severe heart failure and are indicated for cardiac resynchronization therapy.","reviewCode":null,"lifeSustainSupportFlag":"Y","unclassifiedReason":null,"implantFlag":"Y","targetArea":"These devices will be indicated for patients needing an ICD who also have moderate to severe heart failure and are indicated for cardiac resynchronization therapy.","regulationNumber":null,"deviceName":"Defibrillator, Automatic Implantable Cardioverter, With Cardiac Resynchronization (Crt-D)","medicalSpecialty":null},{"productCode":"KRG","physicalState":null,"deviceClass":"3","thirdPartyFlag":"N","definition":null,"submissionTypeID":"2","reviewPanel":"CV","gmpExemptFlag":"N","technicalMethod":null,"reviewCode":null,"lifeSustainSupportFlag":"N","unclassifiedReason":null,"implantFlag":"N","targetArea":null,"regulationNumber":"870.3700","deviceName":"Programmer, Pacemaker","medicalSpecialty":"CV"}],"udi":{"udi":"(01)00643169007222(17)160128(21)BLC200461H","issuingAgency":"GS1","di":"00643169007222","manufacturingDateOriginal":null,"expirationDateOriginal":"160128","expirationDateOriginalFormat":"YYMMDD","expirationDate":"2016-01-28","lotNumber":null,"serialNumber":"BLC200461H"}}}
         */
        try
        {
            $dataSet = json_decode($json, JSON_THROW_ON_ERROR);
            $standardElements = $dataSet['standard_elements'] ?? [];
            unset($record['udi_data']); // don't send back the JSON array
            $record['udi_di'] = $standardElements['di'] ?? null;
            // TODO: @adunsulag check with @brady.miller should this be companyName, or issuingAgency?
            $record['manufacturer'] = $standardElements['issuingAgency'] ?? null;
            $record['manufactureDate'] = $standardElements['manufacturingDate'] ?? null;
            $record['expirationDate'] = $standardElements['expirationDate'] ?? null;
            $record['lotNumber'] = $standardElements['lotNumber'] ?? null;
            $record['serialNumber'] = $standardElements['serialNumber'] ?? null;
            // @see https://www.accessdata.fda.gov/scripts/cdrh/cfdocs/cfcfr/CFRSearch.cfm?fr=1271.290 which describes
            // the distinct identification code which states this is the donor id.
            $record['distinctIdentifier'] = $standardElements['donationId'] ?? null;

        }
        catch (\JsonException $error)
        {
            (new SystemLogger())->error(self::class . "->createResultRecordFromDatabaseResult() failed to decode udi_data json ", ['message' => $error->getMessage(), 'trace' => $error->getTrace()]);
        }
        return $record;
    }
}