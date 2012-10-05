<?php
/*************************************************************************
 * http://www.fpdf.org/en/script/script5.php
 * 
 * @author		Olivier
 * 
 * This script implements EAN13 and UPC-A barcodes (the second being a particular case of the first one). Bars are drawn directly in the PDF (no image is generated).
 * EAN13(float x, float y, string barcode [, float h [, float w]])
 * x: abscissa of barcode.
 * y: ordinate of barcode.
 * barcode: value of barcode.
 * h: height of barcode. Default value: 16.
 * w: width of a bar. Default value: 0.35.
 * 
 * UPC_A(float x, float y, string barcode [, float h [, float w]])
 * 
 * Same parameters.
 * 
 * An EAN13 barcode is made up of 13 digits, UPC-A of 12 (leading zeroes are added if necessary). The last digit is a check digit; if it's not supplied, it will be automatically computed. 
 ************************************************************************/
 
/*************************************************************************
 * http://www.fpdf.org/en/script/script46.php
 * 
 * @author		The-eh
 *
 * This script implements Code 39 barcodes. A Code 39 barcode can encode a string with the following characters: digits (0 to 9), uppercase letters (A to Z) and 8 additional characters (- . space $ / + % *).
 * Code39(float xpos, float ypos, string code [, float baseline [, float height]])
 * xpos: abscissa of barcode
 * ypos: ordinate of barcode
 * code: value of barcode
 * height: bar height
 * baseline: corresponds to the width of a wide bar
 ************************************************************************/


