<?php

class scatter_line
{
	public function __construct( $colour, $width  )
	{
		$this->type      = "scatter_line";
		$this->set_colour( $colour );
		$this->set_width( $width );
	}
	
	public function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;	
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_width( $width )
	{
		$this->width = $width;
	}
	
	public function set_values( $values )
	{
		$this->values = $values;
	}
	
	public function set_step_horizontal()
	{
		$this->stepgraph = 'horizontal';
	}
	
	public function set_step_vertical()
	{
		$this->stepgraph = 'vertical';
	}
	
	public function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
}