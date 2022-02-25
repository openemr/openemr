module Qrda
  module Export
    module Helper
      module PatientViewHelper
        def provider
          JSON.parse(@provider.to_json) if @provider
        end

        def patient
          JSON.parse(@patient.to_json)
        end

        def provider_street
          self['street'].join('')
        end

        def provider_npi
          return nil unless self['ids']

          self['ids'].map { |id| id if id['namingSystem'] == '2.16.840.1.113883.4.6' }.compact
        end

        def provider_tin
          return nil unless self['ids']

          self['ids'].map { |id| id if id['namingSystem'] == '2.16.840.1.113883.4.2' }.compact
        end

        def provider_ccn
          return nil unless self['ids']

          self['ids'].map { |id| id if id['namingSystem'] == '2.16.840.1.113883.4.336' }.compact
        end

        def provider_type_code
          self['specialty']
        end

        def mrn
          @patient.id.to_s
        end

        def given_name
          self['givenNames'].join(' ')
        end

        def gender
          gender_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicSex" }
          return if gender_elements.empty?
          gender_elements.first.dataElementCodes.first['code']
        end

        def birthdate
          birthdate_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicBirthdate" }
          return "None" if birthdate_elements.empty?
          birthdate_elements.first['birthDatetime']
        end

        def expiration
          expired_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicExpired" }
          return "None" if expired_elements.empty?
          expired_elements.first['expiredDatetime']
        end

        def race
          race_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicRace" }
          return if race_elements.empty?
          race_elements.first.dataElementCodes.first['code']
        end

        def ethnic_group
          ethnic_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicEthnicity" }
          return if ethnic_elements.empty?
          ethnic_elements.first.dataElementCodes.first['code']
        end

        def payer
          payer_elements = @qdmPatient.dataElements.select { |de| de._type == "QDM::PatientCharacteristicPayer" }
          return if payer_elements.empty?
          payer_elements.first.dataElementCodes.first['code']
        end
      end
    end
  end
end
