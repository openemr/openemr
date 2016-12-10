<?php

class x_legend
{
	public function __construct( $text='' )
	{
		$this->text = $text;
	}
	
	function set_style( $css )
	{
		$this->style = $css;
		//"{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}";		
	}
}