<?php
/**
 * syntax for the image tag:
 * 
 * <C:showimage:<filename> <opt. width> <opt. height>>
 * 
 * it is possible to specify the image width without specifying the image height (the image will
 * be scaled to the appropriate height).
 * 
 * supported filename in the image tag:
 * '<C:showimage:'.urlencode('http://myserver.mytld/myimage.png').'>'
 * '<C:showimage:'.urlencode('/home/my home/my image.png').'>'
 * the url encoding is required for:
 *  - files from remote servers (first entry from above)
 *  - local files with whitespaces in the directory or file names
 * 
 * local files without whitespaces in their filename can be specified without 
 * url encoding:
 * 
 * '<C:showimage:/home/myhome/myimage.png>'
 * 
 * the php gd2 extension must be enabled for remote files and local gif files. 
 * local png- and jpeg-files are supported without the gd2 extension. 
 *
 * @author Kristian Herpel <Kristian.Herpel@gmx.net>
 * @author Ole K <ole1986@users.sourceforge.net>
 * @version 1.11 fix for problems with big pictures (pictures with width > 2000px were not shown in the table)
 */
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
include 'Cezpdf.php';

/**
 * cezpdf extension for displaying images in table cells
 */
class CezTableImage extends Cezpdf {
	
	/**
	 * @param Cezpdf $ezpdf current cezpdf object
	 */
	function CezTableImage($p,$o = 'portrait',$t = 'none', $op = array()){
		parent::__construct($p, $o,$t,$op);
        $this->allowedTags .= '|showimage:.*?';
	}
	
