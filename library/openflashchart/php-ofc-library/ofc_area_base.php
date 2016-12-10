<?php

/**
 * inherits from line
 */
class area extends line
{
	public function __construct()
	{
		$this->type      = "area";
	}
	
	/**
	 * the fill colour
	 */
	public function set_fill_colour( $colour )
	{
		$this->fill = $colour;
	}
	
	/**
	 * sugar: see set_fill_colour
	 */
	public function fill_colour( $colour )
	{
		$this->set_fill_colour( $colour );
		return $this;
	}
	
	public function set_fill_alpha( $alpha )
	{
		$tmp = "fill-alpha";
		$this->$tmp = $alpha;
	}
	
	public function set_loop()
	{
		$this->loop = true;
	}
}
