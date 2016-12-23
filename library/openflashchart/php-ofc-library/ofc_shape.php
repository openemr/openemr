<?php

class shape_point
{
	public function __construct( $x, $y )
	{
		$this->x = $x;
		$this->y = $y;
	}
}

class shape
{
	public function __construct( $colour )
	{
		$this->type		= "shape";
		$this->colour	= $colour;
		$this->values	= array();
	}
	
	public function append_value( $p )
	{
		$this->values[] = $p;	
	}
}