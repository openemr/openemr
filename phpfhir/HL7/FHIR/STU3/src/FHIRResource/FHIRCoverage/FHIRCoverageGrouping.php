<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRCoverage;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Financial instrument which may be used to reimburse or pay for health care products and services.
 */
class FHIRCoverageGrouping extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify an employer group. May also be referred to as a Policy or Group ID.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $group = null;

    /**
     * A short description for the group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $groupDisplay = null;

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a subset of an employer group.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subGroup = null;

    /**
     * A short description for the subgroup.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subGroupDisplay = null;

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a collection of benefits provided to employees. May be referred to as a Section or Division ID.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $plan = null;

    /**
     * A short description for the plan.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $planDisplay = null;

    /**
     * Identifies a sub-style or sub-collective of coverage issued by the underwriter, for example may be used to identify a subset of a collection of benefits provided to employees.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subPlan = null;

    /**
     * A short description for the subplan.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subPlanDisplay = null;

    /**
     * Identifies a style or collective of coverage issues by the underwriter, for example may be used to identify a class of coverage such as a level of deductables or co-payment.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $class = null;

    /**
     * A short description for the class.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $classDisplay = null;

    /**
     * Identifies a sub-style or sub-collective of coverage issues by the underwriter, for example may be used to identify a subclass of coverage such as a sub-level of deductables or co-payment.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subClass = null;

    /**
     * A short description for the subclass.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $subClassDisplay = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Coverage.Grouping';

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify an employer group. May also be referred to as a Policy or Group ID.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify an employer group. May also be referred to as a Policy or Group ID.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * A short description for the group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getGroupDisplay()
    {
        return $this->groupDisplay;
    }

    /**
     * A short description for the group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $groupDisplay
     * @return $this
     */
    public function setGroupDisplay($groupDisplay)
    {
        $this->groupDisplay = $groupDisplay;
        return $this;
    }

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a subset of an employer group.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubGroup()
    {
        return $this->subGroup;
    }

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a subset of an employer group.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subGroup
     * @return $this
     */
    public function setSubGroup($subGroup)
    {
        $this->subGroup = $subGroup;
        return $this;
    }

    /**
     * A short description for the subgroup.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubGroupDisplay()
    {
        return $this->subGroupDisplay;
    }

    /**
     * A short description for the subgroup.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subGroupDisplay
     * @return $this
     */
    public function setSubGroupDisplay($subGroupDisplay)
    {
        $this->subGroupDisplay = $subGroupDisplay;
        return $this;
    }

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a collection of benefits provided to employees. May be referred to as a Section or Division ID.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Identifies a style or collective of coverage issued by the underwriter, for example may be used to identify a collection of benefits provided to employees. May be referred to as a Section or Division ID.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $plan
     * @return $this
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
        return $this;
    }

    /**
     * A short description for the plan.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPlanDisplay()
    {
        return $this->planDisplay;
    }

    /**
     * A short description for the plan.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $planDisplay
     * @return $this
     */
    public function setPlanDisplay($planDisplay)
    {
        $this->planDisplay = $planDisplay;
        return $this;
    }

    /**
     * Identifies a sub-style or sub-collective of coverage issued by the underwriter, for example may be used to identify a subset of a collection of benefits provided to employees.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubPlan()
    {
        return $this->subPlan;
    }

    /**
     * Identifies a sub-style or sub-collective of coverage issued by the underwriter, for example may be used to identify a subset of a collection of benefits provided to employees.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subPlan
     * @return $this
     */
    public function setSubPlan($subPlan)
    {
        $this->subPlan = $subPlan;
        return $this;
    }

    /**
     * A short description for the subplan.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubPlanDisplay()
    {
        return $this->subPlanDisplay;
    }

    /**
     * A short description for the subplan.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subPlanDisplay
     * @return $this
     */
    public function setSubPlanDisplay($subPlanDisplay)
    {
        $this->subPlanDisplay = $subPlanDisplay;
        return $this;
    }

    /**
     * Identifies a style or collective of coverage issues by the underwriter, for example may be used to identify a class of coverage such as a level of deductables or co-payment.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Identifies a style or collective of coverage issues by the underwriter, for example may be used to identify a class of coverage such as a level of deductables or co-payment.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * A short description for the class.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getClassDisplay()
    {
        return $this->classDisplay;
    }

    /**
     * A short description for the class.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $classDisplay
     * @return $this
     */
    public function setClassDisplay($classDisplay)
    {
        $this->classDisplay = $classDisplay;
        return $this;
    }

    /**
     * Identifies a sub-style or sub-collective of coverage issues by the underwriter, for example may be used to identify a subclass of coverage such as a sub-level of deductables or co-payment.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubClass()
    {
        return $this->subClass;
    }

    /**
     * Identifies a sub-style or sub-collective of coverage issues by the underwriter, for example may be used to identify a subclass of coverage such as a sub-level of deductables or co-payment.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subClass
     * @return $this
     */
    public function setSubClass($subClass)
    {
        $this->subClass = $subClass;
        return $this;
    }

    /**
     * A short description for the subclass.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSubClassDisplay()
    {
        return $this->subClassDisplay;
    }

    /**
     * A short description for the subclass.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $subClassDisplay
     * @return $this
     */
    public function setSubClassDisplay($subClassDisplay)
    {
        $this->subClassDisplay = $subClassDisplay;
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
            if (isset($data['group'])) {
                $this->setGroup($data['group']);
            }
            if (isset($data['groupDisplay'])) {
                $this->setGroupDisplay($data['groupDisplay']);
            }
            if (isset($data['subGroup'])) {
                $this->setSubGroup($data['subGroup']);
            }
            if (isset($data['subGroupDisplay'])) {
                $this->setSubGroupDisplay($data['subGroupDisplay']);
            }
            if (isset($data['plan'])) {
                $this->setPlan($data['plan']);
            }
            if (isset($data['planDisplay'])) {
                $this->setPlanDisplay($data['planDisplay']);
            }
            if (isset($data['subPlan'])) {
                $this->setSubPlan($data['subPlan']);
            }
            if (isset($data['subPlanDisplay'])) {
                $this->setSubPlanDisplay($data['subPlanDisplay']);
            }
            if (isset($data['class'])) {
                $this->setClass($data['class']);
            }
            if (isset($data['classDisplay'])) {
                $this->setClassDisplay($data['classDisplay']);
            }
            if (isset($data['subClass'])) {
                $this->setSubClass($data['subClass']);
            }
            if (isset($data['subClassDisplay'])) {
                $this->setSubClassDisplay($data['subClassDisplay']);
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
        if (isset($this->group)) {
            $json['group'] = $this->group;
        }
        if (isset($this->groupDisplay)) {
            $json['groupDisplay'] = $this->groupDisplay;
        }
        if (isset($this->subGroup)) {
            $json['subGroup'] = $this->subGroup;
        }
        if (isset($this->subGroupDisplay)) {
            $json['subGroupDisplay'] = $this->subGroupDisplay;
        }
        if (isset($this->plan)) {
            $json['plan'] = $this->plan;
        }
        if (isset($this->planDisplay)) {
            $json['planDisplay'] = $this->planDisplay;
        }
        if (isset($this->subPlan)) {
            $json['subPlan'] = $this->subPlan;
        }
        if (isset($this->subPlanDisplay)) {
            $json['subPlanDisplay'] = $this->subPlanDisplay;
        }
        if (isset($this->class)) {
            $json['class'] = $this->class;
        }
        if (isset($this->classDisplay)) {
            $json['classDisplay'] = $this->classDisplay;
        }
        if (isset($this->subClass)) {
            $json['subClass'] = $this->subClass;
        }
        if (isset($this->subClassDisplay)) {
            $json['subClassDisplay'] = $this->subClassDisplay;
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
            $sxe = new \SimpleXMLElement('<CoverageGrouping xmlns="http://hl7.org/fhir"></CoverageGrouping>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->group)) {
            $this->group->xmlSerialize(true, $sxe->addChild('group'));
        }
        if (isset($this->groupDisplay)) {
            $this->groupDisplay->xmlSerialize(true, $sxe->addChild('groupDisplay'));
        }
        if (isset($this->subGroup)) {
            $this->subGroup->xmlSerialize(true, $sxe->addChild('subGroup'));
        }
        if (isset($this->subGroupDisplay)) {
            $this->subGroupDisplay->xmlSerialize(true, $sxe->addChild('subGroupDisplay'));
        }
        if (isset($this->plan)) {
            $this->plan->xmlSerialize(true, $sxe->addChild('plan'));
        }
        if (isset($this->planDisplay)) {
            $this->planDisplay->xmlSerialize(true, $sxe->addChild('planDisplay'));
        }
        if (isset($this->subPlan)) {
            $this->subPlan->xmlSerialize(true, $sxe->addChild('subPlan'));
        }
        if (isset($this->subPlanDisplay)) {
            $this->subPlanDisplay->xmlSerialize(true, $sxe->addChild('subPlanDisplay'));
        }
        if (isset($this->class)) {
            $this->class->xmlSerialize(true, $sxe->addChild('class'));
        }
        if (isset($this->classDisplay)) {
            $this->classDisplay->xmlSerialize(true, $sxe->addChild('classDisplay'));
        }
        if (isset($this->subClass)) {
            $this->subClass->xmlSerialize(true, $sxe->addChild('subClass'));
        }
        if (isset($this->subClassDisplay)) {
            $this->subClassDisplay->xmlSerialize(true, $sxe->addChild('subClassDisplay'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
