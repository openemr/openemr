<?php

namespace OpenEMR\Services\Qrda\Helpers;

trait Cat3View
{
    public static array $PAYER_MAP = ['1' => 'A', '2' => 'B', '3' => 'D', '4' => 'D', '5' => 'C', '6' => 'C', '7' => 'D', '8' => 'D', '9' => 'D'];

    public function population_type(\Mustache_Context $context)
    {
        $type = $context->find('type');
        if ($type == 'IPP') {
            return 'IPOP';
        } else {
            return $type;
        }
        /**
         *     def population_type
                self['type'] == 'IPP' ? 'IPOP' : self['type']
                end
         */
    }

    public function population_value(\Mustache_Context $context)
    {
        $value = $context->find('value');
        return round($value);
        /*
            def population_value
            self['value'].round
            end
         */
    }

    public function msrpopl(\Mustache_Context $context)
    {
        $type = $context->find('type');
        return $type == 'MSRPOPL';
        /**
        def msrpopl?
        self['type'] == 'MSRPOPL'
        end
         */
    }

    public function not_observ(\Mustache_Context $context)
    {
        $type = $context->find('type');
        return $type != 'OBSERV';
        /**
        def not_observ?
        self['type'] != 'OBSERV'
        end
         */
    }

    public function stratification_observation(\Mustache_Context $context)
    {
        return $context->find('observation');
        /**
        def stratification_observation
        self['observation']
        end
         */
    }

    public function population_observation(\Mustache_Context $context)
    {
        return $context->find('observation');
        /**
        def population_observation
        self['observation']
        end
         */
    }

    public function supplemental_template_ids(\Mustache_Context $context): array
    {
        $type = $context->find('type');
        switch ($type) {
            case 'RACE':
                return [
                    ['tid' => '2.16.840.1.113883.10.20.27.3.8', 'extension' => '2016-09-01']
                ];
            case 'ETHNICITY':
                return [
                    ['tid' => '2.16.840.1.113883.10.20.27.3.7', 'extension' => '2016-09-01']
                ];
            case 'SEX':
                return [
                    ['tid' => '2.16.840.1.113883.10.20.27.3.6', 'extension' => '2016-09-01']
                ];
            case 'PAYER':
                return [
                    ['tid' => '2.16.840.1.113883.10.20.27.3.9', 'extension' => '2016-02-01'],
                    ['tid' => '2.16.840.1.113883.10.20.27.3.18', 'extension' => '2018-05-01']
                ];
        }
        /**
        def supplemental_template_ids
        case self['type']
        when 'RACE'
        [{ tid: '2.16.840.1.113883.10.20.27.3.8', extension: '2016-09-01' }]
        when 'ETHNICITY'
        [{ tid: '2.16.840.1.113883.10.20.27.3.7', extension: '2016-09-01' }]
        when 'SEX'
        [{ tid: '2.16.840.1.113883.10.20.27.3.6', extension: '2016-09-01' }]
        when 'PAYER'
        [{ tid: '2.16.840.1.113883.10.20.27.3.9', extension: '2016-02-01' },
        { tid: '2.16.840.1.113883.10.20.27.3.18', extension: '2018-05-01' }]
        end
        end
         */
    }

    public function cms_payer_code(\Mustache_Context $context): string
    {
        $code = $context->find('code');
        if ($code[0] && isset(Cat3View::$PAYER_MAP[$code[0]])) {
            return Cat3View::$PAYER_MAP[$code[0]];
        } else {
            return 'D';
        }
        /**
        def cms_payer_code
        PAYER_MAP[self['code'][0]] || 'D'
        end
         */
    }

    public function payer_code(\Mustache_Context $context): bool
    {
        $type = $context->find('type');
        return $type == 'PAYER';
        /**
        def payer_code?
        self['type'] == 'PAYER'
        end
         */
    }

    public function supplemental_data_code(\Mustache_Context $context): array
    {
        $type = $context->find('type');
        switch ($type) {
            case 'RACE':
                return [
                    [ "supplemental_data_code" => '72826-1', "supplemental_data_code_system" => '2.16.840.1.113883.6.1' ]
                ];
            case 'ETHNICITY':
                return [
                    [ "supplemental_data_code" => '69490-1', "supplemental_data_code_system" => '2.16.840.1.113883.6.1' ]
                ];
            case 'SEX':
                return [
                    [ "supplemental_data_code" => '76689-9', "supplemental_data_code_system" => '2.16.840.1.113883.6.1' ]
                ];
            case 'PAYER':
                return [
                    ["supplemental_data_code" => '48768-6', "supplemental_data_code_system" => '2.16.840.1.113883.6.1']
                ];
        }
        /**
        def supplemental_data_code
        case self['type']
        when 'RACE'
        [{ supplemental_data_code: '72826-1', supplemental_data_code_system: '2.16.840.1.113883.6.1' }]
        when 'ETHNICITY'
        [{ supplemental_data_code: '69490-1', supplemental_data_code_system: '2.16.840.1.113883.6.1' }]
        when 'SEX'
        [{ supplemental_data_code: '76689-9', supplemental_data_code_system: '2.16.840.1.113883.6.1' }]
        when 'PAYER'
        [{ supplemental_data_code: '48768-6', supplemental_data_code_system: '2.16.840.1.113883.6.1' }]
        end
        end
         */
    }

    public function supplemental_data_value_code_system(\Mustache_Context $context): string
    {
        $type = $context->find('type');
        switch ($type) {
            case 'RACE':
                return '2.16.840.1.113883.6.238';
            case 'ETHNICITY':
                return '2.16.840.1.113883.6.238';
            case 'SEX':
                return '2.16.840.1.113883.5.1';
            case 'PAYER':
                return '2.16.840.1.113883.3.221.5';
        }
        /**
        def supplemental_data_value_code_system
        case self['type']
        when 'RACE'
        '2.16.840.1.113883.6.238'
        when 'ETHNICITY'
        '2.16.840.1.113883.6.238'
        when 'SEX'
        '2.16.840.1.113883.5.1'
        when 'PAYER'
        '2.16.840.1.113883.3.221.5'
        end
        end
         */
    }

    public function unknown_supplemental_value(\Mustache_Context $context): bool
    {
        $code = $context->find('code');
        return $code == "" || $code == "UNK";
        /*

        def unknown_supplemental_value?
        self['code'] == "" || self['code'] == "UNK"
        end

         */
    }

    public function population_supplemental_data(\Mustache_Context $context)
    {
        /**
        def population_supplemental_data
        reformat_supplemental_data(self['supplemental_data'])
        end
         */
        $supplemental_data = $context->find('supplemental_data');
        return $this->reformat_supplemental_data($supplemental_data);
    }

    protected function reformat_supplemental_data($supplemental_data)
    {
        $supplemental_data_array = [];
        foreach ($supplemental_data as $supplemental_data_key => $counts) {
            foreach ($counts as $key => $value) {
                $supplemental_data_count = ['code' => $key, 'value' => $value, 'type' => $supplemental_data_key];
                $supplemental_data_array[] = $supplemental_data_count;
            }
        }
        return $supplemental_data_array;
        /**
        def reformat_supplemental_data(supplemental_data)
        supplemental_data_array = []
        supplemental_data.each do |supplemental_data_key, counts|
        counts.each do |key, value|
        supplemental_data_count = { code: key, value: value, type: supplemental_data_key }
        supplemental_data_array << supplemental_data_count
        end
        end
        supplemental_data_array
        end
         */
    }
}
