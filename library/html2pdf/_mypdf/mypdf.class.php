<?php
/**
 * Logiciel : HTML2PDF - classe MyPDF
 * 
 * Convertisseur HTML => PDF
 * Distribué sous la licence LGPL. 
 *
 * @author		Laurent MINGUET <webmaster@html2pdf.fr>
 * @version		3.31
 */

if (!defined('__CLASS_MYPDF__'))
{
	define('__CLASS_MYPDF__', true);
	
	require_once(dirname(__FILE__).'/htmlcolors.php');		// couleurs HTML, contient les memes que le fichier de TCPDF
	require_once(dirname(__FILE__).'/99_fpdf_protection.class.php');		// classe fpdf_protection

	class MyPDF extends FPDF_Protection
	{
		var $footer_param	= array();
		var $transf		= array();
		
		var $underline		= false;
		var $linethrough	= false;
		var $overline		= false;
		
		function MyPDF($sens = 'P', $unit = 'mm', $format = 'A4')
		{
			$this->underline	= false;
			$this->overline		= false;
			$this->linethrough	= false;
			
			$this->FPDF_Protection($sens, $unit, $format);
			$this->AliasNbPages();
			$this->SetMyFooter();
		}
		
		function SetMyFooter($page = null, $date = null, $heure = null, $form = null)
		{
			$page	= ($page ? true : false);
			$date	= ($date ? true : false);
			$heure	= ($heure ? true : false);
			$form	= ($form ? true : false);
			
			$this->footer_param = array('page' => $page, 'date' => $date, 'heure' => $heure, 'form' => $form);
		}
		
		function Footer()
		{ 
			$txt = '';
			if ($this->footer_param['form'])	$txt = (HTML2PDF::textGET('pdf05'));
			if ($this->footer_param['date'] && $this->footer_param['heure'])	$txt.= ($txt ? ' - ' : '').(HTML2PDF::textGET('pdf03'));
			if ($this->footer_param['date'] && !$this->footer_param['heure'])	$txt.= ($txt ? ' - ' : '').(HTML2PDF::textGET('pdf01'));
			if (!$this->footer_param['date'] && $this->footer_param['heure'])	$txt.= ($txt ? ' - ' : '').(HTML2PDF::textGET('pdf02'));
			if ($this->footer_param['page'])	$txt.= ($txt ? ' - ' : '').(HTML2PDF::textGET('pdf04'));
			
			if (strlen($txt)>0)
			{
				$txt = str_replace('[[date_d]]',	date('d'),			$txt);
				$txt = str_replace('[[date_m]]',	date('m'),			$txt);
				$txt = str_replace('[[date_y]]',	date('Y'),			$txt);
				$txt = str_replace('[[date_h]]',	date('H'),			$txt);
				$txt = str_replace('[[date_i]]',	date('i'),			$txt);
				$txt = str_replace('[[date_s]]',	date('s'),			$txt);
				$txt = str_replace('[[current]]',	$this->PageNo(),	$txt);
				$txt = str_replace('[[nb]]',		'{nb}',				$txt);
				
				parent::SetY(-11);
			 	$this->setOverline(false);
			 	$this->setLinethrough(false);
        		$this->SetFont('helvetica', 'I', 8);
				$this->Cell(0, 10, $txt, 0, 0, 'R');
			}
		}
				
		// Draw a polygon
		// Auteur	: Andrew Meier
		// Licence	: Freeware
		function Polygon($points, $style='D')
		{
			if($style=='F')							$op='f';
			elseif($style=='FD' or $style=='DF')	$op='b';
			else									$op='s';
		
			$h = $this->h;
			$k = $this->k;
		
			$points_string = '';
			for($i=0; $i<count($points); $i+=2)
			{
				$points_string .= sprintf('%.2F %.2F', $points[$i]*$k, ($h-$points[$i+1])*$k);
				if($i==0)	$points_string .= ' m ';
				else		$points_string .= ' l ';
			}
			$this->_out($points_string . $op);
		}
		
		function setOverline($value = true)
		{
			$this->overline = $value;
		}

		function setLinethrough($value = true)
		{
			$this->linethrough = $value;
		}
		
		// redéfinition de la methode Text de FPDF afin de rajouter la gestion des overline et linethrough
		function Text($x, $y, $txt)
		{
			//Output a string
			$s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));

			/* MODIFICATION HTML2PDF pour le support de underline, overline, linethrough */
			if ($txt!='')
			{
				if($this->underline)	$s.=' '.$this->_dounderline($x,$y,$txt);
				if($this->overline)		$s.=' '.$this->_dooverline($x,$y,$txt);
				if($this->linethrough)	$s.=' '.$this->_dolinethrough($x,$y,$txt);
			}
			/* FIN MODIFICATION */

			if($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
			$this->_out($s);
		}

		// redéfinition de la methode Cell de FPDF afin de rajouter la gestion des overline et linethrough
		function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
			//Output a cell
			$k=$this->k;
			if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
			{
				//Automatic page break
				$x=$this->x;
				$ws=$this->ws;
				if($ws>0) $this->setWordSpacing(0);
				$this->AddPage($this->CurOrientation,$this->CurPageFormat);
				$this->x=$x;
				if($ws>0) $this->setWordSpacing($ws);
			}
			if($w==0)
				$w=$this->w-$this->rMargin-$this->x;
			$s='';
			if($fill || $border==1)
			{
				if($fill)
					$op=($border==1) ? 'B' : 'f';
				else
					$op='S';
				$s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
			}
			if(is_string($border))
			{
				$x=$this->x;
				$y=$this->y;
				if(strpos($border,'L')!==false)
					$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
				if(strpos($border,'T')!==false)
					$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
				if(strpos($border,'R')!==false)
					$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
				if(strpos($border,'B')!==false)
					$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
			}
			
			if($txt!=='')
			{
				if($align=='R')
					$dx=$w-$this->cMargin-$this->GetStringWidth($txt);
				elseif($align=='C')
					$dx=($w-$this->GetStringWidth($txt))/2;
				else
					$dx=$this->cMargin;
				if($this->ColorFlag)
					$s.='q '.$this->TextColor.' ';
				$txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
				$s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
				
				/* MODIFICATION HTML2PDF pour le support de underline, overline, linethrough */
				if($this->underline)	$s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
				if($this->overline)		$s.=' '.$this->_dooverline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
				if($this->linethrough)	$s.=' '.$this->_dolinethrough($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
				/* FIN MODIFICATION */
				
				if($this->ColorFlag)
					$s.=' Q';
				if($link)
					$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
			}
			
			if($s)
				$this->_out($s);
			$this->lasth=$h;
			if($ln>0)
			{
				//Go to next line
				$this->y+=$h;
				if($ln==1)
					$this->x=$this->lMargin;
			}
			else
				$this->x+=$w;
		}

		function _dounderline($x, $y, $txt)
		{
			//Underline text
			$up=$this->CurrentFont['up'];
			$ut=$this->CurrentFont['ut'];

			$p_x = $x*$this->k;
			$p_y = ($this->h-($y-$up/1000*$this->FontSize))*$this->k;
			$p_w = ($this->GetStringWidth($txt)+$this->ws*substr_count($txt,' '))*$this->k;
			$p_h = -$ut/1000*$this->FontSizePt;

			return sprintf('%.2F %.2F %.2F %.2F re f',$p_x,$p_y,$p_w,$p_h);
		}
		
		function _dooverline($x, $y, $txt)
		{
			//Overline text
			$up=$this->CurrentFont['up'];
			$ut=$this->CurrentFont['ut'];

			$p_x = $x*$this->k;
			$p_y = ($this->h-($y-(1000+1.5*$up)/1000*$this->FontSize))*$this->k;
			$p_w = ($this->GetStringWidth($txt)+$this->ws*substr_count($txt,' '))*$this->k;
			$p_h = -$ut/1000*$this->FontSizePt;
			
			return sprintf('%.2F %.2F %.2F %.2F re f',$p_x,$p_y,$p_w,$p_h);
		}
		
		function _dolinethrough($x, $y, $txt)
		{
			//Linethrough text
			$up=$this->CurrentFont['up'];
			$ut=$this->CurrentFont['ut'];

			$p_x = $x*$this->k;
			$p_y = ($this->h-($y-(1000+2.5*$up)/2000*$this->FontSize))*$this->k;
			$p_w = ($this->GetStringWidth($txt)+$this->ws*substr_count($txt,' '))*$this->k;
			$p_h = -$ut/1000*$this->FontSizePt;
			
			return sprintf('%.2F %.2F %.2F %.2F re f',$p_x,$p_y,$p_w,$p_h);
		}
		
		function cloneFontFrom(&$pdf)
		{
			$this->fonts			= &$pdf->getFonts();
			$this->FontFiles		= &$pdf->getFontFiles();
			$this->diffs			= &$pdf->getDiffs();
		}
		
		function &getFonts() 		{ return $this->fonts; }
		function &getFontFiles()		{ return $this->FontFiles; }
		function &getDiffs() 		{ return $this->diffs; }
		
		function isLoadedFont($fontkey)
		{
			if (isset($this->fonts[$fontkey]))
				return true;
				
			if (isset($this->CoreFonts[$fontkey]))
				return true;
				
			return false;
		}
		
		function setWordSpacing($ws=0.)
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw',$ws*$this->k));
		}
		
		function clippingPathOpen($x = null, $y = null, $w = null, $h = null, $coin_TL=null, $coin_TR=null, $coin_BL=null, $coin_BR=null)
		{
			$path = '';
			if ($x!==null && $y!==null && $w!==null && $h!==null)
			{
				$x1 = $x*$this->k;
				$y1 = ($this->h-$y)*$this->k;

				$x2 = ($x+$w)*$this->k;
				$y2 = ($this->h-$y)*$this->k;

				$x3 = ($x+$w)*$this->k;
				$y3 = ($this->h-$y-$h)*$this->k;

				$x4 = $x*$this->k;
				$y4 = ($this->h-$y-$h)*$this->k;
				
				if ($coin_TL || $coin_TR || $coin_BL || $coin_BR)
				{
					if ($coin_TL) { $coin_TL[0] = $coin_TL[0]*$this->k; $coin_TL[1] =-$coin_TL[1]*$this->k; }
					if ($coin_TR) { $coin_TR[0] = $coin_TR[0]*$this->k; $coin_TR[1] =-$coin_TR[1]*$this->k; }
					if ($coin_BL) { $coin_BL[0] = $coin_BL[0]*$this->k; $coin_BL[1] =-$coin_BL[1]*$this->k; }
					if ($coin_BR) { $coin_BR[0] = $coin_BR[0]*$this->k; $coin_BR[1] =-$coin_BR[1]*$this->k; }

					$MyArc = 4/3 * (sqrt(2) - 1);
					
					if ($coin_TL)
						$path.= sprintf('%.2F %.2F m ', $x1+$coin_TL[0], $y1);
					else
						$path.= sprintf('%.2F %.2F m ', $x1, $y1);
					
					if ($coin_TR)
					{
						$xt1 = ($x2-$coin_TR[0])+$coin_TR[0]*$MyArc;
						$yt1 = ($y2+$coin_TR[1])-$coin_TR[1];
						$xt2 = ($x2-$coin_TR[0])+$coin_TR[0];
						$yt2 = ($y2+$coin_TR[1])-$coin_TR[1]*$MyArc;

						$path.= sprintf('%.2F %.2F l ', $x2-$coin_TR[0], $y2);						
						$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x2, $y2+$coin_TR[1]);
					}
					else
						$path.= sprintf('%.2F %.2F l ', $x2, $y2);

					if ($coin_BR)
					{
						$xt1 = ($x3-$coin_BR[0])+$coin_BR[0];
						$yt1 = ($y3-$coin_BR[1])+$coin_BR[1]*$MyArc;
						$xt2 = ($x3-$coin_BR[0])+$coin_BR[0]*$MyArc;
						$yt2 = ($y3-$coin_BR[1])+$coin_BR[1];

						$path.= sprintf('%.2F %.2F l ', $x3, $y3-$coin_BR[1]);						
						$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x3-$coin_BR[0], $y3);
					}
					else
						$path.= sprintf('%.2F %.2F l ', $x3, $y3);

					if ($coin_BL)
					{
						$xt1 = ($x4+$coin_BL[0])-$coin_BL[0]*$MyArc;
						$yt1 = ($y4-$coin_BL[1])+$coin_BL[1];
						$xt2 = ($x4+$coin_BL[0])-$coin_BL[0];
						$yt2 = ($y4-$coin_BL[1])+$coin_BL[1]*$MyArc;

						$path.= sprintf('%.2F %.2F l ', $x4+$coin_BL[0], $y4);						
						$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x4, $y4-$coin_BL[1]);
					}
					else
						$path.= sprintf('%.2F %.2F l ', $x4, $y4);
				
					if ($coin_TL)
					{
						$xt1 = ($x1+$coin_TL[0])-$coin_TL[0];
						$yt1 = ($y1+$coin_TL[1])-$coin_TL[1]*$MyArc;
						$xt2 = ($x1+$coin_TL[0])-$coin_TL[0]*$MyArc;
						$yt2 = ($y1+$coin_TL[1])-$coin_TL[1];

						$path.= sprintf('%.2F %.2F l ', $x1, $y1+$coin_TL[1]);						
						$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $x1+$coin_TL[0], $y1);
					}
				}
				else
				{
					$path.= sprintf('%.2F %.2F m ', $x1, $y1);
					$path.= sprintf('%.2F %.2F l ', $x2, $y2);
					$path.= sprintf('%.2F %.2F l ', $x3, $y3);
					$path.= sprintf('%.2F %.2F l ', $x4, $y4);
				}

				$path.= ' h W n';
			}
			$this->_out('q '.$path.' ');			
		}
		
		function clippingPathClose()
		{
			$this->_out(' Q');
		}
		
		function drawCourbe($ext1_x, $ext1_y, $ext2_x, $ext2_y, $int1_x, $int1_y, $int2_x, $int2_y, $cen_x, $cen_y)
		{
			$MyArc = 4/3 * (sqrt(2) - 1);
			
			$ext1_x = $ext1_x*$this->k; $ext1_y = ($this->h-$ext1_y)*$this->k;
			$ext2_x = $ext2_x*$this->k; $ext2_y = ($this->h-$ext2_y)*$this->k;
			$int1_x = $int1_x*$this->k; $int1_y = ($this->h-$int1_y)*$this->k;
			$int2_x = $int2_x*$this->k; $int2_y = ($this->h-$int2_y)*$this->k;
			$cen_x	= $cen_x*$this->k;	$cen_y	= ($this->h-$cen_y) *$this->k;
			
			$path = '';
			
			if ($ext1_x-$cen_x!=0)
			{
				$xt1 = $cen_x+($ext1_x-$cen_x);
				$yt1 = $cen_y+($ext2_y-$cen_y)*$MyArc;
				$xt2 = $cen_x+($ext1_x-$cen_x)*$MyArc;
				$yt2 = $cen_y+($ext2_y-$cen_y);
			}
			else
			{
				$xt1 = $cen_x+($ext2_x-$cen_x)*$MyArc;
				$yt1 = $cen_y+($ext1_y-$cen_y);
				$xt2 = $cen_x+($ext2_x-$cen_x);
				$yt2 = $cen_y+($ext1_y-$cen_y)*$MyArc;

			}

			$path.= sprintf('%.2F %.2F m ', $ext1_x, $ext1_y);
			$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2_x, $ext2_y);

			if ($int1_x-$cen_x!=0)
			{
				$xt1 = $cen_x+($int1_x-$cen_x)*$MyArc;
				$yt1 = $cen_y+($int2_y-$cen_y);
				$xt2 = $cen_x+($int1_x-$cen_x);
				$yt2 = $cen_y+($int2_y-$cen_y)*$MyArc;
			}
			else
			{
				$xt1 = $cen_x+($int2_x-$cen_x);
				$yt1 = $cen_y+($int1_y-$cen_y)*$MyArc;
				$xt2 = $cen_x+($int2_x-$cen_x)*$MyArc;
				$yt2 = $cen_y+($int1_y-$cen_y);

			}
			
			$path.= sprintf('%.2F %.2F l ', $int2_x, $int2_y);
			$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $int1_x, $int1_y);

			$this->_out($path . 'f');
		}
		
		function drawCoin($ext1_x, $ext1_y, $ext2_x, $ext2_y, $int_x, $int_y, $cen_x, $cen_y)
		{
			$MyArc = 4/3 * (sqrt(2) - 1);
			
			$ext1_x = $ext1_x*$this->k; $ext1_y = ($this->h-$ext1_y)*$this->k;
			$ext2_x = $ext2_x*$this->k; $ext2_y = ($this->h-$ext2_y)*$this->k;
			$int_x  = $int_x*$this->k;  $int_y  = ($this->h-$int_y)*$this->k;
			$cen_x	= $cen_x*$this->k;	$cen_y	= ($this->h-$cen_y) *$this->k;
			
			$path = '';
			
			if ($ext1_x-$cen_x!=0)
			{
				$xt1 = $cen_x+($ext1_x-$cen_x);
				$yt1 = $cen_y+($ext2_y-$cen_y)*$MyArc;
				$xt2 = $cen_x+($ext1_x-$cen_x)*$MyArc;
				$yt2 = $cen_y+($ext2_y-$cen_y);
			}
			else
			{
				$xt1 = $cen_x+($ext2_x-$cen_x)*$MyArc;
				$yt1 = $cen_y+($ext1_y-$cen_y);
				$xt2 = $cen_x+($ext2_x-$cen_x);
				$yt2 = $cen_y+($ext1_y-$cen_y)*$MyArc;

			}

			$path.= sprintf('%.2F %.2F m ', $ext1_x, $ext1_y);
			$path.= sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $xt1, $yt1, $xt2, $yt2, $ext2_x, $ext2_y);
			$path.= sprintf('%.2F %.2F l ', $int_x, $int_y);
			$path.= sprintf('%.2F %.2F l ', $ext1_x, $ext1_y);
			
			$this->_out($path . 'f');
		}
				
		function startTransform()
		{
			$this->_out('q');
		}
		
		function stopTransform()
		{
			$this->_out('Q');
		}

		function setTranslate($t_x, $t_y)
		{
			// matrice de transformation
			$tm[0]=1;
			$tm[1]=0;
			$tm[2]=0;
			$tm[3]=1;
			$tm[4]=$t_x*$this->k;
			$tm[5]=-$t_y*$this->k;
			
			$this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0],$tm[1],$tm[2],$tm[3],$tm[4],$tm[5]));
		}
		
		function setRotation($angle, $x='', $y='')
		{
			if($x === '') $x=$this->x;
			if($y === '') $y=$this->y;
			
			$y=($this->h-$y)*$this->k;
			$x*=$this->k;
			
			// matrice de transformation
			$tm[0]=cos(deg2rad($angle));
			$tm[1]=sin(deg2rad($angle));
			$tm[2]=-$tm[1];
			$tm[3]=$tm[0];
			$tm[4]=$x+$tm[1]*$y-$tm[0]*$x;
			$tm[5]=$y-$tm[0]*$y-$tm[1]*$x;
			
			$this->_out(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F cm', $tm[0],$tm[1],$tm[2],$tm[3],$tm[4],$tm[5]));
		}
		
		function SetX($x)
		{
			$this->x=$x;
		}
		
		function SetY($y, $resetx=true)
		{
			if ($resetx)
				$this->x=$this->lMargin;
				
			$this->y=$y;
		}
		
		function SetXY($x, $y)
		{
			$this->x=$x;
			$this->y=$y;
		}

		function getK() { return $this->k; }
		function getW() { return $this->w; }
		function getH() { return $this->h; }
		function getPage() { return $this->page; }
		function getlMargin() { return $this->lMargin; }
		function getrMargin() { return $this->rMargin; }
		function gettMargin() { return $this->tMargin; }
		function getbMargin() { return $this->bMargin; }
		function setbMargin($v) { $this->bMargin=$v; }
		function setcMargin($v) { $this->cMargin=$v; }
		function setPage($v) { $this->page=$v; }
		
		function svgSetStyle($styles)
		{
			$style = '';
			
			if ($styles['fill'])
			{
				$this->setMyFillColor($styles['fill']);
				$style.= 'F';
			}
			if ($styles['stroke'] && $styles['stroke-width'])
			{
				$this->SetMyDrawColor($styles['stroke']);
				$this->SetLineWidth($styles['stroke-width']);
				$style.= 'D';
			}
			if ($styles['fill-opacity'])
			{
//				$this->SetAlpha($styles['fill-opacity']);
			}
			
			return $style;
		}
		
		function svgRect($x, $y, $w, $h, $style)
		{
			$xa=$x; $xb=$x+$w; $xc=$x+$w; $xd=$x;
			$ya=$y; $yb=$y; $yc=$y+$h; $yd=$y+$h;
			
			if($style=='F') $op='f';
			elseif($style=='FD' || $style=='DF') $op='B';
			else $op='S';
			$this->_Point($xa, $ya, true);
			$this->_Line($xb, $yb, true);
			$this->_Line($xc, $yc, true);
			$this->_Line($xd, $yd, true);
			$this->_Line($xa, $ya, true);
			$this->_out($op);
		}

		function svgLine($x1, $y1, $x2, $y2)
		{
			$op='S';
			$this->_Point($x1, $y1, true);
			$this->_Line($x2, $y2, true);
			$this->_out($op);
		}
		
		function svgEllipse($x0, $y0, $rx, $ry, $style)
		{
			if($style=='F') $op='f';
			elseif($style=='FD' || $style=='DF') $op='B';
			else $op='S';
			
			$this->_Arc($x0, $y0, $rx, $ry, 0, 2*M_PI, true, true, true);
			$this->_out($op);
		}

		function svgPolygone($actions, $style)
		{
			if($style=='F') $op='f';
			elseif($style=='FD' || $style=='DF') $op='B';
			else $op='S';

			$first = array('', 0, 0);
			$last = array(0, 0, 0, 0);
			
			foreach($actions as $action)
			{
				switch($action[0])
				{
					case 'M':
					case 'm':
						$first = $action;
						$x = $action[1]; $y = $action[2]; $xc = $x; $yc = $y;
						$this->_Point($x, $y, true);
						break;
							
					case 'Z':
					case 'z':
						$x = $first[1]; $y = $first[2]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
					break;	

					case 'L':
						$x = $action[1]; $y = $action[2]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;
						
					case 'l':
						$x = $last[0]+$action[1]; $y = $last[1]+$action[2]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;
						
					case 'H':
						$x = $action[1]; $y = $last[1]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;	
							
					case 'h':
						$x = $last[0]+$action[1]; $y = $last[1]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;	
							
					case 'V':
						$x = $last[0]; $y = $action[1]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;	

					case 'v':
						$x = $last[0]; $y = $last[1]+$action[1]; $xc = $x; $yc = $y;
						$this->_Line($x, $y, true);
						break;	

					case 'A':
						$rx = $action[1];	// rx
						$ry = $action[2];	// ry
						$a = $action[3];	// angle de deviation de l'axe X
						$l = $action[4];	// large-arc-flag 
						$s = $action[5];	// sweep-flag
						$x1 = $last[0];		// begin x
						$y1 = $last[1];		// begin y
						$x2 = $action[6];	// final x
						$y2 = $action[7];	// final y
						
						$this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
						
						$x = $x2; $y = $y2; $xc = $x; $yc = $y;
						break;

					case 'a':
						$rx = $action[1];	// rx
						$ry = $action[2];	// ry
						$a = $action[3];	// angle de deviation de l'axe X
						$l = $action[4];	// large-arc-flag 
						$s = $action[5];	// sweep-flag
						$x1 = $last[0];		// begin x
						$y1 = $last[1];		// begin y
						$x2 = $last[0]+$action[6];	// final x
						$y2 = $last[1]+$action[7];	// final y
						
						$this->_Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a, $l, $s, true);
						
						$x = $x2; $y = $y2; $xc = $x; $yc = $y;
						break;

					case 'C':
						$x1 = $action[1];
						$y1 = $action[2];
						$x2 = $action[3];
						$y2 = $action[4];
						$xf = $action[5];
						$yf = $action[6];
						$this->_Curve($x1, $y1, $x2, $y2,$xf, $yf, true);
						$x = $xf; $y = $yf; $xc = $x2; $yc = $y2;
						break;

					case 'c':
						$x1 = $last[0]+$action[1];
						$y1 = $last[1]+$action[2];
						$x2 = $last[0]+$action[3];
						$y2 = $last[1]+$action[4];
						$xf = $last[0]+$action[5];
						$yf = $last[1]+$action[6];
						$this->_Curve($x1, $y1, $x2, $y2,$xf, $yf, true);
						$x = $xf; $y = $yf; $xc = $x2; $yc = $y2;
						break;

					default:
						echo 'MyPDF Path : <b>'.$action[0].'</b> non reconnu...';
						exit;
				}
				$last = array($x, $y, $xc, $yc);
			}
			$this->_out($op);
		}

		function _Point($x, $y, $trans = false)
		{
			if ($trans) $this->ptTransform($x, $y);
			
			$this->_out(sprintf('%.2F %.2F m', $x, $y));
		}
		
		function _Line($x, $y, $trans = false)
		{
			if ($trans) $this->ptTransform($x, $y);

			$this->_out(sprintf('%.2F %.2F l', $x, $y));
		}
		
		function _Curve($x1, $y1, $x2, $y2, $x3, $y3, $trans = false)
		{
			if ($trans)
			{
				$this->ptTransform($x1, $y1);
				$this->ptTransform($x2, $y2);
				$this->ptTransform($x3, $y3);
			}
			$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1, $y1, $x2, $y2, $x3, $y3));
		}
		
		function _Arc($xc, $yc, $rx, $ry, $a_debut, $a_fin, $sens = true, $draw_first = true, $trans=false)
		{
			$nSeg = 8;
		
			if (!$sens) $a_debut+= M_PI*2.;
			 
			$totalAngle = $a_fin - $a_debut;
			$dt = $totalAngle/$nSeg;
			$dtm = $dt/3;
		
			$x0 = $xc; $y0 = $yc;
		
			$t1 = $a_debut;
			$a0 = $x0 + ($rx * cos($t1));
			$b0 = $y0 + ($ry * sin($t1));
			$c0 = -$rx * sin($t1);
			$d0 = $ry * cos($t1);
			if ($draw_first) $this->_Point($a0, $b0, $trans);
			for ($i = 1; $i <= $nSeg; $i++)
			{
				// Draw this bit of the total curve
				$t1 = ($i * $dt)+$a_debut;
				$a1 = $x0 + ($rx * cos($t1));
				$b1 = $y0 + ($ry * sin($t1));
				$c1 = -$rx * sin($t1);
				$d1 = $ry * cos($t1);
				$this->_Curve(
						$a0 + ($c0 * $dtm), $b0 + ($d0 * $dtm),
						$a1 - ($c1 * $dtm), $b1 - ($d1 * $dtm),
						$a1, $b1,
						$trans
					);
				$a0 = $a1;
				$b0 = $b1;
				$c0 = $c1;
				$d0 = $d1;
			}
		}
		
		function _Arc2($x1, $y1, $x2, $y2, $rx, $ry, $a=0, $l=0, $s=0, $trans = false)
		{
			$v = array();
			$v['x1'] = $x1;
			$v['y1'] = $y1;
			$v['x2'] = $x2;
			$v['y2'] = $y2;
			$v['rx'] = $rx;
			$v['ry'] = $ry;
			$v['xr1'] = $v['x1']*cos($a) - $v['y1']*sin($a); 
			$v['yr1'] = $v['x1']*sin($a) + $v['y1']*cos($a); 
			$v['xr2'] = $v['x2']*cos($a) - $v['y2']*sin($a); 
			$v['yr2'] = $v['x2']*sin($a) + $v['y2']*cos($a); 
			$v['Xr1'] = $v['xr1']/$v['rx']; 
			$v['Yr1'] = $v['yr1']/$v['ry']; 
			$v['Xr2'] = $v['xr2']/$v['rx']; 
			$v['Yr2'] = $v['yr2']/$v['ry']; 
			$v['dXr'] = $v['Xr2'] - $v['Xr1'];
			$v['dYr'] = $v['Yr2'] - $v['Yr1'];
			$v['D'] = $v['dXr']*$v['dXr'] + $v['dYr']*$v['dYr']; 
			
			if ($v['D']==0 || $v['D']>4)
			{
				$this->_Line($x2, $y2, $trans);
				return false;
			}
			
			$v['s1'] = array();
			$v['s2'] = array();
			$v['s1']['t'] = sqrt((4.-$v['D'])/$v['D']);
			$v['s1']['Xr'] = ($v['Xr1']+$v['Xr2'])/2. + $v['s1']['t']*($v['Yr2']-$v['Yr1'])/2.;
			$v['s1']['Yr'] = ($v['Yr1']+$v['Yr2'])/2. + $v['s1']['t']*($v['Xr1']-$v['Xr2'])/2.;
			$v['s1']['xr'] = $v['s1']['Xr']*$v['rx'];
			$v['s1']['yr'] = $v['s1']['Yr']*$v['ry'];
			$v['s1']['x'] = $v['s1']['xr']*cos($a)+$v['s1']['yr']*sin($a); 
			$v['s1']['y'] =-$v['s1']['xr']*sin($a)+$v['s1']['yr']*cos($a); 
			$v['s1']['a1'] = atan2($v['y1']-$v['s1']['y'], $v['x1']-$v['s1']['x']); 
			$v['s1']['a2'] = atan2($v['y2']-$v['s1']['y'], $v['x2']-$v['s1']['x']); 
			if ($v['s1']['a1']>$v['s1']['a2']) $v['s1']['a1']-=2*M_PI;
			
			$v['s2']['t'] = -$v['s1']['t'];
			$v['s2']['Xr'] = ($v['Xr1']+$v['Xr2'])/2. + $v['s2']['t']*($v['Yr2']-$v['Yr1'])/2.;
			$v['s2']['Yr'] = ($v['Yr1']+$v['Yr2'])/2. + $v['s2']['t']*($v['Xr1']-$v['Xr2'])/2.;
			$v['s2']['xr'] = $v['s2']['Xr']*$v['rx']; 
			$v['s2']['yr'] = $v['s2']['Yr']*$v['ry']; 
			$v['s2']['x'] = $v['s2']['xr']*cos($a)+$v['s2']['yr']*sin($a); 
			$v['s2']['y'] =-$v['s2']['xr']*sin($a)+$v['s2']['yr']*cos($a); 
			$v['s2']['a1'] = atan2($v['y1']-$v['s2']['y'], $v['x1']-$v['s2']['x']); 
			$v['s2']['a2'] = atan2($v['y2']-$v['s2']['y'], $v['x2']-$v['s2']['x']); 
			if ($v['s2']['a1']>$v['s2']['a2']) $v['s2']['a1']-=2*M_PI;
			
			if (!$l)
			{
				if ($s)
				{
					$xc = $v['s2']['x'];
					$yc = $v['s2']['y'];
					$a1 = $v['s2']['a1'];
					$a2 = $v['s2']['a2'];
					$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
					
				}
				else
				{
					$xc = $v['s1']['x'];
					$yc = $v['s1']['y'];
					$a1 = $v['s1']['a1'];
					$a2 = $v['s1']['a2'];
					$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
				}
			}
			else
			{
				if ($s)
				{
					$xc = $v['s1']['x'];
					$yc = $v['s1']['y'];
					$a1 = $v['s1']['a1'];
					$a2 = $v['s1']['a2'];
					$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, true, false, $trans);
				}
				else
				{
					$xc = $v['s2']['x'];
					$yc = $v['s2']['y'];
					$a1 = $v['s2']['a1'];
					$a2 = $v['s2']['a2'];
					$this->_Arc($xc, $yc, $rx, $ry, $a1, $a2, false, false, $trans);
				}
			}
		}
		
		function ptTransform(&$x,  &$y, $trans=true)
		{
			$nb = count($this->transf);
			if ($nb)	$m = $this->transf[$nb-1];
			else		$m = array(1,0,0,1,0,0);
			
			list($x,$y) = array(($x*$m[0]+$y*$m[2]+$m[4]),($x*$m[1]+$y*$m[3]+$m[5]));
			
			if ($trans)
			{
				$x = $x*$this->k;
				$y = ($this->h-$y)*$this->k;
			}
			
			return true;
		}
	
		function doTransform($n = null)
		{
			$nb = count($this->transf);
			if ($nb)	$m = $this->transf[$nb-1];
			else		$m = array(1,0,0,1,0,0);
			
			if (!$n) $n = array(1,0,0,1,0,0);

			$n = array(
					$m[0]*$n[0]+$m[2]*$n[1],
					$m[1]*$n[0]+$m[3]*$n[1],
					$m[0]*$n[2]+$m[2]*$n[3],
					$m[1]*$n[2]+$m[3]*$n[3],
					$m[0]*$n[4]+$m[2]*$n[5]+$m[4],  
					$m[1]*$n[4]+$m[3]*$n[5]+$m[5]  
				);	
				
//			echo 'do-'.count($this->transf).' => '.print_r($n, true).'<br>';
			$this->transf[] = $n;
		}
		
		function undoTransform()
		{
			array_pop($this->transf);
//			echo 'un-'.count($this->transf).'<br>';
		}
		
		function setMyDrawColor($c)
		{
			$c = $this->setMyColor($c, true);
			if (!$c) return false;

			$this->DrawColor=$c;
			if($this->page>0) $this->_out($this->DrawColor);
		}
		
		function setMyFillColor($c)
		{
			$c = $this->setMyColor($c);
			if (!$c) return false;

			$this->FillColor=$c;
			$this->ColorFlag=($this->FillColor!=$this->TextColor);
			if($this->page>0) $this->_out($this->FillColor);
		}
			
		function setMyTextColor($c)
		{
			$c = $this->setMyColor($c);
			if (!$c) return false;

			$this->TextColor=$c;
			$this->ColorFlag=($this->FillColor!=$this->TextColor);
		}
		
		function setMyColor($c, $mode = false)
		{
			if (!is_array($c))		return sprintf('%.3F ',$c).($mode ? 'G' : 'g');
			elseif (count($c)==3)	return sprintf('%.3F %.3F %.3F ',$c[0],$c[1],$c[2]).($mode ? 'RG' : 'rg');
			elseif (count($c)==4)	return sprintf('%.3F %.3F %.3F %.3F ',$c[0],$c[1],$c[2],$c[3]).($mode ? 'K' : 'k');
			return null;
		}
	}
}
