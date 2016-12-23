<?php

include_once 'ofc_bar_base.php';

class bar_stack extends bar_base
{
	public function __construct()
	{
		$this->type      = "bar_stack";
		parent::bar_base();
	}
	
	function append_stack( $v )
	{
		$this->append_value( $v );
	}
	
	// an array of HEX colours strings
	// e.g. array( '#ff0000', '#00ff00' );
	function set_colours( $colours )
	{
		$this->colours = $colours;
	}
	
	// an array of bar_stack_value
	function set_keys( $keys )
	{
		$this->keys = $keys;
	}
}

class bar_stack_value
{
	public function __construct( $val, $colour )
	{
		$this->val = $val;
		$this->colour = $colour;
	}
	
	function set_tooltip( $tip )
	{
		$this->tip = $tip;
	} 
}

class bar_stack_key
{
	public function __construct( $colour, $text, $font_size )
	{
		$this->colour = $colour;
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
}