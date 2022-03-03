<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;


use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qrda\Helpers\Cat1View;
use OpenEMR\Services\Qrda\Helpers\Date;
use OpenEMR\Services\Qrda\Helpers\View;

class Cat1 extends \Mustache_Engine
{
    use Date;
    use View;
    use Cat1View;

    protected $templatePath =
        __DIR__ . DIRECTORY_SEPARATOR .
        'qrda-export' . DIRECTORY_SEPARATOR .
        'catI-r5';

    protected $template = 'qrda1_r5.mustache';

    protected $patient;
    protected $mustache;

    public $random_id = '4444';
    public $mrn = '545644';

    /**
     * @var array
     */
    protected $_measures;


    public function __construct(Patient $patient, $measures = array(), $options = array())
    {
        $this->patient = $patient;
        $this->_performance_period_end = $options['performance_period_start'] ?? null;
        $this->_performance_period_end = $options['performance_period_end'] ?? null;
        $this->_measures = $measures;
        $this->_submission_program = $options['submission_program'] ?? null;

        error_log(var_export($patient, true));

        parent::__construct(array(
            'entity_flags' => ENT_QUOTES,
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templatePath),
        ));
    }


    public function renderCat1Xml()
    {
        $xml = $this->render(
            $this->template,
            $this // we pass in ourselves as the context so mustache can see all of our methods, and helper methods
        );

        return $xml;
    }

    public function patient_characteristic_birthdate()
    {
        $elements = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'birthdate')));
        return $elements;
    }

    public function patient_characteristic_sex()
    {
        $value = json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'gender')));
        return $value;
    }

    public function patient_characteristic_race()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'race')));
    }

    public function patient_characteristic_ethnicity()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'ethnicity')));
    }

    public function adverse_event()
    {
        return json_decode(json_encode($this->patient->get_data_elements('adverse_event')));
    }

    public function allergy_intolerance()
    {
        return json_decode(json_encode($this->patient->get_data_elements('allergy', 'intolerance')));
    }

    public function assessment_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'order')));
    }

    public function assessment_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'performed')));
    }

    public function assessment_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('assessment', 'recommended')));
    }

    public function communication_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('communication', 'performed')));
    }

    public function diagnosis()
    {
        return json_decode(json_encode($this->patient->get_data_elements('condition')));
    }

    public function device_applied() {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'applied')));
    }

    public function device_order() {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'order')));
    }

    public function device_recommended() {
        return json_decode(json_encode($this->patient->get_data_elements('device', 'recommended')));
    }

    public function diagnostic_study_order() {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'order')));
    }

    public function diagnostic_study_performed() {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'performed')));
    }

    public function diagnostic_study_recommended() {
        return json_decode(json_encode($this->patient->get_data_elements('diagnostic_study', 'recommended')));
    }

    public function encounter_order() {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'order')));
    }

    public function encounter_performed() {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'performed')));
    }

    public function encounter_recommended() {
        return json_decode(json_encode($this->patient->get_data_elements('encounter', 'recommended')));
    }

    public function family_history() {
        return json_decode(json_encode($this->patient->get_data_elements('family_history')));
    }

    public function immunization_administered() {
        return json_decode(json_encode($this->patient->get_data_elements('immunization', 'administered')));
    }

    public function immunization_order() {
        return json_decode(json_encode($this->patient->get_data_elements('immunization', 'order')));
    }

    public function intervention_order() {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'order')));
    }

    public function intervention_performed() {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'performed')));
    }

    public function intervention_recommended() {
        return json_decode(json_encode($this->patient->get_data_elements('intervention', 'recommended')));
    }

    public function laboratory_test_order() {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'order')));
    }

    public function laboratory_test_performed() {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'performed')));
    }

    public function laboratory_test_recommended() {
        return json_decode(json_encode($this->patient->get_data_elements('laboratory_test', 'recommended')));
    }

    public function medication_active() {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'active')));
    }

    public function medication_administered() {
        $medsAdministered = json_decode(json_encode($this->patient->get_data_elements('medication', 'administered')));
        $subsAdministered = json_decode(json_encode($this->patient->get_data_elements('substance', 'administered')));
        return array_merge($medsAdministered, $subsAdministered);
    }

    public function medication_discharge() {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'discharge')));
    }

    public function medication_dispensed() {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'dispensed')));
    }

    public function medication_order() {
        return json_decode(json_encode($this->patient->get_data_elements('medication', 'order')));
    }
    public function patient_care_experience()
    {
        // TODO: @sjpadgett, @adunsulag, @ken.matrix need to implement this method with helper util
        return [];
        //JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('patient_care_experience', '') }).to_json)
    }

    public function patient_characteristic_clinical_trial_participant()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'clinical_trial_participant')));
    }

    public function patient_characteristic_expired()
    {
        return json_decode(json_encode($this->patient->get_data_elements('patient_characteristic', 'expired')));
    }

    public function physical_exam_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'order')));
    }

    public function physical_exam_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'performed')));
    }

    public function physical_exam_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('physical_exam', 'recommended')));
    }

    public function procedure_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'order')));
    }

    public function procedure_performed()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'performed')));
    }

    public function procedure_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('procedure', 'recommended')));
    }

    public function program_participation()
    {
        return json_decode(json_encode($this->patient->get_data_elements('participation')));
    }

    public function provider_care_experience()
    {
        // TODO: @sjpadgett, @adunsulag, @ken.matrix need to implement this method with helper util
        return [];
        //JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('provider_care_experience', '') }).to_json)
    }

    public function related_person()
    {
        return json_decode(json_encode($this->patient->get_data_elements('related_person')));
    }

    public function substance_administered()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'administered')));
    }

    public function substance_order()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'order')));
    }

    public function substance_recommended()
    {
        return json_decode(json_encode($this->patient->get_data_elements('substance', 'recommended')));
    }

    public function symptom()
    {
        return json_decode(json_encode($this->patient->get_data_elements('symptom')));
    }
}
