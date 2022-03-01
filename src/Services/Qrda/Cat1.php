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
use OpenEMR\Services\Qrda\Helpers\Date;
use OpenEMR\Services\Qrda\Helpers\DateHelper;

class Cat1 extends \Mustache_Engine
{
    //use Date;

    protected $templatePath =
        __DIR__ . DIRECTORY_SEPARATOR .
        'qrda-export' . DIRECTORY_SEPARATOR .
        'catI-r5';

    protected $template = 'qrda1_r5.mustache';

    protected $patient;
    protected $mustache;

    public $random_id = '4444';
    public $mrn = '545644';

    public function __construct(Patient $patient)
    {
//        $helpers = [];
//        $helpers = array_merge($helpers, $this->DateHelper());

        $this->patient = $patient;
        parent::__construct(array(
            'entity_flags' => ENT_QUOTES,
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templatePath),
            'helpers' => [
                'value_or_null_flavor' => function ($text) {
                    if (!empty($text)) {
                        $v = "value='{$text}'";
                    } else {
                        $v = "nullFlavor='UNK'";
                    }
                    return $v;
                },
                'birth_date_time' => function () {
                    $birth_date_time = $this->birthDatetime;
                    $birth_date_time = date('Ymd', strtotime($birth_date_time));
                    return "<birthTime {{#value_or_null_flavor}}" . $birth_date_time . "{{/value_or_null_flavor}}/>";
                }
            ]
        ));

