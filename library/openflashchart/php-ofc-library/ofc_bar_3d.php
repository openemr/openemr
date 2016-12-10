<?php

include_once 'ofc_bar_base.php';

class bar_3d_value
{
	public function __construct( $top )
	{
		$this->top = $top;
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

