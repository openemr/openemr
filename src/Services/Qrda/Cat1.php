<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qrda\Helpers\Cat1View;
use OpenEMR\Services\Qrda\Helpers\Date;
use OpenEMR\Services\Qrda\Helpers\Frequency;
use OpenEMR\Services\Qrda\Helpers\PatientView;
use OpenEMR\Services\Qrda\Helpers\View;
use Ramsey\Uuid\Rfc4122\UuidV4;

class Cat1 extends \Mustache_Engine
{
    use Date;
    use View;
    use Cat1View;
    use PatientView;
    use Frequency;
    use View;

    protected $templatePath =
        __DIR__ . DIRECTORY_SEPARATOR .
        'qrda-export' . DIRECTORY_SEPARATOR .
        'catI-r5';

    protected $template = 'qrda1_r5.mustache';

    protected $patient;

    /**
     * @var array
     */
    protected $_measures;

    public function __construct(Patient $patient, $measures = array(), $options = array())
    {
        parent::__construct(
            array(
                'entity_flags' => ENT_QUOTES,
                'loader' => new \Mustache_Loader_FilesystemLoader($this->templatePath),
            )
        );

        $this->_qrda_guid = UuidV4::uuid4();

        $this->patient = $patient;
        // comes from PatientView trait
        $this->provider = $options['provider'] ?? null;
        // lambda for performance period is in Date helper trait
        $this->_performance_period_start = $options['performance_period_start'] ?? null;
        $this->_performance_period_end = $options['performance_period_end'] ?? null;
        // Lambda for measures is in View "helper"
        $this->_measures = $measures;
        $this->submission_program = $options['submission_program'] ?? null;
    }

    public function renderCat1Xml()
    {
        $xml = $this->render($this->template, $this); // we pass in ourselves as the context so mustache can see all of our methods, and helper methods

        return $xml;
    }

    public function patient_characteristic_payer()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'payer')), true);
    }

    public function patient_characteristic_birthdate()
    {
        $elements = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'birthdate')), true);
        return $elements;
    }

    public function patient_characteristic_sex()
    {
        $value = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'gender')), true);
        return $value;
    }

    public function patient_characteristic_race()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'race')), true);
    }

    public function patient_characteristic_ethnicity()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'ethnicity')), true);
    }

    public function adverse_event()
    {
        return json_decode(json_encode($this->patient->get_data_elements('adverse_event')), true);
    }

    public function allergy_intolerance()
    {
        return json_decode(json_encode($this->patient->get_data_elements('allergy', 'intolerance')), true);
    }

    public function assessment_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'order')), true);
    }

    public function assessment_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'performed')), true);
    }

    public function assessment_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'recommended')), true);
    }

    public function communication_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('communication', 'performed')), true);
    }

    public function diagnosis()
    {
        return json_decode(json_encode($this->patient->get_data_elements('condition')), true);
    }

    public function device_applied()
    {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'applied')), true);
    }

    public function device_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'order')), true);
    }

    public function device_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'recommended')), true);
    }

    public function diagnostic_study_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'order')), true);
    }

    public function diagnostic_study_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'performed')), true);
    }

    public function diagnostic_study_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'recommended')), true);
    }

    public function encounter_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'order')), true);
    }

    public function encounter_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'performed')), true);
    }

    public function encounter_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'recommended')), true);
    }

    public function family_history()
    {
        return json_decode(json_encode($this->patient->get_data_elements('family_history')), true);
    }

    public function immunization_administered()
    {
        return json_decode(json_encode($this->patient->get_data_elements('immunization', 'administered')), true);
    }

    public function immunization_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('immunization', 'order')), true);
    }

    public function intervention_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'order')), true);
    }

    public function intervention_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'performed')), true);
    }

    public function intervention_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'recommended')), true);
    }

    public function laboratory_test_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'order')), true);
    }

    public function laboratory_test_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'performed')), true);
    }

    public function laboratory_test_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'recommended')), true);
    }

    public function medication_active()
    {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'active')), true);
    }

    public function medication_administered()
    {
        $medsAdministered = json_decode(json_encode($this->patient->get_data_elements('medication', 'administered')), true);
        $subsAdministered = json_decode(json_encode($this->patient->get_data_elements('substance', 'administered')), true);
        return array_merge($medsAdministered, $subsAdministered);
    }

    public function medication_discharge()
    {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'discharge')), true);
    }

    public function medication_dispensed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'dispensed')), true);
    }

    public function medication_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'order')), true);
    }

    public function patient_care_experience()
    {
        // TODO: @sjpadgett, @adunsulag, @ken.matrix need to implement this method with helper util
        return [];
        //JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('patient_care_experience', '') }).to_json)
    }

    public function patient_characteristic_clinical_trial_participant()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'clinical_trial_participant')), true);
    }

    public function patient_characteristic_expired()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'expired')), true);
    }

    public function physical_exam_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'order')), true);
    }

    public function physical_exam_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'performed')), true);
    }

    public function physical_exam_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'recommended')), true);
    }

    public function procedure_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'order')), true);
    }

    public function procedure_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'performed')), true);
    }

    public function procedure_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'recommended')), true);
    }

    public function program_participation()
    {
        return json_decode(json_encode($this->patient->get_data_elements('participation')), true);
    }

    public function provider_care_experience()
    {
        // TODO: @sjpadgett, @adunsulag, @ken.matrix need to implement this method with helper util
        return [];
        //JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('provider_care_experience', '') }).to_json)
    }

    public function related_person()
    {
        return json_decode(json_encode($this->patient->get_data_elements('related_person')), true);
    }

    public function substance_administered()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'administered')), true);
    }

    public function substance_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'order')), true);
    }

    public function substance_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'recommended')), true);
    }

    public function symptom()
    {
        return json_decode(json_encode($this->patient->get_data_elements('symptom')), true);
    }
}
