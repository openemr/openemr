<?php
/*************************************************************************
 * http://www.fpdf.org/en/script/script1.php
 * 
 * @author		Olivier
 * 
 * This extension adds bookmark support. The method to add a bookmark is:
 * 
 * function Bookmark(string txt [, int level [, float y]])
 * 
 * txt: the bookmark title.
 * level: the bookmark level (0 is top level, 1 is just below, and so on).
 * y: the y position of the bookmark destination in the current page. -1 means the current position. Default value: 0.
 * 
 * The title must be encoded in ISO Latin-1.
 ************************************************************************/
/*************************************************************************
 * http://www.fpdf.org/en/script/script13.php
 * 
 * @author		Min's
 * 
 * This class prints an index from the created bookmarks. 
 ************************************************************************/
 
if (!defined('__CLASS_FPDF_BOOKMARK__'))
{
	define('__CLASS_FPDF_BOOKMARK__', true);

require_once(dirname(__FILE__).'/00_fpdf_codebar.class.php');

	class FPDF_BookMark extends FPDF_Codebar
	{
		var $outlines=array();
		var $OutlineRoot;
		
		function FPDF_BookMark($orientation='P',$unit='mm',$format='A4')
		{
			$this->FPDF_Codebar($orientation,$unit,$format);
		
		}
		
		function Bookmark($txt, $level=0, $y=0)
		{
			if($y==-1) $y=$this->GetY();
			$this->outlines[]=array('t'=>$txt, 'l'=>$level, 'y'=>($this->h-$y)*$this->k, 'p'=>$this->PageNo());
		}
		
		function _putbookmarks()
		{
			$nb=count($this->outlines);
			if($nb==0) return;
			$lru=array();
			$level=0;
			foreach($this->outlines as $i=>$o)
			{
				if($o['l']>0)
				{
					$parent=$lru[$o['l']-1];
					//Set parent and last pointers
					$this->outlines[$i]['parent']=$parent;
					$this->outlines[$parent]['last']=$i;
					if($o['l']>$level)
					{
						//Level increasing: set first pointer
						$this->outlines[$parent]['first']=$i;
					}
				}
				else
					$this->outlines[$i]['parent']=$nb;
					
				if($o['l']<=$level and $i>0)
				{
					//Set prev and next pointers
					$prev=$lru[$o['l']];
					$this->outlines[$prev]['next']=$i;
					$this->outlines[$i]['prev']=$prev;
				}
				$lru[$o['l']]=$i;
				$level=$o['l'];
			}
			
			//Outline items
			$n=$this->n+1;
			foreach($this->outlines as $i=>$o)
			{
				$this->_newobj();
				$this->_out('<</Title '.$this->_textstring($o['t']));
				$this->_out('/Parent '.($n+$o['parent']).' 0 R');
				if(isset($o['prev']))
				$this->_out('/Prev '.($n+$o['prev']).' 0 R');
				if(isset($o['next']))
				$this->_out('/Next '.($n+$o['next']).' 0 R');
				if(isset($o['first']))
				$this->_out('/First '.($n+$o['first']).' 0 R');
				if(isset($o['last']))
				$this->_out('/Last '.($n+$o['last']).' 0 R');
				$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]',1+2*$o['p'],$o['y']));
				$this->_out('/Count 0>>');
				$this->_out('endobj');
			}
			
			//Outline root
			$this->_newobj();
			$this->OutlineRoot=$this->n;
			$this->_out('<</Type /Outlines /First '.$n.' 0 R');
			$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
			$this->_out('endobj');
		}
		
		function _putresources()
		{
			parent::_putresources();
			$this->_putbookmarks();
		}
		
		function _putcatalog()
		{
			parent::_putcatalog();
			if(count($this->outlines)>0)
			{
				$this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
				$this->_out('/PageMode /UseOutlines');
			}
		}
		
		function CreateIndex(&$obj, $titre = 'Index', $size_title = 20, $size_bookmark = 15, $bookmark_title = true, $display_page = true, $page = null)
		{
			if ($bookmark_title) $this->Bookmark($titre, 0, -1);
			
			//Index title
			$this->SetFontSize($size_title);
			$this->Cell(0,5,$titre,0,1,'C');
			$this->SetFontSize($size_bookmark);
			$this->Ln(10);
			
			$size=sizeof($this->outlines);
			$PageCellSize=$this->GetStringWidth('p. '.$this->outlines[$size-1]['p'])+2;
			for ($i=0;$i<$size;$i++)
			{
				if ($this->getY()+$this->FontSize>=($this->h - $this->bMargin))
				{
					$obj->INDEX_NewPage($page);
					$this->SetFontSize($size_bookmark);
				}
				
				//Offset
				$level=$this->outlines[$i]['l'];
				if($level>0) $this->Cell($level*8);
				
				//Caption
				$str=$this->outlines[$i]['t'];
				$strsize=$this->GetStringWidth($str);
				$avail_size=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*8)-4;
				while ($strsize>=$avail_size)
				{
					$str=substr($str,0,-1);
					$strsize=$this->GetStringWidth($str);
				}
				if ($display_page)
				{
					$this->Cell($strsize+2,$this->FontSize+2,$str);
				
					//Filling dots
					$w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*8)-($strsize+2);
					$nb=$w/$this->GetStringWidth('.');
					$dots=str_repeat('.',$nb);
					$this->Cell($w,$this->FontSize+2,$dots,0,0,'R');

					//Page number
					$this->Cell($PageCellSize,$this->FontSize+2,'p. '.$this->outlines[$i]['p'],0,1,'R');
				}
				else
				{
					$this->Cell($strsize+2,$this->FontSize+2,$str, 0, 1);					
				}
			}
		}
	}
}