	/**
	 * Modification to this function from Cezpdf
	 * line 495-495: added parseImages function
	 * line 518-524: added some condition to calculate cell height
	 * line 528-528: modified to set the new cell height
	 */
    public function ezTable(&$data,$cols='',$title='',$options=''){
        if (!is_array($data)){
            return;
        }

        if (!is_array($cols)){
            // take the columns from the first row of the data set
            reset($data);
            list($k,$v)=each($data);
            if (!is_array($v)){
                return;
            }
            $cols=array();
            foreach ($v as $k1=>$v1){
                $cols[$k1]=$k1;
            }
        }

        if (!is_array($options)){
            $options=array();
        }

        $defaults = array('shaded'=>1,'showBgCol'=>0,'shadeCol'=>array(0.8,0.8,0.8),'shadeCol2'=>array(0.7,0.7,0.7),'fontSize'=>10,'titleFontSize'=>12,
        'titleGap'=>5,'lineCol'=>array(0,0,0),'gap'=>5,'xPos'=>'centre','xOrientation'=>'centre',
        'showHeadings'=>1,'textCol'=>array(0,0,0),'width'=>0,'maxWidth'=>0,'cols'=>array(),'minRowSpace'=>-100,'rowGap'=>2,'colGap'=>5,
        'innerLineThickness'=>1,'outerLineThickness'=>1,'splitRows'=>0,'protectRows'=>1,'nextPageY'=>0,
        'shadeHeadingCol'=>array(), 'gridlines' => EZ_GRIDLINE_DEFAULT
        );

        foreach ($defaults as $key=>$value){
            if (is_array($value)){
                if (!isset($options[$key]) || !is_array($options[$key])){
                    $options[$key]=$value;
                }
            } else {
                if (!isset($options[$key])){
                    $options[$key]=$value;
                }
            }
        }

        // @deprecated Compatibility with 'showLines' option
        if(isset($options['showLines'])){
            switch ($options['showLines']) {
				case 0:	$options['gridlines'] = 0; break;
				case 1:	$options['gridlines'] = EZ_GRIDLINE_DEFAULT; break;
				case 2:	$options['gridlines'] = EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_ROWS; break;
				case 3:	$options['gridlines'] = EZ_GRIDLINE_ROWS; break;
				case 4:	$options['gridlines'] = EZ_GRIDLINE_HEADERONLY; break;
				default: 	$options['gridlines'] = EZ_GRIDLINE_TABLE + EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_COLUMNS;
			}
            unset($options['showLines']);
        }

        $options['gap']=2*$options['colGap'];
        // Use Y Position of Current Page position in Table
        if ($options['nextPageY']) $nextPageY = $this->y;

        $middle = ($this->ez['pageWidth']-$this->ez['rightMargin'])/2+($this->ez['leftMargin'])/2;
        // figure out the maximum widths of the text within each column
        $maxWidth=array();
        foreach ($cols as $colName=>$colHeading){
            $maxWidth[$colName]=0;
        }
        // find the maximum cell widths based on the data
        foreach ($data as $row){
            foreach ($cols as $colName=>$colHeading){
                // BUGFIX #16 ignore empty columns | thanks jafjaf
                if (empty($row[$colName])) continue;
                $w = $this->ezGetTextWidth($options['fontSize'],(string)$row[$colName])*1.01;
                if ($w > $maxWidth[$colName]){
                    $maxWidth[$colName]=$w;
                }
            }
        }
        // and the maximum widths to fit in the headings
        foreach ($cols as $colName=>$colTitle){
            $w = $this->ezGetTextWidth($options['fontSize'],(string)$colTitle)*1.01;
            if ($w > $maxWidth[$colName]){
                $maxWidth[$colName]=$w;
            }
        }

        // calculate the start positions of each of the columns
        $pos=array();
        $x=0;
        $t=$x;
        $adjustmentWidth=0;
        $setWidth=0;
        foreach ($maxWidth as $colName => $w){
            $pos[$colName]=$t;
            // if the column width has been specified then set that here, also total the
            // width avaliable for adjustment
            if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['width']) && $options['cols'][$colName]['width']>0){
                $t=$t+$options['cols'][$colName]['width'];
                $maxWidth[$colName] = $options['cols'][$colName]['width']-$options['gap'];
                $setWidth += $options['cols'][$colName]['width'];
            } else {
                $t=$t+$w+$options['gap'];
                $adjustmentWidth += $w;
                $setWidth += $options['gap'];
            }
        }
        $pos['_end_']=$t;

        // if maxWidth is specified, and the table is too wide, and the width has not been set,
        // then set the width.
        if ($options['width']==0 && $options['maxWidth'] && ($t-$x)>$options['maxWidth']){
            // then need to make this one smaller
            $options['width']=$options['maxWidth'];
        }

        if ($options['width'] && $adjustmentWidth>0 && $setWidth<$options['width']){
            // first find the current widths of the columns involved in this mystery
            $cols0 = array();
            $cols1 = array();
            $xq=0;
            $presentWidth=0;
            $last='';
            foreach ($pos as $colName=>$p){
                if (!isset($options['cols'][$last]) || !isset($options['cols'][$last]['width']) || $options['cols'][$last]['width']<=0){
                    if (strlen($last)){
                        $cols0[$last]=$p-$xq -$options['gap'];
                        $presentWidth += ($p-$xq - $options['gap']);
                    }
                } else {
                    $cols1[$last]=$p-$xq;
                }
                $last=$colName;
                $xq=$p;
            }
            // $cols0 contains the widths of all the columns which are not set
            $neededWidth = $options['width']-$setWidth;
            // if needed width is negative then add it equally to each column, else get more tricky
            if ($presentWidth<$neededWidth){
                foreach ($cols0 as $colName=>$w){
                    $cols0[$colName]+= ($neededWidth-$presentWidth)/count($cols0);
                }
            } else {

                $cnt=0;
                while ($presentWidth>$neededWidth && $cnt<100){
                    $cnt++; // insurance policy
                    // find the widest columns, and the next to widest width
                    $aWidest = array();
                    $nWidest=0;
                    $widest=0;
                    foreach ($cols0 as $colName=>$w){
                        if ($w>$widest){
                            $aWidest=array($colName);
                            $nWidest = $widest;
                            $widest=$w;
                        } else if ($w==$widest){
                            $aWidest[]=$colName;
                        }
                    }
                    // then figure out what the width of the widest columns would have to be to take up all the slack
                    $newWidestWidth = $widest - ($presentWidth-$neededWidth)/count($aWidest);
                    if ($newWidestWidth > $nWidest){
                        // then there is space to set them to this
                        foreach ($aWidest as $colName){
                            $cols0[$colName] = $newWidestWidth;
                        }
                        $presentWidth=$neededWidth;
                    } else {
                        // there is not space, reduce the size of the widest ones down to the next size down, and we
                        // will go round again
                        foreach ($aWidest as $colName){
                            $cols0[$colName] = $nWidest;
                        }
                        $presentWidth=$presentWidth-($widest-$nWidest)*count($aWidest);
                    }
                }
            }
            // $cols0 now contains the new widths of the constrained columns.
            // now need to update the $pos and $maxWidth arrays
            $xq=0;
            foreach ($pos as $colName=>$p){
                $pos[$colName]=$xq;
                if (!isset($options['cols'][$colName]) || !isset($options['cols'][$colName]['width']) || $options['cols'][$colName]['width']<=0){
                    if (isset($cols0[$colName])){
                        $xq += $cols0[$colName] + $options['gap'];
                        $maxWidth[$colName]=$cols0[$colName];
                    }
                } else {
                    if (isset($cols1[$colName])){
                        $xq += $cols1[$colName];
                    }
                }
            }

            $t=$x+$options['width'];
            $pos['_end_']=$t;
        }

        // now adjust the table to the correct location across the page
        switch ($options['xPos']){
            case 'left':
                $xref = $this->ez['leftMargin'];
                break;
            case 'right':
                $xref = $this->ez['pageWidth'] - $this->ez['rightMargin'];
                break;
            case 'centre':
            case 'center':
                $xref = $middle;
                break;
            default:
                $xref = $options['xPos'];
                break;
        }
        switch ($options['xOrientation']){
            case 'left':
                $dx = $xref-$t;
                break;
            case 'right':
                $dx = $xref;
                break;
            case 'centre':
            case 'center':
                $dx = $xref-$t/2;
                break;
        }
        // applied patch #18 alignment fixes for tables and images | thank you Emil Totev
        $dx += $options['colGap'];

        foreach ($pos as $k=>$v){
            $pos[$k]=$v+$dx;
        }
        $x0=$x+$dx;
        $x1=$t+$dx;

        $baseLeftMargin = $this->ez['leftMargin'];
        $basePos = $pos;
        $baseX0 = $x0;
        $baseX1 = $x1;
        // ok, just about ready to make me a table
        $this->setColor($options['textCol'][0],$options['textCol'][1],$options['textCol'][2]);
        $this->setStrokeColor($options['shadeCol'][0],$options['shadeCol'][1],$options['shadeCol'][2]);

        $middle = ($x1+$x0)/2;

        // start a transaction which will be used to regress the table, if there are not enough rows protected
        if ($options['protectRows']>0){
            $this->transaction('start');
            $movedOnce=0;
        }
        $abortTable = 1;
        while ($abortTable){
            $abortTable=0;
            $dm = $this->ez['leftMargin']-$baseLeftMargin;
            foreach ($basePos as $k=>$v){
                $pos[$k]=$v+$dm;
            }
            $x0=$baseX0+$dm;
            $x1=$baseX1+$dm;
            $middle = ($x1+$x0)/2;


            // if the title is set, then do that
            if (strlen($title)){
                $w = $this->getTextWidth($options['titleFontSize'],$title);
                $this->y -= $this->getFontHeight($options['titleFontSize']);
                if ($this->y < $this->ez['bottomMargin']){
                    $this->ezNewPage();
                    // margins may have changed on the newpage
                    $dm = $this->ez['leftMargin']-$baseLeftMargin;
                    foreach ($basePos as $k=>$v){
                        $pos[$k]=$v+$dm;
                    }
                    $x0=$baseX0+$dm;
                    $x1=$baseX1+$dm;
                    $middle = ($x1+$x0)/2;
                    $this->y -= $this->getFontHeight($options['titleFontSize']);
                }
                $this->addText($middle-$w/2,$this->y,$options['titleFontSize'],$title);
                $this->y -= $options['titleGap'];
            }
            // margins may have changed on the newpage
            $dm = $this->ez['leftMargin']-$baseLeftMargin;
            foreach ($basePos as $k=>$v){
                $pos[$k]=$v+$dm;
            }
            $x0=$baseX0+$dm;
            $x1=$baseX1+$dm;

            $y=$this->y; // to simplify the code a bit

            // make the table
            $height = $this->getFontHeight($options['fontSize']);
            $descender = $this->getFontDescender($options['fontSize']);

            $y0=$y+$descender;
            $dy=0;
            if ($options['showHeadings']){
                // patch #9 start
                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3){
                    $this->saveState();
                    $textHeadingsObjectId = $this->openObject();
                    $this->closeObject();
                    $this->addObject($textHeadingsObjectId);
                    $this->reopenObject($textHeadingsObjectId);
                }
                // patch #9 end
                // this function will move the start of the table to a new page if it does not fit on this one
                $headingHeight = $this->ezTableColumnHeadings($cols,$pos,$maxWidth,$height,$descender,$options['rowGap'],$options['fontSize'],$y,$options);
                $y0 = $y+$headingHeight+$options['rowGap'];
                $y1 = $y - $options['rowGap']*2;

                $dm = $this->ez['leftMargin']-$baseLeftMargin;
                foreach ($basePos as $k=>$v){
                    $pos[$k]=$v+$dm;
                }
                $x0=$baseX0+$dm;
                $x1=$baseX1+$dm;
                // patch #9 start
                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3){
                    $this->closeObject();
                    $this->setColor($options['shadeHeadingCol'][0],$options['shadeHeadingCol'][1],$options['shadeHeadingCol'][2],1);
                    $this->filledRectangle($x0-$options['gap']/2,$y+$descender,$x1-$x0,($y0 - $y - $descender));
                    $this->reopenObject($textHeadingsObjectId);
                    $this->closeObject();
                    $this->restoreState();
                 }
                // patch #9 end
            } else {
                $y1 = $y0;
            }
            $firstLine=1;

            // open an object here so that the text can be put in over the shading
            if ($options['shaded'] || $options['showBgCol']){
                $this->saveState();
                $textObjectId = $this->openObject();
                $this->closeObject();
                $this->addObject($textObjectId);
                $this->reopenObject($textObjectId);
            }

            $cnt=0;
            $newPage=0;
            foreach ($data as $row){
                $cnt++;
                // the transaction support will be used to prevent rows being split
                if ($options['splitRows']==0){
                    $pageStart = $this->ezPageCount;
                    if (isset($this->ez['columns']) && $this->ez['columns']['on']==1){
                        $columnStart = $this->ez['columns']['colNum'];
                    }
                    $this->transaction('start');
                    $row_orig = $row;
                    $y_orig = $y;
                    $y0_orig = $y0;
                    $y1_orig = $y1;
                }
                $ok=0;
                $secondTurn=0;
                while(!$abortTable && $ok == 0){

                    $mx=0;
                    $newRow=1;
                    while(!$abortTable && ($newPage || $newRow)){

                        $y-=$height;
                        if ($newPage || $y<$this->ez['bottomMargin'] || (isset($options['minRowSpace']) && $y<($this->ez['bottomMargin']+$options['minRowSpace'])) ){
                            // check that enough rows are with the heading
                            if ($options['protectRows']>0 && $movedOnce==0 && $cnt<=$options['protectRows']){
                                // then we need to move the whole table onto the next page
                                $movedOnce = 1;
                                $abortTable = 1;
                            }

                            $y2=$y-$mx+2*$height+$descender-$newRow*$height;
                            if ($options['gridlines']){
                                $y1+=$descender;
                                if (!$options['showHeadings']){
                                    $y0=$y1;
                                }
                              $this->ezTableDrawLines($pos,$options['gap'], $options['rowGap'],$x0,$x1,$y0,$y1,$y2,$options['lineCol'],$options['innerLineThickness'],$options['outerLineThickness'], $options['gridlines']);
                            }
                            if ($options['shaded'] || $options['showBgCol']){
                                $this->closeObject();
                                $this->restoreState();
                            }
                            $this->ezNewPage();
                            // and the margins may have changed, this is due to the possibility of the columns being turned on
                            // as the columns are managed by manipulating the margins
                            $dm	= $this->ez['leftMargin']-$baseLeftMargin;
                            foreach ($basePos as $k=>$v){
                                $pos[$k]=$v+$dm;
                            }

                            $x0=$baseX0+$dm;
                            $x1=$baseX1+$dm;
                            if ($options['shaded'] || $options['showBgCol']){
                                $this->saveState();
                                $textObjectId = $this->openObject();
                                $this->closeObject();
                                $this->addObject($textObjectId);
                                $this->reopenObject($textObjectId);
                            }
                            $this->setColor($options['textCol'][0],$options['textCol'][1],$options['textCol'][2],1);
                            $y = ($options['nextPageY'])?$nextPageY:($this->ez['pageHeight']-$this->ez['topMargin']);
                            $y0=$y+$descender;
                            $mx=0;
                            if ($options['showHeadings']){
                                // patch #9 start
                                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3){
                                    $this->saveState();
                                    $textHeadingsObjectId = $this->openObject();
                                    $this->closeObject();
                                    $this->addObject($textHeadingsObjectId);
                                    $this->reopenObject($textHeadingsObjectId);
                                    $this->closeObject();
                                    $this->setColor($options['shadeHeadingCol'][0],$options['shadeHeadingCol'][1],$options['shadeHeadingCol'][2],1);
                                    $this->filledRectangle($x0-$options['gap']/2,$y0,$x1-$x0,-($headingHeight-$descender+$options['rowGap']) );
                                    $this->reopenObject($textHeadingsObjectId);
                                    $this->closeObject();
                                    $this->restoreState();
                                }
                                // patch #9 end
                                $this->ezTableColumnHeadings($cols,$pos,$maxWidth,$height,$descender,$options['rowGap'],$options['fontSize'],$y,$options);
                                $y1 = $y - $options['rowGap']*2;

                            } else {
                                $y1=$y0;
                            }
                            $firstLine=1;
                            $y -= $height;
                        }
                        $newRow=0;
                        // write the actual data
                        // if these cells need to be split over a page, then $newPage will be set, and the remaining
                        // text will be placed in $leftOvers
                        $newPage=0;
                        $leftOvers=array();

                        foreach ($cols as $colName=>$colTitle){
                            $this->ezSetY($y+$height);
                            $colNewPage=0;
                            if (isset($row[$colName])){
                                 // KH: parse image tags and calculate the position and size for the images
        						$this->parseImages($row[$colName],$maxWidth[$colName],0,($this->y - $options['rowGap'] - 2 * abs($descender)));
                                if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['link']) && strlen($options['cols'][$colName]['link'])){

                                    //$lines = explode("\n",$row[$colName]);
                                    $lines = preg_split("[\r\n|\r|\n]",$row[$colName]);
                                    if (isset($row[$options['cols'][$colName]['link']]) && strlen($row[$options['cols'][$colName]['link']])){
                                        foreach ($lines as $k=>$v){
                                            $lines[$k]='<c:alink:'.$row[$options['cols'][$colName]['link']].'>'.$v.'</c:alink>';
                                        }
                                    }
                                } else {
                                    //$lines = explode("\n",$row[$colName]);
                                    $lines = preg_split("[\r\n|\r|\n]",$row[$colName]);
                                }
                            } else {
                                $lines = array();
                            }
                            $this->y -= $options['rowGap'];
                            foreach ($lines as $line){
                                $line = $this->ezProcessText($line);
                                $start=1;
                                while (strlen($line) || $start){
                                    // KH: get the height of all images in the current table cell
                                    $_image = $this->checkForImage($line);
                                    if ($_image>0) {
                                        // TODO: Bildbreite anpassen, wenn Bild breiter ist als die Spalte
                                        $_lineheight = $_image + 2 * abs($descender);
                                    } else {
                                        $_lineheight = $height;
                                    }
                                   $start=0;
                                    if (!$colNewPage){
                                    // KH: modified to set the new cell height
                                        $this->y=$this->y-$_lineheight;
                                    }
                                    if ($this->y < $this->ez['bottomMargin']){
                                        // $this->ezNewPage();
                                        $newPage=1; // whether a new page is required for any of the columns
                                        $colNewPage=1; // whether a new page is required for this column
                                    }
                                    if ($colNewPage){
                                        if (isset($leftOvers[$colName])){
                                            $leftOvers[$colName].="\n".$line;
                                        } else {
                                            $leftOvers[$colName] = $line;
                                        }
                                        $line='';
                                    } else {
                                        if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['justification']) ){
                                            $just = $options['cols'][$colName]['justification'];
                                        } else {
                                            $just='left';
                                        }

                                        $line=$this->addText($pos[$colName],$this->y, $options['fontSize'], $line, $maxWidth[$colName], $just);
                                    }
                                }
                            }

                            $dy=$y+$height-$this->y+$options['rowGap'];
                            if ($dy-$height*$newPage>$mx){
                                $mx=$dy-$height*$newPage;
                            }
                        }
                        // set $row to $leftOvers so that they will be processed onto the new page
                        $row = $leftOvers;
                        // now add the shading underneath
                        if ($options['shaded'] && $cnt%2==0){
                            $this->closeObject();
                            $this->setColor($options['shadeCol'][0],$options['shadeCol'][1],$options['shadeCol'][2],1);
                            $this->filledRectangle($x0-$options['gap']/2,$y+$descender+$height-$mx,$x1-$x0,$mx);
                            $this->reopenObject($textObjectId);
                        }

                        if ($options['shaded']==2 && $cnt%2==1){
                            $this->closeObject();
                            $this->setColor($options['shadeCol2'][0],$options['shadeCol2'][1],$options['shadeCol2'][2],1);
                            $this->filledRectangle($x0-$options['gap']/2,$y+$descender+$height-$mx,$x1-$x0,$mx);
                            $this->reopenObject($textObjectId);
                        }

                        // if option showColColor is set,  then can draw filledrectangle column
                        if ($options['showBgCol'] == 1) {
                            foreach ($cols as $colName=>$colTitle){
                                if ( isset($options['cols'][$colName]) && isset($options['cols'][$colName]['bgcolor'])) {
                                    $arrColColor = $options['cols'][$colName]['bgcolor'];
                                    $this->closeObject();
                                    $this->setColor($arrColColor[0],$arrColColor[1],$arrColColor[2],1);
                                    $this->filledRectangle($pos[$colName]-$options['gap']/2,$y+$descender+$height-$mx,$maxWidth[$colName]+$options['gap'],$mx);
                                    $this->reopenObject($textObjectId);
                                }
                            }
                        }
                        if ($options['gridlines'] & EZ_GRIDLINE_ROWS){
                            // then draw a line on the top of each block
                            // $this->closeObject();
                            $this->saveState();
                            $this->setStrokeColor($options['lineCol'][0],$options['lineCol'][1],$options['lineCol'][2],1);
                            // $this->line($x0-$options['gap']/2,$y+$descender+$height-$mx,$x1-$x0,$mx);
                            if ($firstLine){
                                $firstLine=0;
                            } else {
                                $this->setLineStyle($options['innerLineThickness']);
                                $this->line($x0-$options['gap']/2,$y+$descender+$height,$x1-$options['gap']/2,$y+$descender+$height);
                            }

                            $this->restoreState();
                            // $this->reopenObject($textObjectId);
                        }
                    } // end of while
                    $y=$y-$mx+$height;

                    // checking row split over pages
                    if ($options['splitRows']==0){
                        if ( ( ($this->ezPageCount != $pageStart) || (isset($this->ez['columns']) && $this->ez['columns']['on']==1 && $columnStart != $this->ez['columns']['colNum'] )) && $secondTurn==0){
                            // then we need to go back and try that again !
                            $newPage=1;
                            $secondTurn=1;
                            $this->transaction('rewind');
                            $row = $row_orig;
                            $y = $y_orig;
                            $y0 = $y0_orig;
                            $y1 = $y1_orig;
                            $ok=0;

                            $dm = $this->ez['leftMargin']-$baseLeftMargin;
                            foreach ($basePos as $k=>$v){
                                $pos[$k]=$v+$dm;
                            }
                            $x0=$baseX0+$dm;
                            $x1=$baseX1+$dm;

                        } else {
                            $this->transaction('commit');
                            $ok=1;
                        }
                    } else {
                        $ok=1; // don't go round the loop if splitting rows is allowed
                    }
                } // end of while to check for row splitting
                if ($abortTable){
                    if ($ok==0){
                        $this->transaction('abort');
                    }
                    // only the outer transaction should be operational
                    $this->transaction('rewind');
                    $this->ezNewPage();
                    break;
                }

            } // end of foreach ($data as $row)

        } // end of while ($abortTable)

        // table has been put on the page, the rows guarded as required, commit.
        $this->transaction('commit');

        $y2=$y+$descender;
        if ($options['gridlines']){
            $y1+=$descender;
            if (!$options['showHeadings']){
                $y0=$y1;
            }
             $this->ezTableDrawLines($pos,$options['gap'], $options['rowGap'],$x0,$x1,$y0,$y1,$y2,$options['lineCol'],$options['innerLineThickness'],$options['outerLineThickness'], $options['gridlines']);
        }
        // close the object for drawing the text on top
        if ($options['shaded'] || $options['showBgCol']){
            $this->closeObject();
            $this->restoreState();
        }

        $this->y=$y;
        return $y;
    }
	
	/**
	 * parses and returns all image tags from the given text 
	 * 
	 * @param String $text input text
	 * @return array found image tags
	 * @access public
	 */
	function getImagesFromText($text = '') {
		preg_match_all("/\<C:showimage:([^>]*)\>/U",$text,$matches);
		return $matches;
	}

	/**
	 * calculate the total height for all images in text
	 * 
	 * @param String $text input text
	 * @return float total height of all images in the text
	 * @access public
	 */ 
	function checkForImage($text) {
		$height = 0;
		$matches = $this->getImagesFromText($text);
		for ($key=0; $key<count($matches[0]); $key++) {
			$CezShowimageParameter = new CezShowimageParameter();
			$params = $CezShowimageParameter->create($matches[1][$key]);
			if ($params->getHeight()>0) {
				$height = $height + $params->getHeight();
			} else {
				$height = $height + $params->getOriginalHeight();
			}
		}
		return $height;
	}
	
	/**
	 * add a given image to the document
	 * 
	 * @param CezShowimageParameter $params image parameter
	 * @param float $x horizontal position
	 * @param float $y vertical position
     * @param $w width
     * @param $h height
     * @param $quality image quality
	 * @access protected
	 */
	function addImage(& $params, $x = 0, $y = 0, $w=0,$h=0,$quality=75, $angle = 0) {
		if ($params->isUrl()) {
			if (function_exists('imagecreatefrompng')) { 
				switch($params->getImageType()) {
					
					case 3: // png
						$image = imagecreatefrompng($params->getFilename());
					break;
					case 2: // jpeg
						$image = imagecreatefromjpeg($params->getFilename());
					break;
					case 1: // gif
						$image = imagecreatefromgif($params->getFilename());
					break;
				}
				parent::addImage($image, $x, $y, $params->getWidth(), $params->getHeight(), $quality, $angle);
			}
		} else {
			// check for image type, currently only png and jpeg supported	
			switch($params->getImageType()) {
				case 3: // png
					parent::addPngFromFile($params->getFilename(), $x, $y, $params->getWidth(), $params->getHeight(), $angle);
				break;
				case 2: // jpeg
					parent::addJpegFromFile($params->getFilename(), $x, $y, $params->getWidth(), $params->getHeight(), $angle);
				break;
				case 1: // gif
					parent::addGifFromFile($params->getFilename(), $x, $y, $params->getWidth(), $params->getHeight(), $angle);
				break;
			}
		}
	} 
	
	/**
	 * callback method for adding an image to the document
	 * 
	 * note: this method is called by the pdf generator class callback method <code>showImage($info)</code>
	 * 
	 * @param Cezpdf $ezpdf current cezpdf object
	 * @param array $info callback data array (see the callback function part in the R&amp;OS pdf documentation)
	 * @access public 
	 */
	function showimage($info) {
		if ($info['status']=='start') {
			$CezShowimageParameter = new CezShowimageParameter();
			$params =  $CezShowimageParameter->create($info['p']);
			if ($params->isReadable()) {
				$y = ($params->getPositionY()>0) ? $params->getPositionY() : $info['y'];
				$this->addImage($params, $info['x'], $y);
			}
		}
	}
	
	/**
	 * searches for <C:showimage:...> and replaces all occurences with extended tag information
	 * 
	 * the extended informations contains the vertical position and the (calculated) image
	 * dimensions
	 * 
	 * @param String $text text to parse
	 * @param int $maxwidth optional maximal width for the image
	 * @param int $maxheight optional maximal height for the image
	 * @param int $currenty current vertical (y) position on the pdf page
	 * @access public
	 */		
	function parseImages(&$text, $maxwidth = 0, $maxheight = 0, $currenty = 0) {
		$matches = $this->getImagesFromText($text);
		for ($key=0; $key<count($matches[0]); $key++) {
			$CezShowimageParameter = new CezShowimageParameter();
			$params =  $CezShowimageParameter->create($matches[1][$key]);

			if ($params->isReadable()) {
				$width = $params->getWidth();
				$height = $params->getHeight();
				if ($width==0 && $height>0) {
					$width = $height/$params->getOriginalHeight() * $params->getOriginalWidth();
				} elseif ($height==0 && $width>0) {
					$height = $width/$params->getOriginalWidth() * $params->getOriginalHeight();
				} elseif ($height==0 && $width==0) {
					$width = $params->getOriginalWidth();
					$height = $params->getOriginalHeight();
				}
				if ($maxwidth>0 && $width>$maxwidth) {
					$height = ($maxwidth * $height)/$width;
					$width = $maxwidth;
				}
				if ($maxheight>0 && $height>$maxheight) {
					$width = ($maxheight * $width)/$height;
					$height = $maxheight;
				}
				$currenty = $currenty - $height;
				$imagetag = '<C:showimage:'.$params->getFilename().' '.round($width).' '.round($height).' '.$currenty.'>';
			} else {
				$imagetag = '';
			}
			$text = str_replace($matches[0][$key],$imagetag,$text);
		}
	}
	
	/**
	 * check for images an calculate their maximum width
	 * 
	 * note: the image tags will be removed from the input text, this method is required due to further 
	 * calculation in the original pdf class
	 * 
	 * @param String $text input text 
	 * @return float calculated maximum height
	 * @access public
	 */
	function parseMaximumWidth(& $text) {
		$mx = 0;
		$matches = $this->getImagesFromText($text);
		for ($key=0; $key<count($matches[0]); $key++) {
			$CezShowimageParameter = new CezShowimageParameter();
			$params =  $CezShowimageParameter->create($matches[1][$key]);
	    	if ($params->getWidth()>$mx) {
	    		$mx = $params->getWidth();
	    	} elseif ($params->getOriginalWidth()>$mx) {
	    		$mx = $params->getOriginalWidth();
	      	}
	      	// remove the Image-Tag from the text for further calculation
	    	$text = str_replace($matches[0][$key],'',$text);
	    }
	    $mx = min($mx, $this->ezPdf->ez['pageWidth']);
	    return $mx;
	}
}

