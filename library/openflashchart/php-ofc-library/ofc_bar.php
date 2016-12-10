<?php

include_once 'ofc_bar_base.php';

class bar_value
{
	public function __construct( $top, $bottom=null )
	{
		$this->top = $top;
		
		if( isset( $bottom ) )
			$this->bottom = $bottom;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
}

class bar extends bar_base
{
	public function __construct()
	{
		$this->type      = "bar";
		parent::bar_base();
	}
}

