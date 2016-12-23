<?php

class radar_axis
{
	public function __construct( $max )
	{
		$this->set_max( $max );
	}
	
	public function set_max( $max )
	{
		$this->max = $max;
	}
	
	public function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	public function set_stroke( $s )
	{
		$this->stroke = $s;
	}
    
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_grid_colour( $colour )
	{
		$tmp = 'grid-colour';
		$this->$tmp = $colour;
	}
	
	public function set_labels( $labels )
	{
		$this->labels = $labels;
	}
	
	public function set_spoke_labels( $labels )
	{
		$tmp = 'spoke-labels';
		$this->$tmp = $labels;
	}
}

