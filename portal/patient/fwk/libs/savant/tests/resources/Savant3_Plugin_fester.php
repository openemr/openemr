<?php

/**
 *
 * Example plugin for unit testing.
 * 
 * @version $Id: Savant3_Plugin_fester.php,v 1.1 2005/01/19 22:25:07 pmjones Exp $
 *
 */
class Savant3_Plugin_fester extends Savant3_Plugin {
	public $message = "Fester";
	public $count = 0;
	function __construct() {
		// do some other constructor stuff
		$this->message .= " is printing this: ";
	}
	function fester(&$text) {
		$output = $this->message . $text . " ({$this->count})";
		$this->count ++;
		return $output;
	}
}
?>