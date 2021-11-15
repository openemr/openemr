<?php

namespace Mpdf\Css;

class DefaultCss
{

	public static $definition = [
		'BODY' => [
			'FONT-FAMILY' => 'serif',
			'FONT-SIZE' => '11pt',
			'TEXT-INDENT' => '0pt',
			'LINE-HEIGHT' => 'normal',
			'MARGIN-COLLAPSE' => 'collapse', // Custom property to collapse top/bottom margins at top/bottom of page - ignored in tables/lists
			'HYPHENS' => 'manual',
			'FONT-KERNING' => 'auto',
		],
		'P' => [
			'MARGIN' => '1.12em 0',
		],
		'H1' => [
			'FONT-SIZE' => '2em',
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '0.67em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'H2' => [
			'FONT-SIZE' => '1.5em',
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '0.75em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'H3' => [
			'FONT-SIZE' => '1.17em',
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '0.83em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'H4' => [
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '1.12em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'H5' => [
			'FONT-SIZE' => '0.83em',
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '1.5em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'H6' => [
			'FONT-SIZE' => '0.75em',
			'FONT-WEIGHT' => 'bold',
			'MARGIN' => '1.67em 0',
			'PAGE-BREAK-AFTER' => 'avoid',
		],
		'HR' => [
			'COLOR' => '#888888',
			'TEXT-ALIGN' => 'center',
			'WIDTH' => '100%',
			'HEIGHT' => '0.2mm',
			'MARGIN-TOP' => '0.83em',
			'MARGIN-BOTTOM' => '0.83em',
		],
		'PRE' => [
			'MARGIN' => '0.83em 0',
			'FONT-FAMILY' => 'monospace',
		],
		'S' => [
			'TEXT-DECORATION' => 'line-through',
		],
		'STRIKE' => [
			'TEXT-DECORATION' => 'line-through',
		],
		'DEL' => [
			'TEXT-DECORATION' => 'line-through',
		],
		'SUB' => [
			'VERTICAL-ALIGN' => 'sub',
			'FONT-SIZE' => '55%', /* Recommended 0.83em */
		],
		'SUP' => [
			'VERTICAL-ALIGN' => 'super',
			'FONT-SIZE' => '55%', /* Recommended 0.83em */
		],
		'U' => [
			'TEXT-DECORATION' => 'underline',
		],
		'INS' => [
			'TEXT-DECORATION' => 'underline',
		],
		'B' => [
			'FONT-WEIGHT' => 'bold',
		],
		'STRONG' => [
			'FONT-WEIGHT' => 'bold',
		],
		'I' => [
			'FONT-STYLE' => 'italic',
		],
		'CITE' => [
			'FONT-STYLE' => 'italic',
		],
		'Q' => [
			'FONT-STYLE' => 'italic',
		],
		'EM' => [
			'FONT-STYLE' => 'italic',
		],
		'VAR' => [
			'FONT-STYLE' => 'italic',
		],
		'SAMP' => [
			'FONT-FAMILY' => 'monospace',
		],
		'CODE' => [
			'FONT-FAMILY' => 'monospace',
		],
		'KBD' => [
			'FONT-FAMILY' => 'monospace',
		],
		'TT' => [
			'FONT-FAMILY' => 'monospace',
		],
		'SMALL' => [
			'FONT-SIZE' => '83%',
		],
		'BIG' => [
			'FONT-SIZE' => '117%',
		],
		'ACRONYM' => [
			'FONT-SIZE' => '77%',
			'FONT-WEIGHT' => 'bold',
		],
		'ADDRESS' => [
			'FONT-STYLE' => 'italic',
		],
		'BLOCKQUOTE' => [
			'MARGIN-LEFT' => '40px',
			'MARGIN-RIGHT' => '40px',
			'MARGIN-TOP' => '1.12em',
			'MARGIN-BOTTOM' => '1.12em',
		],
		'A' => [
			'COLOR' => '#0000FF',
			'TEXT-DECORATION' => 'underline',
		],
		'UL' => [
			'PADDING' => '0 auto',
			'MARGIN-TOP' => '0.83em',
			'MARGIN-BOTTOM' => '0.83em',
		],
		'OL' => [
			'PADDING' => '0 auto',
			'MARGIN-TOP' => '0.83em',
			'MARGIN-BOTTOM' => '0.83em',
		],
		'DL' => [
			'MARGIN' => '1.67em 0',
		],
		'DT' => [],
		'DD' => [
			'PADDING-LEFT' => '40px',
		],
		'TABLE' => [
			'MARGIN' => '0',
			'BORDER-COLLAPSE' => 'separate',
			'BORDER-SPACING' => '2px',
			'EMPTY-CELLS' => 'show',
			'LINE-HEIGHT' => '1.2',
			'VERTICAL-ALIGN' => 'middle',
			'HYPHENS' => 'manual',
			'FONT-KERNING' => 'auto',
		],
		'THEAD' => [],
		'TFOOT' => [],
		'TH' => [
			'FONT-WEIGHT' => 'bold',
			'TEXT-ALIGN' => 'center',
			'PADDING-LEFT' => '0.1em',
			'PADDING-RIGHT' => '0.1em',
			'PADDING-TOP' => '0.1em',
			'PADDING-BOTTOM' => '0.1em',
		],
		'TD' => [
			'PADDING-LEFT' => '0.1em',
			'PADDING-RIGHT' => '0.1em',
			'PADDING-TOP' => '0.1em',
			'PADDING-BOTTOM' => '0.1em',
		],
		'CAPTION' => [
			'TEXT-ALIGN' => 'center',
		],
		'IMG' => [
			'MARGIN' => '0',
			'VERTICAL-ALIGN' => 'baseline',
			'IMAGE-RENDERING' => 'auto',
		],
		'INPUT' => [
			'FONT-FAMILY' => 'sans-serif',
			'VERTICAL-ALIGN' => 'middle',
			'FONT-SIZE' => '0.9em',
		],
		'SELECT' => [
			'FONT-FAMILY' => 'sans-serif',
			'FONT-SIZE' => '0.9em',
			'VERTICAL-ALIGN' => 'middle',
		],
		'TEXTAREA' => [
			'FONT-FAMILY' => 'monospace',
			'FONT-SIZE' => '0.9em',
			'VERTICAL-ALIGN' => 'text-bottom',
		],
		'MARK' => [
			'BACKGROUND-COLOR' => 'yellow',
		],
	];
}
