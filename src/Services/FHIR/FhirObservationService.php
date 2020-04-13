<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\BaseService;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class FhirObservationService extends BaseService
{
    private $FHIRData;

    public function __construct()
    {
        parent::__construct('form_vitals');
        $this->FHIRData = array();
    }

    public function createObservationResource($id = '', $data = '', $encode = true)
    {
        // Check Profile type and add its Category | Status | Note
        switch ($data['formdir']) {
            case 'vitals':
                $coding['coding']['system'] = "http://terminology.hl7.org/CodeSystem/observation-category";
                $coding['coding']['code'] = "vital-signs";
                $coding['coding']['display'] = "Vital Signs";
                $coding['text'] = "Vital Signs";
                $this->FHIRData['category'] = ['category' => new FHIRCodeableConcept($coding)];
                $data['text'] ? $this->FHIRData['note']['note'] = new FHIRAnnotation(['text' => $data['text']]) : null;
                $this->FHIRData['status'] = 'final';
                break;
            default:
                #TODO: Return CapabilityStatement or Outcome Resource
                break;
        }

        // Check Profile or Member and add/set appropriately
        if ($data['profile'] == 'vitals') {
            $this->addMembers($data);
        } else {
            $this->setValues($data);
        }

        //  TODO: Yash Please Refactor this to as usual as its hard to read.
        $this->FHIRData['subject'] = new FHIRReference(['reference' => "Patient/" . $data['pid'], "type" => "Patient"]);
        $this->FHIRData['encounter'] = new FHIRReference(['reference' => "Encounter/" . $data['encounter'], "type" => "Encounter"]);
        $this->FHIRData['performer']['performer'] = new FHIRReference(['reference' => "Practitioner/" . $data['provider_id'], "type" => "Practitioner"]);
        $this->FHIRData['effectiveDateTime'] = new FHIRDateTime($data['date']);
        $resource = new FHIRObservation($this->FHIRData);
        $resource->setId($id);
        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }

    public function getVital($id)
    {
        return $this->get(
            array(
                "where" => "WHERE form_vitals.id = ?",
                "data" => array($id),
                "join" => "JOIN forms fo on form_vitals.id = fo.form_id",
                "limit" => 1
            )
        );
    }

    private function addMembers($data)
    {
        $members = array_filter($data, function ($k) {
            if ($k != 0 && $k != 0.0 && $k != 0.00 && $k != null && $k != '') {
                return $k;
            }
        });
        $this->FHIRData['hasMember'] = array();
        foreach ($members as $key => $value) {
            if ($key == 'temperature') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/temperature-" . $data['form_id'],
                    "display" => "Body Temperature"
                ));
            } elseif ($key == 'bps') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/bps-" . $data['form_id'],
                    "display" => "Systolic blood pressure"
                ));
            } elseif ($key == 'bpd') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/bpd-" . $data['form_id'],
                    "display" => "Diastolic blood pressure"
                ));
            } elseif ($key == 'pulse') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/pulse-" . $data['form_id'],
                    "display" => "Heart Rate"
                ));
            } elseif ($key == 'respiration') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/respiration-" . $data['form_id'],
                    "display" => "Respiratory Rate"
                ));
            } elseif ($key == 'weight') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/weight-" . $data['form_id'],
                    "display" => "Body weight"
                ));
            } elseif ($key == 'height') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/height-" . $data['form_id'],
                    "display" => "Body height"
                ));
            } elseif ($key == 'BMI') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/BMI-" . $data['form_id'],
                    "display" => "Body mass index (BMI) [Ratio]"
                ));
            } elseif ($key == 'head_circ') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/head_circ-" . $data['form_id'],
                    "display" => "Head Occipital-frontal circumference"
                ));
            } elseif ($key == 'oxygen_saturation') {
                array_push($this->FHIRData['hasMember'], array(
                    "reference" => "Observation/oxygen_saturation-" . $data['form_id'],
                    "display" => "Oxygen saturation in Arterial blood"
                ));
            }
        }
    }

    private function setValues($data)
    {
        $quantity = new FHIRQuantity();
        $quantity->setSystem("http://unitsofmeasure.org");
        switch ($data['profile']) {
            case 'weight':
                $quantity->setValue($data['weight']);
                $quantity->setUnit('lbs');
                $quantity->setCode("[lb_av]");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'height':
                $quantity->setValue($data['height']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'bps':
                $quantity->setValue($data['bps']);
                $quantity->setUnit('mmHg');
                $quantity->setCode("[mm[Hg]]");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'bpd':
                $quantity->setValue($data['bpd']);
                $quantity->setUnit('mmHg');
                $quantity->setCode("[mm[Hg]]");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'temperature':
                $quantity->setValue($data['temperature']);
                $quantity->setUnit('F');
                $quantity->setCode("[degF]");
                $coding['coding']['system'] = "http://snomed.info/sct";
                $coding['coding']['code'] = "vital-signs";
                $coding['coding']['display'] = $data['temp_method'];
                $coding['text'] = "Vital Signs";
                $this->FHIRData['category'] = ['category' => new FHIRCodeableConcept($coding)];
                $bodySite = new FHIRCodeableConcept();

                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'pulse':
                $quantity->setValue($data['pulse']);
                $quantity->setUnit('beats/minute');
                $quantity->setCode("/min");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'respiration':
                $quantity->setValue($data['respiration']);
                $quantity->setUnit('breaths/minute');
                $quantity->setCode("/min");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'BMI':
                $quantity->setValue($data['BMI']);
                $quantity->setUnit('kg/m2');
                $quantity->setCode("kg/m2");
                $height_ref = new FHIRReference();
                $height_ref->setReference("Observation/height-" . $data['id']);
                $height_ref->setDisplay("Body Height");
                $weight_ref = new FHIRReference();
                $weight_ref->setReference("Observation/weight-" . $data['id']);
                $weight_ref->setDisplay("Body Weight");
                $this->FHIRData['derivedFrom'] = array($height_ref, $weight_ref);
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'head_circ':
                $quantity->setValue($data['head_circ']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;
            case 'oxygen_saturation':
                $quantity->setValue($data['oxygen_saturation']);
                $quantity->setUnit('%');
                $quantity->setCode("%");
                $this->FHIRData['valueQuantity'] = $quantity;
                break;

            default:
                break;
        }
    }
}
