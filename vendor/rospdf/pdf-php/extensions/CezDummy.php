<?php
/**
 * Dummy class extension.
 *
 * This template file is an example on how to use extensions for Cezpdf or Cpdf
 */
set_time_limit(1800);
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
include 'Cezpdf.php';

/**
 * Dummy class description.
 */
class CezDummy extends Cezpdf
{
    public $data = array(
                    ['first' => 'John', 'last' => 'Doe'],
                    ['first' => 'Ole', 'last' => 'K.'],
                );
    /**
     * @param Cezpdf $ezpdf current cezpdf object
     */
    public function __construct($p, $o = 'portrait', $t = 'none', $op = [])
    {
        parent::__construct($p, $o, $t, $op);

        $this->allowedTags .= '|dummy:[0-9]+';
    }

    /*
     * Dummy callback method
     */
    public function dummy($info)
    {
        $item = new CDummyItem($info['p'], $this->data);
        $this->addText($info['x'], $info['y'], $info['height'], $item->fullName);
    }
}

/**
 * additional classes.
 */
class CDummyItem
{
    public $fullName;

    public function __construct($param, &$data)
    {
        error_log('CDummyItem:'.$param);
        $this->_parseName($data, $param);
    }

    public function _parseName(&$data, $param)
    {
        if (isset($data[$param])) {
            $this->fullName = $data[$param]['first'].' '.$data[$param]['last'];
        }
    }
}
