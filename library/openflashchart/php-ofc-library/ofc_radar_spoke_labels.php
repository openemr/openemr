<?php

class radar_spoke_labels
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