/**
 * parameter object 
 */
class CezShowimageParameter {
	
	var $filename = '';
	var $width = 0;
	var $height = 0;
	
	var $imageType = 0;
	var $imageHeight = 0;
	var $imageWidth = 0;

	var $IMAGETYPE_GIF = 1;
	var $IMAGETYPE_JPG = 2;
	var $IMAGETYPE_PNG = 3;
	
	var $fileIsUrl = false;
	var $fileIsReadable = false;
	
	var $positionX = 0;
	var $positionY = 0;

	function CezShowimageParameter() {
		
	}
	
	/**
	 * gets the image width
	 * 
	 * @return int image width
	 * @access public
	 */
	function getWidth() {
		return $this->width;
	}
	
	/**
	 * gets the image height 
	 * 
	 * @return int image height
	 * @access public
	 */
	function getHeight() {
		return $this->height;
	}
	
	/**
	 * gets the filename
	 * 
	 * @return String filename
	 * @access public
	 */
	function getFilename() {
		return $this->filename;
	}
	
	function getImagetype() {
		return $this->imageType;
	}
	
	function getOriginalHeight() {
		return $this->imageHeight;
	}
	
	function getOriginalWidth() {
		return $this->imageWidth;
	}
	
	function isUrl() {
		return $this->fileIsUrl;
	}
	
