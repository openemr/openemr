<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\BaseService;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;

class FhirObservationService extends BaseService
{

    public function __construct()
    {
        parent::__construct('form_vitals');
    }

    public function createObservationResource($id = '', $data = '', $encode = true)
    {
        $resource = new FHIRObservation();

        // Check Profile type and add its Category | Status | Note
        switch ($data['formdir']) {
            case 'vitals':
                $code = new FHIRCoding();
                $coding = new FHIRCodeableConcept();
                $code->setSystem("http://terminology.hl7.org/CodeSystem/observation-category");
                $code->setCode("vital-signs");
                $code->setDisplay("Vital Signs");
                $coding->addCoding($code);
                $coding->setText("Vital Signs");
                $resource->addCategory($coding);
                $data['text'] ? $resource->addNote($data['text']) : null;
                $resource->setStatus('final');
                break;
            default:
                #TODO: Return CapabilityStatement or Outcome Resource
                break;
        }

        // Check Profile or Member and add/set appropriately
        if ($data['profile'] == 'vitals') {
            $this->addMembers($data, $resource);
        } else {
            $this->setValues($data, $resource);
        }

        $subject = new FHIRReference();
        $subject->setReference("Patient/" . $data['pid']);
        $subject->setType("Patient");
        $resource->setSubject($subject);
        $encounter =  new FHIRReference();
        $encounter->setReference("Encounter/" . $data['encounter']);
        $encounter->setType("Encounter");
        $resource->setEncounter($encounter);
        $performer = new FHIRReference();
        $performer->setReference("Practitioner/" . $data['provider_id']);
        $performer->setType("Practitioner");
        $resource->addPerformer($performer);
        $resource->setEffectiveDateTime($data['date']);
        $resource->setId($id);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }

    public function getOne($id)
    {
        $split_id = explode("-", $id);
        $profile = $split_id[0];
        $id = $split_id[1];
        $profile_data = $this->get(
            array(
                "where" => "WHERE form_vitals.id = ?",
                "data" => array($id),
                "join" => "JOIN forms fo on form_vitals.id = fo.form_id",
                "limit" => 1
            )
        );
        $profile_data['profile'] = $profile;
        return $profile_data;
    }

    public function getAll()
    {
        return $this->get(
            array(
                "join" => "JOIN forms fo on form_vitals.id = fo.form_id"
            )
        );
    }

    private function addMembers($data, $resource)
    {
        $members = array_filter($data, function ($k) {
            if ($k != 0 && $k != 0.0 && $k != 0.00 && $k != null && $k != '') {
                return $k;
            }
        });

        foreach ($members as $key => $value) {
            if ($key == 'temperature') {
                $temp_refrence = new FHIRReference();
                $temp_refrence->setReference("Observation/temperature-" . $data['form_id']);
                $temp_refrence->setDisplay("Body Temperature");
                $resource->addHasMember($temp_refrence);
            } elseif ($key == 'bps') {
                $bps_refrence = new FHIRReference();
                $bps_refrence->setReference("Observation/bps-" . $data['form_id']);
                $bps_refrence->setDisplay("Systolic blood pressure");
                $resource->addHasMember($bps_refrence);
            } elseif ($key == 'bpd') {
                $bpd_refrence = new FHIRReference();
                $bpd_refrence->setReference("Observation/bpd-" . $data['form_id']);
                $bpd_refrence->setDisplay("Diastolic blood pressure");
                $resource->addHasMember($bpd_refrence);
            } elseif ($key == 'pulse') {
                $pulse_refrence = new FHIRReference();
                $pulse_refrence->setDisplay("Heart Rate");
                $pulse_refrence->setReference("Observation/pulse-" . $data['form_id']);
                $resource->addHasMember($pulse_refrence);
            } elseif ($key == 'respiration') {
                $respiration_refrence = new FHIRReference();
                $respiration_refrence->setReference("Observation/respiration-" . $data['form_id']);
                $respiration_refrence->setDisplay("Respiratory Rate");
                $resource->addHasMember($respiration_refrence);
            } elseif ($key == 'weight') {
                $weight_refrence = new FHIRReference();
                $weight_refrence->setReference("Observation/weight-" . $data['form_id']);
                $weight_refrence->setDisplay("Body weight");
                $resource->addHasMember($weight_refrence);
            } elseif ($key == 'height') {
                $height_refrence = new FHIRReference();
                $height_refrence->setReference("Observation/height-" . $data['form_id']);
                $height_refrence->setDisplay("Body height");
                $resource->addHasMember($height_refrence);
            } elseif ($key == 'BMI') {
                $BMI_refrence = new FHIRReference();
                $BMI_refrence->setReference("Observation/BMI-" . $data['form_id']);
                $BMI_refrence->setDisplay("Body mass index (BMI) [Ratio]");
                $resource->addHasMember($BMI_refrence);
            } elseif ($key == 'head_circ') {
                $head_circ_refrence = new FHIRReference();
                $head_circ_refrence->setReference("Observation/head_circ-" . $data['form_id']);
                $head_circ_refrence->setDisplay("Head Occipital-frontal circumference");
                $resource->addHasMember($head_circ_refrence);
            } elseif ($key == 'oxygen_saturation') {
                $oxygen_refrence = new FHIRReference();
                $oxygen_refrence->setReference("Observation/oxygen_saturation-" . $data['form_id']);
                $oxygen_refrence->setDisplay("Oxygen saturation in Arterial blood");
                $resource->addHasMember($oxygen_refrence);
            }
        }
    }

    private function setValues($data, $resource)
    {
        $quantity = new FHIRQuantity();
        $quantity->setSystem("http://unitsofmeasure.org");
        switch ($data['profile']) {
            case 'weight':
                $quantity->setValue($data['weight']);
                $quantity->setUnit('lbs');
                $quantity->setCode("[lb_av]");
                $resource->setValueQuantity($quantity);
                break;
            case 'height':
                $quantity->setValue($data['height']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $resource->setValueQuantity($quantity);
                break;
            case 'bps':
                $quantity->setValue($data['bps']);
                $quantity->setUnit('mmHg');
                $quantity->setCode("[mm[Hg]]");
                $resource->setValueQuantity($quantity);
                break;
            case 'bpd':
                $quantity->setValue($data['bpd']);
                $quantity->setUnit('mmHg');
                $quantity->setCode("[mm[Hg]]");
                $resource->setValueQuantity($quantity);
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
                $resource->setValueQuantity($quantity);
                break;
            case 'pulse':
                $quantity->setValue($data['pulse']);
                $quantity->setUnit('beats/minute');
                $quantity->setCode("/min");
                $resource->setValueQuantity($quantity);
                break;
            case 'respiration':
                $quantity->setValue($data['respiration']);
                $quantity->setUnit('breaths/minute');
                $quantity->setCode("/min");
                $resource->setValueQuantity($quantity);
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
                $resource->addDerivedFrom($height_ref);
                $resource->addDerivedFrom($height_ref);
                $resource->setValueQuantity($quantity);
                break;
            case 'head_circ':
                $quantity->setValue($data['head_circ']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $resource->setValueQuantity($quantity);
                break;
            case 'oxygen_saturation':
                $quantity->setValue($data['oxygen_saturation']);
                $quantity->setUnit('%');
                $quantity->setCode("%");
                $resource->setValueQuantity($quantity);
                break;

            default:
                break;
        }
    }
}
