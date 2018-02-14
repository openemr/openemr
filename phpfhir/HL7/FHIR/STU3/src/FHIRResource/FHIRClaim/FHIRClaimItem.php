<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRClaim;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A provider issued list of services and products provided, or to be provided, to a patient which is provided to an insurer for payment recovery.
 */
class FHIRClaimItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A service line number.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $sequence = null;

    /**
     * CareTeam applicable for this service or product line.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public $careTeamLinkId = [];

    /**
     * Diagnosis applicable for this service or product line.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public $diagnosisLinkId = [];

    /**
     * Procedures applicable for this service or product line.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public $procedureLinkId = [];

    /**
     * Exceptions, special conditions and supporting information pplicable for this service or product line.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public $informationLinkId = [];

    /**
     * The type of reveneu or cost center providing the product and/or service.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $revenue = null;

    /**
     * Health Care Service Type Codes  to identify the classification of service or benefits.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * If this is an actual service or product line, ie. not a Group, then use code to indicate the Professional Service or Product supplied (eg. CTP, HCPCS,USCLS,ICD10, NCPDP,DIN,RXNorm,ACHI,CCI). If a grouping item then use a group code to indicate the type of thing being grouped eg. 'glasses' or 'compound'.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $service = null;

    /**
     * Item typification or modifiers codes, eg for Oral whether the treatment is cosmetic or associated with TMJ, or for medical whether the treatment was outside the clinic or out of office hours.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $modifier = [];

    /**
     * For programs which require reason codes for the inclusion or covering of this billed item under the program or sub-program.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $programCode = [];

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $servicedDate = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $servicedPeriod = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $locationCodeableConcept = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAddress
     */
    public $locationAddress = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $locationReference = null;

    /**
     * The number of repetitions of a service or product.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * If the item is a node then this is the fee for the product or service, otherwise this is the total of the fees for the children of the group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $unitPrice = null;

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $factor = null;

    /**
     * The quantity times the unit price for an addittional service or product or charge. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $net = null;

    /**
     * List of Unique Device Identifiers associated with this line item.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $udi = [];

    /**
     * Physical service site on the patient (limb, tooth, etc).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $bodySite = null;

    /**
     * A region or surface of the site, eg. limb region or tooth surface(s).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $subSite = [];

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $encounter = [];

    /**
     * Second tier of goods and services.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRClaim\FHIRClaimDetail[]
     */
    public $detail = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Claim.Item';

    /**
     * A service line number.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * A service line number.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * CareTeam applicable for this service or product line.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public function getCareTeamLinkId()
    {
        return $this->careTeamLinkId;
    }

    /**
     * CareTeam applicable for this service or product line.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $careTeamLinkId
     * @return $this
     */
    public function addCareTeamLinkId($careTeamLinkId)
    {
        $this->careTeamLinkId[] = $careTeamLinkId;
        return $this;
    }

    /**
     * Diagnosis applicable for this service or product line.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public function getDiagnosisLinkId()
    {
        return $this->diagnosisLinkId;
    }

    /**
     * Diagnosis applicable for this service or product line.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $diagnosisLinkId
     * @return $this
     */
    public function addDiagnosisLinkId($diagnosisLinkId)
    {
        $this->diagnosisLinkId[] = $diagnosisLinkId;
        return $this;
    }

    /**
     * Procedures applicable for this service or product line.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public function getProcedureLinkId()
    {
        return $this->procedureLinkId;
    }

    /**
     * Procedures applicable for this service or product line.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $procedureLinkId
     * @return $this
     */
    public function addProcedureLinkId($procedureLinkId)
    {
        $this->procedureLinkId[] = $procedureLinkId;
        return $this;
    }

    /**
     * Exceptions, special conditions and supporting information pplicable for this service or product line.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt[]
     */
    public function getInformationLinkId()
    {
        return $this->informationLinkId;
    }

    /**
     * Exceptions, special conditions and supporting information pplicable for this service or product line.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $informationLinkId
     * @return $this
     */
    public function addInformationLinkId($informationLinkId)
    {
        $this->informationLinkId[] = $informationLinkId;
        return $this;
    }

    /**
     * The type of reveneu or cost center providing the product and/or service.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * The type of reveneu or cost center providing the product and/or service.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $revenue
     * @return $this
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
        return $this;
    }

    /**
     * Health Care Service Type Codes  to identify the classification of service or benefits.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Health Care Service Type Codes  to identify the classification of service or benefits.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * If this is an actual service or product line, ie. not a Group, then use code to indicate the Professional Service or Product supplied (eg. CTP, HCPCS,USCLS,ICD10, NCPDP,DIN,RXNorm,ACHI,CCI). If a grouping item then use a group code to indicate the type of thing being grouped eg. 'glasses' or 'compound'.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * If this is an actual service or product line, ie. not a Group, then use code to indicate the Professional Service or Product supplied (eg. CTP, HCPCS,USCLS,ICD10, NCPDP,DIN,RXNorm,ACHI,CCI). If a grouping item then use a group code to indicate the type of thing being grouped eg. 'glasses' or 'compound'.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Item typification or modifiers codes, eg for Oral whether the treatment is cosmetic or associated with TMJ, or for medical whether the treatment was outside the clinic or out of office hours.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Item typification or modifiers codes, eg for Oral whether the treatment is cosmetic or associated with TMJ, or for medical whether the treatment was outside the clinic or out of office hours.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $modifier
     * @return $this
     */
    public function addModifier($modifier)
    {
        $this->modifier[] = $modifier;
        return $this;
    }

    /**
     * For programs which require reason codes for the inclusion or covering of this billed item under the program or sub-program.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProgramCode()
    {
        return $this->programCode;
    }

    /**
     * For programs which require reason codes for the inclusion or covering of this billed item under the program or sub-program.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $programCode
     * @return $this
     */
    public function addProgramCode($programCode)
    {
        $this->programCode[] = $programCode;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getServicedDate()
    {
        return $this->servicedDate;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $servicedDate
     * @return $this
     */
    public function setServicedDate($servicedDate)
    {
        $this->servicedDate = $servicedDate;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getServicedPeriod()
    {
        return $this->servicedPeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $servicedPeriod
     * @return $this
     */
    public function setServicedPeriod($servicedPeriod)
    {
        $this->servicedPeriod = $servicedPeriod;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getLocationCodeableConcept()
    {
        return $this->locationCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $locationCodeableConcept
     * @return $this
     */
    public function setLocationCodeableConcept($locationCodeableConcept)
    {
        $this->locationCodeableConcept = $locationCodeableConcept;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAddress
     */
    public function getLocationAddress()
    {
        return $this->locationAddress;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAddress $locationAddress
     * @return $this
     */
    public function setLocationAddress($locationAddress)
    {
        $this->locationAddress = $locationAddress;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getLocationReference()
    {
        return $this->locationReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $locationReference
     * @return $this
     */
    public function setLocationReference($locationReference)
    {
        $this->locationReference = $locationReference;
        return $this;
    }

    /**
     * The number of repetitions of a service or product.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The number of repetitions of a service or product.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * If the item is a node then this is the fee for the product or service, otherwise this is the total of the fees for the children of the group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * If the item is a node then this is the fee for the product or service, otherwise this is the total of the fees for the children of the group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of services delivered and/or goods received. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $factor
     * @return $this
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * The quantity times the unit price for an addittional service or product or charge. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getNet()
    {
        return $this->net;
    }

    /**
     * The quantity times the unit price for an addittional service or product or charge. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $net
     * @return $this
     */
    public function setNet($net)
    {
        $this->net = $net;
        return $this;
    }

    /**
     * List of Unique Device Identifiers associated with this line item.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getUdi()
    {
        return $this->udi;
    }

    /**
     * List of Unique Device Identifiers associated with this line item.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $udi
     * @return $this
     */
    public function addUdi($udi)
    {
        $this->udi[] = $udi;
        return $this;
    }

    /**
     * Physical service site on the patient (limb, tooth, etc).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * Physical service site on the patient (limb, tooth, etc).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $bodySite
     * @return $this
     */
    public function setBodySite($bodySite)
    {
        $this->bodySite = $bodySite;
        return $this;
    }

    /**
     * A region or surface of the site, eg. limb region or tooth surface(s).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubSite()
    {
        return $this->subSite;
    }

    /**
     * A region or surface of the site, eg. limb region or tooth surface(s).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $subSite
     * @return $this
     */
    public function addSubSite($subSite)
    {
        $this->subSite[] = $subSite;
        return $this;
    }

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * A billed item may include goods or services provided in multiple encounters.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function addEncounter($encounter)
    {
        $this->encounter[] = $encounter;
        return $this;
    }

    /**
     * Second tier of goods and services.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRClaim\FHIRClaimDetail[]
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Second tier of goods and services.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRClaim\FHIRClaimDetail $detail
     * @return $this
     */
    public function addDetail($detail)
    {
        $this->detail[] = $detail;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['sequence'])) {
                $this->setSequence($data['sequence']);
            }
            if (isset($data['careTeamLinkId'])) {
                if (is_array($data['careTeamLinkId'])) {
                    foreach ($data['careTeamLinkId'] as $d) {
                        $this->addCareTeamLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"careTeamLinkId" must be array of objects or null, '.gettype($data['careTeamLinkId']).' seen.');
                }
            }
            if (isset($data['diagnosisLinkId'])) {
                if (is_array($data['diagnosisLinkId'])) {
                    foreach ($data['diagnosisLinkId'] as $d) {
                        $this->addDiagnosisLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"diagnosisLinkId" must be array of objects or null, '.gettype($data['diagnosisLinkId']).' seen.');
                }
            }
            if (isset($data['procedureLinkId'])) {
                if (is_array($data['procedureLinkId'])) {
                    foreach ($data['procedureLinkId'] as $d) {
                        $this->addProcedureLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"procedureLinkId" must be array of objects or null, '.gettype($data['procedureLinkId']).' seen.');
                }
            }
            if (isset($data['informationLinkId'])) {
                if (is_array($data['informationLinkId'])) {
                    foreach ($data['informationLinkId'] as $d) {
                        $this->addInformationLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"informationLinkId" must be array of objects or null, '.gettype($data['informationLinkId']).' seen.');
                }
            }
            if (isset($data['revenue'])) {
                $this->setRevenue($data['revenue']);
            }
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['service'])) {
                $this->setService($data['service']);
            }
            if (isset($data['modifier'])) {
                if (is_array($data['modifier'])) {
                    foreach ($data['modifier'] as $d) {
                        $this->addModifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modifier" must be array of objects or null, '.gettype($data['modifier']).' seen.');
                }
            }
            if (isset($data['programCode'])) {
                if (is_array($data['programCode'])) {
                    foreach ($data['programCode'] as $d) {
                        $this->addProgramCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"programCode" must be array of objects or null, '.gettype($data['programCode']).' seen.');
                }
            }
            if (isset($data['servicedDate'])) {
                $this->setServicedDate($data['servicedDate']);
            }
            if (isset($data['servicedPeriod'])) {
                $this->setServicedPeriod($data['servicedPeriod']);
            }
            if (isset($data['locationCodeableConcept'])) {
                $this->setLocationCodeableConcept($data['locationCodeableConcept']);
            }
            if (isset($data['locationAddress'])) {
                $this->setLocationAddress($data['locationAddress']);
            }
            if (isset($data['locationReference'])) {
                $this->setLocationReference($data['locationReference']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['unitPrice'])) {
                $this->setUnitPrice($data['unitPrice']);
            }
            if (isset($data['factor'])) {
                $this->setFactor($data['factor']);
            }
            if (isset($data['net'])) {
                $this->setNet($data['net']);
            }
            if (isset($data['udi'])) {
                if (is_array($data['udi'])) {
                    foreach ($data['udi'] as $d) {
                        $this->addUdi($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"udi" must be array of objects or null, '.gettype($data['udi']).' seen.');
                }
            }
            if (isset($data['bodySite'])) {
                $this->setBodySite($data['bodySite']);
            }
            if (isset($data['subSite'])) {
                if (is_array($data['subSite'])) {
                    foreach ($data['subSite'] as $d) {
                        $this->addSubSite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subSite" must be array of objects or null, '.gettype($data['subSite']).' seen.');
                }
            }
            if (isset($data['encounter'])) {
                if (is_array($data['encounter'])) {
                    foreach ($data['encounter'] as $d) {
                        $this->addEncounter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"encounter" must be array of objects or null, '.gettype($data['encounter']).' seen.');
                }
            }
            if (isset($data['detail'])) {
                if (is_array($data['detail'])) {
                    foreach ($data['detail'] as $d) {
                        $this->addDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"detail" must be array of objects or null, '.gettype($data['detail']).' seen.');
                }
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->sequence)) {
            $json['sequence'] = $this->sequence;
        }
        if (0 < count($this->careTeamLinkId)) {
            $json['careTeamLinkId'] = [];
            foreach ($this->careTeamLinkId as $careTeamLinkId) {
                $json['careTeamLinkId'][] = $careTeamLinkId;
            }
        }
        if (0 < count($this->diagnosisLinkId)) {
            $json['diagnosisLinkId'] = [];
            foreach ($this->diagnosisLinkId as $diagnosisLinkId) {
                $json['diagnosisLinkId'][] = $diagnosisLinkId;
            }
        }
        if (0 < count($this->procedureLinkId)) {
            $json['procedureLinkId'] = [];
            foreach ($this->procedureLinkId as $procedureLinkId) {
                $json['procedureLinkId'][] = $procedureLinkId;
            }
        }
        if (0 < count($this->informationLinkId)) {
            $json['informationLinkId'] = [];
            foreach ($this->informationLinkId as $informationLinkId) {
                $json['informationLinkId'][] = $informationLinkId;
            }
        }
        if (isset($this->revenue)) {
            $json['revenue'] = $this->revenue;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->service)) {
            $json['service'] = $this->service;
        }
        if (0 < count($this->modifier)) {
            $json['modifier'] = [];
            foreach ($this->modifier as $modifier) {
                $json['modifier'][] = $modifier;
            }
        }
        if (0 < count($this->programCode)) {
            $json['programCode'] = [];
            foreach ($this->programCode as $programCode) {
                $json['programCode'][] = $programCode;
            }
        }
        if (isset($this->servicedDate)) {
            $json['servicedDate'] = $this->servicedDate;
        }
        if (isset($this->servicedPeriod)) {
            $json['servicedPeriod'] = $this->servicedPeriod;
        }
        if (isset($this->locationCodeableConcept)) {
            $json['locationCodeableConcept'] = $this->locationCodeableConcept;
        }
        if (isset($this->locationAddress)) {
            $json['locationAddress'] = $this->locationAddress;
        }
        if (isset($this->locationReference)) {
            $json['locationReference'] = $this->locationReference;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->unitPrice)) {
            $json['unitPrice'] = $this->unitPrice;
        }
        if (isset($this->factor)) {
            $json['factor'] = $this->factor;
        }
        if (isset($this->net)) {
            $json['net'] = $this->net;
        }
        if (0 < count($this->udi)) {
            $json['udi'] = [];
            foreach ($this->udi as $udi) {
                $json['udi'][] = $udi;
            }
        }
        if (isset($this->bodySite)) {
            $json['bodySite'] = $this->bodySite;
        }
        if (0 < count($this->subSite)) {
            $json['subSite'] = [];
            foreach ($this->subSite as $subSite) {
                $json['subSite'][] = $subSite;
            }
        }
        if (0 < count($this->encounter)) {
            $json['encounter'] = [];
            foreach ($this->encounter as $encounter) {
                $json['encounter'][] = $encounter;
            }
        }
        if (0 < count($this->detail)) {
            $json['detail'] = [];
            foreach ($this->detail as $detail) {
                $json['detail'][] = $detail;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<ClaimItem xmlns="http://hl7.org/fhir"></ClaimItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequence)) {
            $this->sequence->xmlSerialize(true, $sxe->addChild('sequence'));
        }
        if (0 < count($this->careTeamLinkId)) {
            foreach ($this->careTeamLinkId as $careTeamLinkId) {
                $careTeamLinkId->xmlSerialize(true, $sxe->addChild('careTeamLinkId'));
            }
        }
        if (0 < count($this->diagnosisLinkId)) {
            foreach ($this->diagnosisLinkId as $diagnosisLinkId) {
                $diagnosisLinkId->xmlSerialize(true, $sxe->addChild('diagnosisLinkId'));
            }
        }
        if (0 < count($this->procedureLinkId)) {
            foreach ($this->procedureLinkId as $procedureLinkId) {
                $procedureLinkId->xmlSerialize(true, $sxe->addChild('procedureLinkId'));
            }
        }
        if (0 < count($this->informationLinkId)) {
            foreach ($this->informationLinkId as $informationLinkId) {
                $informationLinkId->xmlSerialize(true, $sxe->addChild('informationLinkId'));
            }
        }
        if (isset($this->revenue)) {
            $this->revenue->xmlSerialize(true, $sxe->addChild('revenue'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->service)) {
            $this->service->xmlSerialize(true, $sxe->addChild('service'));
        }
        if (0 < count($this->modifier)) {
            foreach ($this->modifier as $modifier) {
                $modifier->xmlSerialize(true, $sxe->addChild('modifier'));
            }
        }
        if (0 < count($this->programCode)) {
            foreach ($this->programCode as $programCode) {
                $programCode->xmlSerialize(true, $sxe->addChild('programCode'));
            }
        }
        if (isset($this->servicedDate)) {
            $this->servicedDate->xmlSerialize(true, $sxe->addChild('servicedDate'));
        }
        if (isset($this->servicedPeriod)) {
            $this->servicedPeriod->xmlSerialize(true, $sxe->addChild('servicedPeriod'));
        }
        if (isset($this->locationCodeableConcept)) {
            $this->locationCodeableConcept->xmlSerialize(true, $sxe->addChild('locationCodeableConcept'));
        }
        if (isset($this->locationAddress)) {
            $this->locationAddress->xmlSerialize(true, $sxe->addChild('locationAddress'));
        }
        if (isset($this->locationReference)) {
            $this->locationReference->xmlSerialize(true, $sxe->addChild('locationReference'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->unitPrice)) {
            $this->unitPrice->xmlSerialize(true, $sxe->addChild('unitPrice'));
        }
        if (isset($this->factor)) {
            $this->factor->xmlSerialize(true, $sxe->addChild('factor'));
        }
        if (isset($this->net)) {
            $this->net->xmlSerialize(true, $sxe->addChild('net'));
        }
        if (0 < count($this->udi)) {
            foreach ($this->udi as $udi) {
                $udi->xmlSerialize(true, $sxe->addChild('udi'));
            }
        }
        if (isset($this->bodySite)) {
            $this->bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
        }
        if (0 < count($this->subSite)) {
            foreach ($this->subSite as $subSite) {
                $subSite->xmlSerialize(true, $sxe->addChild('subSite'));
            }
        }
        if (0 < count($this->encounter)) {
            foreach ($this->encounter as $encounter) {
                $encounter->xmlSerialize(true, $sxe->addChild('encounter'));
            }
        }
        if (0 < count($this->detail)) {
            foreach ($this->detail as $detail) {
                $detail->xmlSerialize(true, $sxe->addChild('detail'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
