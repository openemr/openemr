<?php
/**
 * Dummy class extension
 * 
 * This template file is an example on how to use extensions for Cezpdf or Cpdf
 */
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
include 'Cezpdf.php';

/**
 * Dummy class description
 */
class CezDummy extends Cezpdf {
	
	var $data = array(
					array('first'=>'John','last'=>'Doe'),
					array('first'=>'Ole','last'=>'K.'),
				);
	/**
	 * @param Cezpdf $ezpdf current cezpdf object
	 */
	function CezDummy($p,$o = 'portrait',$t = 'none', $op = array()){
		parent::__construct($p, $o,$t,$op);
        
        $this->allowedTags .= '|dummy:[0-9]+';
	}
	
	/*
	 * Dummy callback method
	 */
	 function dummy($info){
	 	 $item = new CDummyItem($info['p'], $this->data);
	 	 $this->addText($info['x'],$info['y'], $info['height'], $item->fullName);
	 }
}

/**
 * additional classes
 */
class CDummyItem {
	
	var $fullName;
	
	function __construct($param, &$data) {
		error_log('CDummyItem:'.$param);
		$this->_parseName($data,$param);
	}
	
	function _parseName(&$data, $param){
		if(isset($data[$param])){
			$this->fullName = $data[$param]['first'] . ' '. $data[$param]['last'];
		}
	}
}

?>