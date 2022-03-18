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
    }

    public function population_value(\Mustache_Context $context)
    {
        $value = $context->find('value');
        return round($value);
    }

    public function msrpopl(\Mustache_Context $context)
    {
        $type = $context->find('type');
        return $type == 'MSRPOPL';
    }

    public function not_observ(\Mustache_Context $context)
    {
        $type = $context->find('type');
        return $type != 'OBSERV';
    }

    public function stratification_observation(\Mustache_Context $context)
    {
        return $context->find('observation');
    }

    public function population_observation(\Mustache_Context $context)
    {
        return $context->find('observation');
    }

    public function supplemental_template_ids(\Mustache_Context $context)
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
    }

    public function cms_payer_code(\Mustache_Context $context)
    {
        $code = $context->find('code');
        if ($code[0] && isset(Cat3View::$PAYER_MAP[$code[0]])) {
            return Cat3View::$PAYER_MAP[$code[0]];
        } else {
            return 'D';
        }
    }
}
