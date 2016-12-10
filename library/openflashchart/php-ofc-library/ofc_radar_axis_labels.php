<?php

class radar_axis_labels
{
	// $labels : array
	public function __construct( $labels )
	{
		$this->labels = $labels;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
}