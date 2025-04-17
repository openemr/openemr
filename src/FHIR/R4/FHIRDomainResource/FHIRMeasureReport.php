<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * The MeasureReport resource contains the results of the calculation of a measure; and optionally a reference to the resources involved in that calculation.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMeasureReport extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A formal identifier that is used to identify this MeasureReport when it is represented in other formats or referenced in a specification, model, design or an instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The MeasureReport status. No data will be available until the MeasureReport status is complete.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportStatus
     */
    public $status = null;

    /**
     * The type of measure report. This may be an individual report, which provides the score for the measure for an individual member of the population; a subject-listing, which returns the list of members that meet the various criteria in the measure; a summary report, which returns a population count for each of the criteria in the measure; or a data-collection, which enables the MeasureReport to be used to exchange the data-of-interest for a quality measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportType
     */
    public $type = null;

    /**
     * A reference to the Measure that was calculated to produce this report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $measure = null;

    /**
     * Optional subject identifying the individual or individuals the report is for.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The date this measure report was generated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The individual, location, or organization that is reporting the data.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $reporter = null;

    /**
     * The reporting period for which the report was calculated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Whether improvement in the measure is noted by an increase or decrease in the measure score.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $improvementNotation = null;

    /**
     * The results of the calculation, one for each population group in the measure.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportGroup[]
     */
    public $group = [];

    /**
     * A reference to a Bundle containing the Resources that were used in the calculation of this measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $evaluatedResource = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport';

    /**
     * A formal identifier that is used to identify this MeasureReport when it is represented in other formats or referenced in a specification, model, design or an instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A formal identifier that is used to identify this MeasureReport when it is represented in other formats or referenced in a specification, model, design or an instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The MeasureReport status. No data will be available until the MeasureReport status is complete.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The MeasureReport status. No data will be available until the MeasureReport status is complete.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The type of measure report. This may be an individual report, which provides the score for the measure for an individual member of the population; a subject-listing, which returns the list of members that meet the various criteria in the measure; a summary report, which returns a population count for each of the criteria in the measure; or a data-collection, which enables the MeasureReport to be used to exchange the data-of-interest for a quality measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of measure report. This may be an individual report, which provides the score for the measure for an individual member of the population; a subject-listing, which returns the list of members that meet the various criteria in the measure; a summary report, which returns a population count for each of the criteria in the measure; or a data-collection, which enables the MeasureReport to be used to exchange the data-of-interest for a quality measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMeasureReportType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A reference to the Measure that was calculated to produce this report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * A reference to the Measure that was calculated to produce this report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $measure
     * @return $this
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * Optional subject identifying the individual or individuals the report is for.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Optional subject identifying the individual or individuals the report is for.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The date this measure report was generated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date this measure report was generated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The individual, location, or organization that is reporting the data.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * The individual, location, or organization that is reporting the data.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reporter
     * @return $this
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
        return $this;
    }

    /**
     * The reporting period for which the report was calculated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The reporting period for which the report was calculated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Whether improvement in the measure is noted by an increase or decrease in the measure score.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getImprovementNotation()
    {
        return $this->improvementNotation;
    }

    /**
     * Whether improvement in the measure is noted by an increase or decrease in the measure score.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $improvementNotation
     * @return $this
     */
    public function setImprovementNotation($improvementNotation)
    {
        $this->improvementNotation = $improvementNotation;
        return $this;
    }

    /**
     * The results of the calculation, one for each population group in the measure.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportGroup[]
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * The results of the calculation, one for each population group in the measure.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportGroup $group
     * @return $this
     */
    public function addGroup($group)
    {
        $this->group[] = $group;
        return $this;
    }

    /**
     * A reference to a Bundle containing the Resources that were used in the calculation of this measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEvaluatedResource()
    {
        return $this->evaluatedResource;
    }

    /**
     * A reference to a Bundle containing the Resources that were used in the calculation of this measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $evaluatedResource
     * @return $this
     */
    public function addEvaluatedResource($evaluatedResource)
    {
        $this->evaluatedResource[] = $evaluatedResource;
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
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['measure'])) {
                $this->setMeasure($data['measure']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['reporter'])) {
                $this->setReporter($data['reporter']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['improvementNotation'])) {
                $this->setImprovementNotation($data['improvementNotation']);
            }
            if (isset($data['group'])) {
                if (is_array($data['group'])) {
                    foreach ($data['group'] as $d) {
                        $this->addGroup($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"group" must be array of objects or null, ' . gettype($data['group']) . ' seen.');
                }
            }
            if (isset($data['evaluatedResource'])) {
                if (is_array($data['evaluatedResource'])) {
                    foreach ($data['evaluatedResource'] as $d) {
                        $this->addEvaluatedResource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"evaluatedResource" must be array of objects or null, ' . gettype($data['evaluatedResource']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->measure)) {
            $json['measure'] = $this->measure;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->reporter)) {
            $json['reporter'] = $this->reporter;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->improvementNotation)) {
            $json['improvementNotation'] = $this->improvementNotation;
        }
        if (0 < count($this->group)) {
            $json['group'] = [];
            foreach ($this->group as $group) {
                $json['group'][] = $group;
            }
        }
        if (0 < count($this->evaluatedResource)) {
            $json['evaluatedResource'] = [];
            foreach ($this->evaluatedResource as $evaluatedResource) {
                $json['evaluatedResource'][] = $evaluatedResource;
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
            $sxe = new \SimpleXMLElement('<MeasureReport xmlns="http://hl7.org/fhir"></MeasureReport>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->measure)) {
            $this->measure->xmlSerialize(true, $sxe->addChild('measure'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->reporter)) {
            $this->reporter->xmlSerialize(true, $sxe->addChild('reporter'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->improvementNotation)) {
            $this->improvementNotation->xmlSerialize(true, $sxe->addChild('improvementNotation'));
        }
        if (0 < count($this->group)) {
            foreach ($this->group as $group) {
                $group->xmlSerialize(true, $sxe->addChild('group'));
            }
        }
        if (0 < count($this->evaluatedResource)) {
            foreach ($this->evaluatedResource as $evaluatedResource) {
                $evaluatedResource->xmlSerialize(true, $sxe->addChild('evaluatedResource'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