        /*
         *
         *             'value_or_null_flavor' => function ($time) {
                return date('Y-m-d H:i', strtotime($time));
            },
            'performance_period_start' => function () {
                return "Hello Period Start";
            },
            'performance_period_end' => function () {
                return "Hello Period End";
            },
            'current_time' => function () {
                return date('Y-m-d H:i');
            },
            //
            'birth_date_time' => function ($text, $mustache) {
                return "<birthTime" . $mustache->render("#{value_or_null_flavor({$this->patient->birthDatetime})}") . "/>";
            },
         */
    }


    public function renderCat1Xml()
    {
        $xml = $this->render(
            $this->template,
            $this
        );

        return $xml;
    }

    public function patient_characteristic_birthdate()
    {
        $elements = $this->patient->get_data_elements('patient_characteristic', 'birthdate');
        if (count($elements) === 1) {
            $birthDate = $elements[0];
        } else {
            throw new \Exception("ERROR: There can only be one birthdate element");
        }
        return function ($text, $context) use ($birthDate) {
            $mustache = new \Mustache_Engine([
                'entity_flags' => ENT_QUOTES,
                'helpers' => [
                    'value_or_null_flavor' => function ($text) {
                        if (!empty($text)) {
                            $v = "value='{$text}'";
                        } else {
                            $v = "nullFlavor='UNK'";
                        }
                        return $v;
                    },
                    'birth_date_time' => function () use ($birthDate) {
                        $birth_date_time = $birthDate->birthDatetime->date;
                        $birth_date_time = date('Ymd', strtotime($birth_date_time));
                        return "<birthTime {{#value_or_null_flavor}}" . $birth_date_time . "{{/value_or_null_flavor}}/>";
                    }
                ]
            ]);
            return $mustache->render($text, $birthDate);
        };
    }

    public function patient_characteristic_sex()
    {
        $value = $this->patient->get_data_elements('patient_characteristic', 'gender');
        return $value;
    }

    public function patient_characteristic_race()
    {
        return $this->patient->get_data_elements('patient_characteristic', 'race');
    }

    public function patient_characteristic_ethnicity()
    {
        return $this->patient->get_data_elements('patient_characteristic', 'ethnicity');
    }

    public function adverse_event()
    {
        return $this->patient->get_data_elements('adverse_event');
    }

    public function allergy_intolerance()
    {
        return $this->patient->get_data_elements('allergy', 'intolerance');
    }

    public function assessment_order()
    {
        return $this->patient->get_data_elements('assessment', 'order');
    }

    public function assessment_performed()
    {
        return $this->patient->get_data_elements('assessment', 'performed');
    }

    public function assessment_recommended()
    {
        return $this->patient->get_data_elements('assessment', 'recommended');
    }

    public function communication_performed()
    {
        return $this->patient->get_data_elements('communication', 'performed');
    }

    public function diagnosis()
    {
        return $this->patient->get_data_elements('condition');
    }

    /*


  def diagnosis
    JSON.parse(@qdmPatient.get_data_elements('condition', nil).to_json)
  end

  def device_applied
    JSON.parse(@qdmPatient.get_data_elements('device', 'applied').to_json)
  end

  def device_order
    JSON.parse(@qdmPatient.get_data_elements('device', 'order').to_json)
  end

  def device_recommended
    JSON.parse(@qdmPatient.get_data_elements('device', 'recommended').to_json)
  end

  def diagnostic_study_order
    JSON.parse(@qdmPatient.get_data_elements('diagnostic_study', 'order').to_json)
  end

  def diagnostic_study_performed
    JSON.parse(@qdmPatient.get_data_elements('diagnostic_study', 'performed').to_json)
  end

  def diagnostic_study_recommended
    JSON.parse(@qdmPatient.get_data_elements('diagnostic_study', 'recommended').to_json)
  end

  def encounter_order
    JSON.parse(@qdmPatient.get_data_elements('encounter', 'order').to_json)
  end

  def encounter_performed
    JSON.parse(@qdmPatient.get_data_elements('encounter', 'performed').to_json)
  end

  def encounter_recommended
    JSON.parse(@qdmPatient.get_data_elements('encounter', 'recommended').to_json)
  end

  def family_history
    JSON.parse(@qdmPatient.get_data_elements('family_history', nil).to_json)
  end

  def immunization_administered
    JSON.parse(@qdmPatient.get_data_elements('immunization', 'administered').to_json)
  end

  def immunization_order
    JSON.parse(@qdmPatient.get_data_elements('immunization', 'order').to_json)
  end

  def intervention_order
    JSON.parse(@qdmPatient.get_data_elements('intervention', 'order').to_json)
  end

  def intervention_performed
    JSON.parse(@qdmPatient.get_data_elements('intervention', 'performed').to_json)
  end

  def intervention_recommended
    JSON.parse(@qdmPatient.get_data_elements('intervention', 'recommended').to_json)
  end

  def laboratory_test_order
    JSON.parse(@qdmPatient.get_data_elements('laboratory_test', 'order').to_json)
  end

  def laboratory_test_performed
    JSON.parse(@qdmPatient.get_data_elements('laboratory_test', 'performed').to_json)
  end

  def laboratory_test_recommended
    JSON.parse(@qdmPatient.get_data_elements('laboratory_test', 'recommended').to_json)
  end

  def medication_active
    JSON.parse(@qdmPatient.get_data_elements('medication', 'active').to_json)
  end

  def medication_administered
    JSON.parse(@qdmPatient.get_data_elements('medication', 'administered').to_json) + JSON.parse(@qdmPatient.get_data_elements('substance', 'administered').to_json)
  end

  def medication_discharge
    JSON.parse(@qdmPatient.get_data_elements('medication', 'discharge').to_json)
  end

  def medication_dispensed
    JSON.parse(@qdmPatient.get_data_elements('medication', 'dispensed').to_json)
  end

  def medication_order
    JSON.parse(@qdmPatient.get_data_elements('medication', 'order').to_json)
  end

  def patient_care_experience
    JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('patient_care_experience', '') }).to_json)
  end

  def patient_characteristic_clinical_trial_participant
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'clinical_trial_participant').to_json)
  end

  def patient_characteristic_expired
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'expired').to_json)
  end

  def physical_exam_order
    JSON.parse(@qdmPatient.get_data_elements('physical_exam', 'order').to_json)
  end

  def physical_exam_performed
    JSON.parse(@qdmPatient.get_data_elements('physical_exam', 'performed').to_json)
  end

  def physical_exam_recommended
    JSON.parse(@qdmPatient.get_data_elements('physical_exam', 'recommended').to_json)
  end

  def procedure_order
    JSON.parse(@qdmPatient.get_data_elements('procedure', 'order').to_json)
  end

  def procedure_performed
    JSON.parse(@qdmPatient.get_data_elements('procedure', 'performed').to_json)
  end

  def procedure_recommended
    JSON.parse(@qdmPatient.get_data_elements('procedure', 'recommended').to_json)
  end

  def program_participation
    JSON.parse(@qdmPatient.get_data_elements('participation', nil).to_json)
  end

  def provider_care_experience
    JSON.parse(@qdmPatient.dataElements.where(hqmfOid: { '$in' => HQMF::Util::HQMFTemplateHelper.get_all_hqmf_oids('provider_care_experience', '') }).to_json)
  end

  def related_person
    JSON.parse(@qdmPatient.get_data_elements('related_person', nil).to_json)
  end

  def substance_administered
    JSON.parse(@qdmPatient.get_data_elements('substance', 'administered').to_json)
  end

  def substance_order
    JSON.parse(@qdmPatient.get_data_elements('substance', 'order').to_json)
  end

  def substance_recommended
    JSON.parse(@qdmPatient.get_data_elements('substance', 'recommended').to_json)
  end

  def symptom
    JSON.parse(@qdmPatient.get_data_elements('symptom', nil).to_json)
  end
     */
}
