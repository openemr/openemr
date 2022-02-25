# TODO: make sure still accessible to _entity.mustache after moving from qrda-export/helper
module HQMF
  module Util
    module EntityHelper
      def practitioner_entity?
        self[:hqmfOid] == '2.16.840.1.113883.10.20.28.4.137'
      end

      def care_partner_entity?
        self[:hqmfOid] == '2.16.840.1.113883.10.20.28.4.134'
      end

      def location_entity?
        self[:hqmfOid] == '2.16.840.1.113883.10.20.28.4.142'
      end

      def organization_entity?
        self[:hqmfOid] == '2.16.840.1.113883.10.20.28.4.135'
      end

      def patient_entity?
        self[:hqmfOid] == '2.16.840.1.113883.10.20.28.4.136'
      end
    end
  end
end
