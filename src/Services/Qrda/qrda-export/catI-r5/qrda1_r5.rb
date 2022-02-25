require 'mustache'
class Qrda1R5 < Mustache
  include Qrda::Export::Helper::DateHelper
  include Qrda::Export::Helper::ViewHelper
  include Qrda::Export::Helper::Cat1ViewHelper
  include Qrda::Export::Helper::PatientViewHelper
  include Qrda::Export::Helper::FrequencyHelper
  include HQMF::Util::EntityHelper

  self.template_path = __dir__

  def initialize(patient, measures, options = {})
    @patient = patient
    @qdmPatient = patient.qdmPatient
    @measures = measures
    @provider = options[:provider]
    @patient_address_option = options[:patient_addresses]
    @patient_telecom_option = options[:patient_telecoms]
    @performance_period_start = options[:start_time]
    @performance_period_end = options[:end_time]
    @submission_program = options[:submission_program]
  end

  def patient_addresses
    @patient_address_option ||= [CQM::Address.new(
      use: 'HP',
      street: ['202 Burlington Rd.'],
      city: 'Bedford',
      state: 'MA',
      zip: '01730',
      country: 'US'
    )]
    JSON.parse(@patient_address_option.to_json)
  end

  def patient_telecoms
    @patient_telecom_option ||= [CQM::Telecom.new(
      use: 'HP',
      value: '555-555-2003'
    )]
    JSON.parse(@patient_telecom_option.to_json)
  end

  def patient_characteristic_payer
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'payer').to_json)
  end

  def patient_characteristic_birthdate
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'birthdate').to_json)
  end

  def patient_characteristic_sex
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'gender').to_json)
  end

  def patient_characteristic_race
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'race').to_json)
  end

  def patient_characteristic_ethnicity
    JSON.parse(@qdmPatient.get_data_elements('patient_characteristic', 'ethnicity').to_json)
  end

  def adverse_event
    JSON.parse(@qdmPatient.get_data_elements('adverse_event', nil).to_json)
  end

  def allergy_intolerance
    JSON.parse(@qdmPatient.get_data_elements('allergy', 'intolerance').to_json)
  end

  def assessment_order
    JSON.parse(@qdmPatient.get_data_elements('assessment', 'order').to_json)
  end

  def assessment_performed
    JSON.parse(@qdmPatient.get_data_elements('assessment', 'performed').to_json)
  end

  def assessment_recommended
    JSON.parse(@qdmPatient.get_data_elements('assessment', 'recommended').to_json)
  end

  def communication_performed
    JSON.parse(@qdmPatient.get_data_elements('communication', 'performed').to_json)
  end

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
end