if (!defined('__CLASS_FPDF_CODEBAR__'))
{
	define('__CLASS_FPDF_CODEBAR__', true);
	
	require_once(dirname(__FILE__).'/../_fpdf/fpdf.php');

	class FPDF_Codebar extends FPDF
	{
		var $footer_param = array();
		
		function FPDF_Codebar($sens = 'P', $unit = 'mm', $format = 'A4')
		{
			$this->FPDF($sens, $unit, $format);
		}
		
		function BARCODE_EAN13($x,$y,$w,$h, $barcode,$label=true)
		{
			return $this->Barcode($x,$y,$w,$h, $barcode,13,$label);
		}
		
		function BARCODE_UPC_A($x,$y,$w,$h, $barcode,$label=true)
		{
			return $this->Barcode($x,$y,$w,$h, $barcode,12,$label);
		}
		
		function GetCheckDigit($barcode)
		{
			//Compute the check digit
			$sum=0;
			for($i=1;$i<=11;$i+=2)
				$sum+=3*$barcode{$i};
			for($i=0;$i<=10;$i+=2)
				$sum+=$barcode{$i};
			$r=$sum%10;
			if($r>0)
				$r=10-$r;
			return $r;
		}
		
		function TestCheckDigit($barcode)
		{
			//Test validity of check digit
			$sum=0;
			for($i=1;$i<=11;$i+=2)
				$sum+=3*$barcode{$i};
			for($i=0;$i<=10;$i+=2)
				$sum+=$barcode{$i};
			return ($sum+$barcode{12})%10==0;
		}
		
		function Barcode($x,$y,$code_w,$code_h,$barcode,$len, $label=true)
		{
			//Padding
			$barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
			if($len==12)
				$barcode='0'.$barcode;
			//Add or control the check digit
			if(strlen($barcode)==12)
				$barcode.=$this->GetCheckDigit($barcode);
			elseif(!$this->TestCheckDigit($barcode))
				$this->Error('Incorrect check digit');
			//Convert digits to bars
			$codes=array(
				'A'=>array(
					'0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
					'5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
				'B'=>array(
					'0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
					'5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
				'C'=>array(
					'0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
					'5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
				);
			$parities=array(
				'0'=>array('A','A','A','A','A','A'),
				'1'=>array('A','A','B','A','B','B'),
				'2'=>array('A','A','B','B','A','B'),
				'3'=>array('A','A','B','B','B','A'),
				'4'=>array('A','B','A','A','B','B'),
				'5'=>array('A','B','B','A','A','B'),
				'6'=>array('A','B','B','B','A','A'),
				'7'=>array('A','B','A','B','A','B'),
				'8'=>array('A','B','A','B','B','A'),
				'9'=>array('A','B','B','A','B','A')
				);
			$code='101';
			$p=$parities[$barcode{0}];
			for($i=1;$i<=6;$i++)
				$code.=$codes[$p[$i-1]][$barcode{$i}];
			$code.='01010';
			for($i=7;$i<=12;$i++)
				$code.=$codes['C'][$barcode{$i}];
			$code.='101';
			//Draw bars
			if (strlen($code))
			{
				$w = $code_w / strlen($code);
				for($i=0;$i<strlen($code);$i++)
				{
					if($code{$i}=='1')
						$this->Rect($x+$i*$w,$y,$w,$code_h,'F');
				}
			
				$code_t = substr($barcode,-$len);				
				$code_f = $code_w/strlen($code_t)*$this->k/0.60;
				
				if ($label)
				{
					$this->SetFont('Arial','',$code_f);
					$this->Text($x,$y+$code_h+0.90*$code_f/$this->k,$code_t);
					//Print text uder barcode
					$code_h+= $code_f/$this->k;
				}
			}
			
			return array($code_w, $code_h);
		}
		
		function BARCODE_CODE39($x,$y,$w,$h, $barcode, $label=true)
		{
			$barChar['0'] = 'nnnwwnwnn';
			$barChar['1'] = 'wnnwnnnnw';
			$barChar['2'] = 'nnwwnnnnw';
			$barChar['3'] = 'wnwwnnnnn';
			$barChar['4'] = 'nnnwwnnnw';
			$barChar['5'] = 'wnnwwnnnn';
			$barChar['6'] = 'nnwwwnnnn';
			$barChar['7'] = 'nnnwnnwnw';
			$barChar['8'] = 'wnnwnnwnn';
			$barChar['9'] = 'nnwwnnwnn';
			$barChar['A'] = 'wnnnnwnnw';
			$barChar['B'] = 'nnwnnwnnw';
			$barChar['C'] = 'wnwnnwnnn';
			$barChar['D'] = 'nnnnwwnnw';
			$barChar['E'] = 'wnnnwwnnn';
			$barChar['F'] = 'nnwnwwnnn';
			$barChar['G'] = 'nnnnnwwnw';
			$barChar['H'] = 'wnnnnwwnn';
			$barChar['I'] = 'nnwnnwwnn';
			$barChar['J'] = 'nnnnwwwnn';
			$barChar['K'] = 'wnnnnnnww';
			$barChar['L'] = 'nnwnnnnww';
			$barChar['M'] = 'wnwnnnnwn';
			$barChar['N'] = 'nnnnwnnww';
			$barChar['O'] = 'wnnnwnnwn'; 
			$barChar['P'] = 'nnwnwnnwn';
			$barChar['Q'] = 'nnnnnnwww';
			$barChar['R'] = 'wnnnnnwwn';
			$barChar['S'] = 'nnwnnnwwn';
			$barChar['T'] = 'nnnnwnwwn';
			$barChar['U'] = 'wwnnnnnnw';
			$barChar['V'] = 'nwwnnnnnw';
			$barChar['W'] = 'wwwnnnnnn';
			$barChar['X'] = 'nwnnwnnnw';
			$barChar['Y'] = 'wwnnwnnnn';
			$barChar['Z'] = 'nwwnwnnnn';
			$barChar['-'] = 'nwnnnnwnw';
			$barChar['.'] = 'wwnnnnwnn';
			$barChar[' '] = 'nwwnnnwnn';
			$barChar['*'] = 'nwnnwnwnn';
			$barChar['$'] = 'nwnwnwnnn';
			$barChar['/'] = 'nwnwnnnwn';
			$barChar['+'] = 'nwnnnwnwn';
			$barChar['%'] = 'nnnwnwnwn';
		
			$barcode = '*'.strtoupper($barcode).'*';
			$code = '';
			$w_wide=3; $w_narrow=1; $w_gap=1; $nb_w = 0;
			for($i=0; $i<strlen($barcode); $i++)
			{
				$char = $barcode{$i};
				if(!isset($barChar[$char]))
					$this->Error('Invalid character in barcode: '.$char);
				
				$seq = $barChar[$char];
				$code.= $seq;
				
				for($bar=0; $bar<9; $bar++)
					$nb_w+= ($seq{$bar}=='n' ? $w_narrow : $w_wide);

				$nb_w+= $w_gap;
			}
			
			$w_wide		= $w/$nb_w*$w_wide;
			$w_narrow	= $w/$nb_w*$w_narrow;
			$w_gap		= $w/$nb_w*$w_gap;
			$xt = $x;
			for($i=0; $i<strlen($code); $i++)
			{
				$j = $i%9;
				$w_line = ($code{$i}=='n' ? $w_narrow : $w_wide);
				if(!($j%2)) $this->Rect($xt, $y, $w_line, $h, 'F');
				$xt+= $w_line;
				if ($j==8) $xt+= $w_gap;
			}
			
			$code_w = $xt-$x;
			$code_h = $h;
			$code_t = $barcode;
			$code_f = $code_w/strlen($code_t)*$this->k/0.60/2.5;
			
			if ($label)
			{
				//Print text uder barcode
				$code_h+= $code_f/$this->k;		
				$this->SetFont('Arial','',$code_f);
				$this->Text($x,$y+$h+0.90*$code_f/$this->k,$code_t);
			}
			
			return array($code_w, $code_h);
		}
	}
}