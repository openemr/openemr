module QRDA
  module Util
    # General helpers for working with codes and code systems
    class QRDATemplateHelper

      def self.definition_for_template_id(hqmf_template_id, qrda_version = "r5")
        template_id_map(qrda_version)[hqmf_template_id]
      end

      def self.template_id_map(version)
        if @id_map.blank?
          @id_map = {
            'r5_1' => JSON.parse(File.read(File.expand_path('qrdar5_1_template_oid_map.json', __dir__)))
          }
        end
        @id_map[version]
      end
    end
  end
end