	function isReadable() {
		return $this->fileIsReadable;
	}
	
	function getPositionX() {
		return $this->positionX;
	}
	
	function getPositionY() {
		return $this->positionY;
	}
	
	/**
	 * @access protected
	 */
	function _parse($param = '') {
		$params = explode(" ", $param);
		
		$this->filename = urldecode($params[0]);
		
		if (substr($this->filename,0,5)=='http:' || substr($this->filename,0,6)=='https:') {
			$this->fileIsUrl = true;
			$fd = fopen($this->filename, "r");
			$this->fileIsReadable = ($fd!==false) ? true : false;
			fclose($fd); 
		} else {
			$this->fileIsUrl = false;
			$this->fileIsReadable = is_readable($this->filename);
		}
		
		if (isset($params[2]) && $params[2]>0) {
			$this->height = $params[2];	
		}
		
		if (isset($params[1]) && $params[1]>0) {
			$this->width = $params[1];
		}
		
		if (isset($params[3]) && $params[3]>0) {
			$this->positionY = $params[3];
		}
		
		$_imagesize = getImagesize($this->filename);
		
		if (is_array($_imagesize)) {
			
			$this->imageType = $_imagesize[2];
			
			$this->imageWidth = $_imagesize[0]; 
			$this->imageHeight = $_imagesize[1];

		}
		
	}
		
	/**
	 * creates the parameter object from the given parameter list
	 * 
	 * @param String $param parameter list as string
	 * @return CezShowimageParameter parameter object
	 * @access public
	 */
	function  create($param = '') {
		
		$obj = new CezShowimageParameter();
		$obj->_parse($param);
		return $obj;
		
	}
	
}

?>
