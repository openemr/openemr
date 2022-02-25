module Qrda
  module Export
    module Helper
      module Cat1ViewHelper
        def negation_ind
          self[:negationRationale].nil? ? "" : "negationInd=\"true\""
        end

        def negated
          self[:negationRationale].nil? ? false : true
        end

        def multiple_codes?
          self[:dataElementCodes].size > 1
        end

        def display_author_dispenser_id?
          self['qdmCategory'] == 'medication' && self['qdmStatus'] == 'dispensed'
        end

        def display_author_prescriber_id?
          self['qdmCategory'] == 'medication' && self['qdmStatus'] == 'order'
        end

        def id_or_null_flavor
          return "<id root=\"#{self['namingSystem']}\" extension=\"#{self['value']}\"/>" if self['namingSystem'] && self['value']
          "<id nullFlavor=\"NA\"/>"
        end

        def code_and_codesystem
          oid = self['system'] || self['codeSystem']
          if oid == '1.2.3.4.5.6.7.8.9.10'
            "nullFlavor=\"NA\" sdtc:valueSet=\"#{self['code']}\""
          else
            "code=\"#{self['code']}\" codeSystem=\"#{oid}\" codeSystemName=\"#{HQMF::Util::CodeSystemHelper.code_system_for(oid)}\""
          end
        end

        def primary_code_and_codesystem
          oid = self[:dataElementCodes][0]['system'] || self[:dataElementCodes][0]['codeSystem']
          "code=\"#{self[:dataElementCodes][0]['code']}\" codeSystem=\"#{oid}\" codeSystemName=\"#{HQMF::Util::CodeSystemHelper.code_system_for(oid)}\""
        end

        def translation_codes_and_codesystem_list
          translation_list = ""
          self[:dataElementCodes].each_with_index do |_dec, index|
            next if index.zero?
            oid = self[:dataElementCodes][index]['system'] || self[:dataElementCodes][index]['codeSystem']
            translation_list += "<translation code=\"#{self[:dataElementCodes][index]['code']}\" codeSystem=\"#{oid}\" codeSystemName=\"#{HQMF::Util::CodeSystemHelper.code_system_for(oid)}\"/>"
          end
          translation_list
        end

        def value_as_float
          self['value'].to_f
        end

        def dose_quantity_value
          return "<doseQuantity value=\"#{value_as_float}\" unit=\"#{self['unit']}\"/>" if self['unit']
          "<doseQuantity value=\"#{value_as_float}\" />"
        end

        def result_value
          return "<value xsi:type=\"CD\" nullFlavor=\"UNK\"/>" unless self['result']

          result_string = if self['result'].is_a? Array
                            result_value_as_string(self['result'][0])
                          elsif self['result'].is_a? Hash
                            result_value_as_string(self['result'])
                          elsif self['result'].is_a? String
                            "<value xsi:type=\"ST\">#{self['result']}</value>"
                          elsif !self['result'].nil?
                            "<value xsi:type=\"PQ\" value=\"#{self['result']}\" unit=\"1\"/>"
                          end
          result_string
        end

        def result_value_as_string(result)
          return "<value xsi:type=\"CD\" nullFlavor=\"UNK\"/>" unless result
          oid = result['system'] || result['codeSystem']
          return "<value xsi:type=\"CD\" code=\"#{result['code']}\" codeSystem=\"#{oid}\" codeSystemName=\"#{HQMF::Util::CodeSystemHelper.code_system_for(oid)}\"/>" if result['code']
          return "<value xsi:type=\"PQ\" value=\"#{result['value']}\" unit=\"#{result['unit']}\"/>" if result['unit']
        end

        def authordatetime_or_dispenserid?
          self['authorDatetime'] || self['dispenserId']
        end
      end
    end
  end
end
