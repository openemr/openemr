module Qrda
  module Export
    module Helper
      module FrequencyHelper
        # FREQUENCY_CODE_MAP extracted from Direct Reference Codes in Opioid_v5_6_eCQM.xml (CMS460v0)
        FREQUENCY_CODE_MAP = {
          '396107007' => { low: 12, high: 24, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'One to two times a day (qualifier value)' },
          '396108002' => { low: 8, high: 24, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'One to three times a day (qualifier value)' },
          '396109005' => { low: 6, high: 24, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'One to four times a day (qualifier value)' },
          '396111001' => { low: 6, high: 12, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Two to four times a day (qualifier value)' },

          '229797004' => { low: 24, high: nil, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Once daily (qualifier value)' },
          '229799001' => { low: 12, high: nil, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Twice a day (qualifier value)' },
          '229798009' => { low: 8, high: nil, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Three times daily (qualifier value)' },
          '307439001' => { low: 6, high: nil, unit: 'h', institution_specified: true, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Four times daily (qualifier value)' },

          '225752000' => { low: 2, high: 4, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every two to four hours (qualifier value)' },
          '225754004' => { low: 3, high: 4, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every three to four hours (qualifier value)' },
          '396127008' => { low: 3, high: 6, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every three to six hours (qualifier value)' },
          '396139000' => { low: 6, high: 8, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every six to eight hours (qualifier value)' },
          '396140003' => { low: 8, high: 12, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every eight to twelve hours (qualifier value)' },

          '225756002' => { low: 4, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', display_name: 'Every four hours (qualifier value)' },
          '307468000' => { low: 6, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every six hours (qualifier value)' },
          '307469008' => { low: 8, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every eight hours (qualifier value)' },
          '307470009' => { low: 12, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every twelve hours (qualifier value)' },
          '396125000' => { low: 24, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every twenty four hours (qualifier value)' },
          '396126004' => { low: 36, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every thirty six hours (qualifier value)' },
          '396131002' => { low: 48, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every forty eight hours (qualifier value)' },
          '396143001' => { low: 72, high: nil, unit: 'h', institution_specified: false, code_system: '2.16.840.1.113883.6.96', code_system_name: 'SNOMEDCT', code_system_version: '2018-03', display_name: 'Every seventy two hours (qualifier value)' }
        }.freeze

        def medication_frequency
          # If the code matches one of the known Direct Reference Codes, export that time in hours. Otherwise default to "every twenty four hours" code
          frequency_code_entry = FREQUENCY_CODE_MAP[self['code']] || FREQUENCY_CODE_MAP['396125000']
          if !frequency_code_entry[:institution_specified]
            if frequency_code_entry[:high].nil?
              institution_not_specified_point_frequency(frequency_code_entry)
            else
              institution_not_specified_range_frequency(frequency_code_entry)
            end
          else
            if frequency_code_entry[:high].nil?
              institution_specified_point_frequency(frequency_code_entry)
            else
              institution_specified_range_frequency(frequency_code_entry)
            end
          end
        end

        def institution_not_specified_point_frequency(frequency_code_entry)
          "<effectiveTime xsi:type='PIVL_TS' operator='A'>"\
          "<period value='#{frequency_code_entry[:low]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "</effectiveTime>"
        end

        def institution_not_specified_range_frequency(frequency_code_entry)
          "<effectiveTime xsi:type='PIVL_TS' operator='A'>"\
          "<period xsi:type='IVL_PQ'>"\
          "<low value='#{frequency_code_entry[:low]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "<high value='#{frequency_code_entry[:high]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "</period>"\
          "</effectiveTime>"
        end

        def institution_specified_point_frequency(frequency_code_entry)
          "<effectiveTime xsi:type='PIVL_TS' institutionSpecified='true' operator='A'>"\
          "<period value='#{frequency_code_entry[:low]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "</effectiveTime>"
        end

        def institution_specified_range_frequency(frequency_code_entry)
          "<effectiveTime xsi:type='PIVL_TS' institutionSpecified='true' operator='A'>"\
          "<period xsi:type='IVL_PQ'>"\
          "<low value='#{frequency_code_entry[:low]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "<high value='#{frequency_code_entry[:high]}' unit='#{frequency_code_entry[:unit]}'/>"\
          "</period>"\
          "</effectiveTime>"
        end
      end
    end
  end
end
