<?php

include_once 'Cpdf.php';

/*
 * draw all lines to ezTable output
 */
define('EZ_GRIDLINE_ALL', 31);
/*
 * draw default set of lines to ezTable output, so EZ_GRIDLINE_TABLE, EZ_GRIDLINE_HEADERONLY and EZ_GRIDLINE_COLUMNS
 */
define('EZ_GRIDLINE_DEFAULT', 29); // same as EZ_GRIDLINE_TABLE + EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_COLUMNS
/*
 * draw the outer lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE', 24);
/*
 * draw the outer horizontal lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE_H', 16);
/*
 * draw the outer vertical lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE_V', 8);
/*
 * draw a horizontal line between header and first data row
 */
define('EZ_GRIDLINE_HEADERONLY', 4);
/*
 * draw a horizontal line for each row
 */
define('EZ_GRIDLINE_ROWS', 2);
/*
 * draw a vertical line for each column
 */
define('EZ_GRIDLINE_COLUMNS', 1);

 /**
  * Helper class to create pdf documents via ROS PDF class called 'Cpdf'.
  *
  * This class will take the basic interaction facilities of the Cpdf class
  * and make more useful functions so that the user does not have to
  * know all the ins and outs of pdf presentation to produce something pretty.
  *
  * @category Documents
  * @author Wayne Munro, R&OS Ltd, <http://www.ros.co.nz/pdf>
  * @author Ole Koeckemann <ole.k@web.de>
  * @author Lars Olesen <lars@legestue.net>
  * @author Nicola Asuni <info@tecnick.com>
  * @link https://github.com/rospdf/pdf-php
  */
class Cezpdf extends Cpdf
{
     /**
     * used to store most of the page configuration parameters.
     */
    public $ez = ['fontSize' => 10];
    /**
     * stores the actual vertical position on the page of the writing point, very important.
     */
    public $y;
    /**
     * keep an array of the ids of the pages, making it easy to go back and add page numbers etc.
     */
    public $ezPages = [];
    /**
     * stores the number of pages used in this document.
     */
    public $ezPageCount = 0;

    /**
     * background color/image information.
     */
    protected $ezBackground = [];
    /**
     * Assuming that people don't want to specify the paper size using the absolute coordinates
     * allow a couple of options:
     * orientation can be 'portrait' or 'landscape'
     * or, to actually set the coordinates, then pass an array in as the first parameter.
     * the defaults are as shown
     *
     * 2002-07-24 - Nicola Asuni (info@tecnick.com)
     * Added new page formats (45 standard ISO paper formats and 4 american common formats)
     * paper cordinates are calculated in this way: (inches * 72) where 1 inch = 2.54 cm
     *
     * **$options**<br>
     * if $type equals to 'color'<br>
     *   $options[0] = red-component   of backgroundcolour ( 0 <= r <= 1)<br>
     *   $options[1] = green-component of backgroundcolour ( 0 <= g <= 1)<br>
     *   $options[2] = blue-component  of backgroundcolour ( 0 <= b <= 1)<br>
     * if $type equals to 'image':<br>
     *   $options['img']     = location of image file; URI's are allowed if allow_url_open is enabled in php.ini<br>
     *   $options['width']   = width of background image; default is width of page<br>
     *   $options['height']  = height of background image; default is height of page<br>
     *   $options['xpos']    = horizontal position of background image; default is 0<br>
     *   $options['ypos']    = vertical position of background image; default is 0<br>
     *   $options['repeat']  = repeat image horizontally (1), repeat image vertically (2) or full in both directions (3); default is 0<br>
     *
     * highly recommend to set this->hashed to true when using repeat function<br>
     *
     * @since [0.11.3] added repeat option for images
     *
     * @param mixed  $paper       paper format as string ('A4', 'A5', 'B5', ...) or an array with two/four elements defining the size
     * @param string $orientation either portrait or landscape
     * @param string $type background type - 'none', 'image' or 'color'
     * @param array $options see options from above
     **/
    public function __construct($paper = 'a4', $orientation = 'portrait', $type = 'none', $options = [])
    {
        if (!is_array($paper)) {
            switch (strtoupper($paper)) {
                case '4A0':
                    $size = [0, 0, 4767.87, 6740.79];
                    break;
                case '2A0':
                    $size = [0, 0, 3370.39, 4767.87];
                    break;
                case 'A0':
                    $size = [0, 0, 2383.94, 3370.39];
                    break;
                case 'A1':
                    $size = [0, 0, 1683.78, 2383.94];
                    break;
                case 'A2':
                    $size = [0, 0, 1190.55, 1683.78];
                    break;
                case 'A3':
                    $size = [0, 0, 841.89, 1190.55];
                    break;
                case 'A4':
                default:
                    $size = [0, 0, 595.28, 841.89];
                    break;
                case 'A5':
                    $size = [0, 0, 419.53, 595.28];
                    break;
                case 'A6':
                    $size = [0, 0, 297.64, 419.53];
                    break;
                case 'A7':
                    $size = [0, 0, 209.76, 297.64];
                    break;
                case 'A8':
                    $size = [0, 0, 147.40, 209.76];
                    break;
                case 'A9':
                    $size = [0, 0, 104.88, 147.40];
                    break;
                case 'A10':
                    $size = [0, 0, 73.70, 104.88];
                    break;
                case 'B0':
                    $size = [0, 0, 2834.65, 4008.19];
                    break;
                case 'B1':
                    $size = [0, 0, 2004.09, 2834.65];
                    break;
                case 'B2':
                    $size = [0, 0, 1417.32, 2004.09];
                    break;
                case 'B3':
                    $size = [0, 0, 1000.63, 1417.32];
                    break;
                case 'B4':
                    $size = [0, 0, 708.66, 1000.63];
                    break;
                case 'B5':
                    $size = [0, 0, 498.90, 708.66];
                    break;
                case 'B6':
                    $size = [0, 0, 354.33, 498.90];
                    break;
                case 'B7':
                    $size = [0, 0, 249.45, 354.33];
                    break;
                case 'B8':
                    $size = [0, 0, 175.75, 249.45];
                    break;
                case 'B9':
                    $size = [0, 0, 124.72, 175.75];
                    break;
                case 'B10':
                    $size = [0, 0, 87.87, 124.72];
                    break;
                case 'C0':
                    $size = [0, 0, 2599.37, 3676.54];
                    break;
                case 'C1':
                    $size = [0, 0, 1836.85, 2599.37];
                    break;
                case 'C2':
                    $size = [0, 0, 1298.27, 1836.85];
                    break;
                case 'C3':
                    $size = [0, 0, 918.43, 1298.27];
                    break;
                case 'C4':
                    $size = [0, 0, 649.13, 918.43];
                    break;
                case 'C5':
                    $size = [0, 0, 459.21, 649.13];
                    break;
                case 'C6':
                    $size = [0, 0, 323.15, 459.21];
                    break;
                case 'C7':
                    $size = [0, 0, 229.61, 323.15];
                    break;
                case 'C8':
                    $size = [0, 0, 161.57, 229.61];
                    break;
                case 'C9':
                    $size = [0, 0, 113.39, 161.57];
                    break;
                case 'C10':
                    $size = [0, 0, 79.37, 113.39];
                    break;
                case 'RA0':
                    $size = [0, 0, 2437.80, 3458.27];
                    break;
                case 'RA1':
                    $size = [0, 0, 1729.13, 2437.80];
                    break;
                case 'RA2':
                    $size = [0, 0, 1218.90, 1729.13];
                    break;
                case 'RA3':
                    $size = [0, 0, 864.57, 1218.90];
                    break;
                case 'RA4':
                    $size = [0, 0, 609.45, 864.57];
                    break;
                case 'SRA0':
                    $size = [0, 0, 2551.18, 3628.35];
                    break;
                case 'SRA1':
                    $size = [0, 0, 1814.17, 2551.18];
                    break;
                case 'SRA2':
                    $size = [0, 0, 1275.59, 1814.17];
                    break;
                case 'SRA3':
                    $size = [0, 0, 907.09, 1275.59];
                    break;
                case 'SRA4':
                    $size = [0, 0, 637.80, 907.09];
                    break;
                case 'LETTER':
                    $size = [0, 0, 612.00, 792.00];
                    break;
                case 'LEGAL':
                    $size = [0, 0, 612.00, 1008.00];
                    break;
                case 'EXECUTIVE':
                    $size = [0, 0, 521.86, 756.00];
                    break;
                case 'FOLIO':
                    $size = [0, 0, 612.00, 936.00];
                    break;
            }
            switch (strtolower($orientation)) {
                case 'landscape':
                    $a = $size[3];
                    $size[3] = $size[2];
                    $size[2] = $a;
                    break;
            }
        } else {
            if (count($paper) > 2) {
                // then an array was sent it to set the size
                $size = $paper;
            } else { //size in centimeters has been passed
                $size[0] = 0;
                $size[1] = 0;
                $size[2] = ($paper[0] / 2.54) * 72;
                $size[3] = ($paper[1] / 2.54) * 72;
            }
        }
        parent::__construct($size);
        $this->ez['pageWidth'] = $size[2];
        $this->ez['pageHeight'] = $size[3];

        // also set the margins to some reasonable defaults
        $this->ez['topMargin'] = 30;
        $this->ez['bottomMargin'] = 30;
        $this->ez['leftMargin'] = 30;
        $this->ez['rightMargin'] = 30;

        // set the current writing position to the top of the first page
        $this->y = $this->ez['pageHeight'] - $this->ez['topMargin'];
        // and get the ID of the page that was created during the instancing process.
        $this->ezPages[1] = $this->getFirstPageId();
        $this->ezPageCount = 1;

        switch ($type) {
            case 'color':
            case 'colour':
                $this->ezBackground['type'] = 'color';
                $this->ezBackground['color'] = $options;
                break;
            case 'image':
                if (!isset($options['img'])) {
                    $errormsg = 'Background Image not set.';
                    break;
                }

                if (!file_exists($options['img'])) {
                    $errormsg = "Background Image does not exists: '".$options['img']."'";
                    break;
                }

                $im = getimagesize($options['img']);
                if ($im === false) {
                    $errormsg = "Background Image is invalid: '".$options['img']."'";
                    break;
                }

                $this->ezBackground['type'] = 'image';
                $this->ezBackground['image'] = $options['img'];
                $this->ezBackground['format'] = $im[2];
                $this->ezBackground['repeat'] = $options['repeat'];

                if (isset($options['width']) && is_numeric($options['width'])) {
                    $this->ezBackground['width'] = $options['width'];
                } else {
                    $this->ezBackground['width'] = $this->ez['pageWidth'];
                }
                if (isset($options['height']) && is_numeric($options['height'])) {
                    $this->ezBackground['height'] = $options['height'];
                } else {
                    $this->ezBackground['height'] = $this->ez['pageHeight'];
                }
                if (isset($options['xpos']) && is_numeric($options['xpos'])) {
                    $this->ezBackground['xpos'] = $options['xpos'];
                } else {
                    $this->ezBackground['xpos'] = 0;
                }
                if (isset($options['ypos']) && is_numeric($options['ypos'])) {
                    $this->ezBackground['ypos'] = $options['ypos'];
                } else {
                    $this->ezBackground['ypos'] = 0;
                }
                break;
            case 'none':
            default:
                $this->ezBackground['type'] = 'none';
                break;
        }

        $this->setBackground();
    }

    /**
     * set the background image or color on all pages
     * gets executed in constructor and in ezNewPage.
     */
    protected function setBackground()
    {
        if (isset($this->ezBackground['type'])) {
            switch ($this->ezBackground['type']) {
                case 'color':
                    if (isset($this->ezBackground['color']) && is_array($this->ezBackground['color']) && count($this->ezBackground['color']) == 3) {
                        $this->saveState();
                        $this->setColor($this->ezBackground['color'][0], $this->ezBackground['color'][1], $this->ezBackground['color'][2], 1);
                        $this->filledRectangle(0, 0, $this->ez['pageWidth'], $this->ez['pageHeight']);
                        $this->restoreState();
                    }
                    break;
                case 'image':
                    $ypos = $this->ezBackground['ypos'];
                    $xpos = $this->ezBackground['xpos'];

                    if ($this->ezBackground['repeat'] == 1) {
                        $xpos = 0;
                    }
                    
                    if ($this->ezBackground['repeat'] == 2) {
                        $ypos = 0;
                    }

                    $this->addBackgroundImage($xpos, $ypos);

                    if ($this->ezBackground['repeat'] & 1) { // repeat-x
                        $numX = ceil($this->ez['pageWidth'] / $this->ezBackground['width']);
                        for ($i = 0; $i < $numX; ++$i) {
                            $xpos = ($this->ezBackground['width'] * $i);
                            $this->addBackgroundImage($xpos, $ypos);
                        }
                    }

                    if ($this->ezBackground['repeat'] & 2) { // repeat-y
                        $numY = ceil($this->ez['pageHeight'] / $this->ezBackground['height']);
                        for ($i = 0; $i < $numY; ++$i) {
                            $ypos = ($this->ezBackground['height'] * $i);
                            $this->addBackgroundImage($xpos, $ypos);
                        }
                    }

                    if ($this->ezBackground['repeat'] == 3) { // repeat all
                        $numX = ceil($this->ez['pageWidth'] / $this->ezBackground['width']);
                        $numY = ceil($this->ez['pageHeight'] / $this->ezBackground['height']);

                        for ($i = 0; $i < $numX; ++$i) {
                            $xpos = ($this->ezBackground['width'] * $i);
                            for ($j = 0; $j < $numY; ++$j) {
                                $ypos = ($this->ezBackground['height'] * $j);
                                $this->addBackgroundImage($xpos, $ypos);
                            }
                        }
                    }

                    break;
                case 'none':
                default:
                    break;
            }
        }
    }

    /**
     * add background image for JPEG and PNG file format
     * Especially used for repeating function.
     *
     * @param float $xOffset horizontal offset
     * @param float $yOffset vertical offset
     */
    private function addBackgroundImage($xOffset = 0, $yOffset = 0)
    {
        switch ($this->ezBackground['format']) {
            case IMAGETYPE_JPEG:
                $this->addJpegFromFile($this->ezBackground['image'], $xOffset, $yOffset, $this->ezBackground['width'], $this->ezBackground['height']);
                break;
            case IMAGETYPE_PNG:
                $this->addPngFromFile($this->ezBackground['image'], $xOffset, $yOffset, $this->ezBackground['width'], $this->ezBackground['height']);
                break;
        }
    }

    /**
     * setup a margin on document page.
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezSetMargins(50,50,50,50)
     * </pre>
     *
     * @param float $top    top margin
     * @param float $bottom botom margin
     * @param float $left   left margin
     * @param float $right  right margin
     */
    public function ezSetMargins($top, $bottom, $left, $right)
    {
        // sets the margins to new values
        $this->ez['topMargin'] = $top;
        $this->ez['bottomMargin'] = $bottom;
        $this->ez['leftMargin'] = $left;
        $this->ez['rightMargin'] = $right;
        // check to see if this means that the current writing position is outside the
        // writable area
        if ($this->y > $this->ez['pageHeight'] - $top) {
            // then move y down
            $this->y = $this->ez['pageHeight'] - $top;
        }
        if ($this->y < $bottom) {
            // then make a new page
            $this->ezNewPage();
        }
    }

    /**
     * setup a margin on document page.
     *
     * @author 2002-07-24: Nicola Asuni (info@tecnick.com)
     *
     * @param float $top    top margin in cm
     * @param float $bottom botom margin in cm
     * @param float $left   left margin in cm
     * @param float $right  right margin in cm
     */
    public function ezSetCmMargins($top, $bottom, $left, $right)
    {
        $top = ($top / 2.54) * 72;
        $bottom = ($bottom / 2.54) * 72;
        $left = ($left / 2.54) * 72;
        $right = ($right / 2.54) * 72;
        $this->ezSetMargins($top, $bottom, $left, $right);
    }

    /**
     * create a new page.
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezNewPage()
     * </pre>
     */
    public function ezNewPage()
    {
        $pageRequired = 1;
        if (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1) {
            // check if this is just going to a new column
            // increment the column number
            //echo 'HERE<br>';
            ++$this->ez['columns']['colNum'];
            //echo $this->ez['columns']['colNum'].'<br>';
            if ($this->ez['columns']['colNum'] <= $this->ez['columns']['options']['num']) {
                // then just reset to the top of the next column
                $pageRequired = 0;
            } else {
                $this->ez['columns']['colNum'] = 1;
                $this->ez['topMargin'] = $this->ez['columns']['margins'][2];
            }

            $width = $this->ez['columns']['width'];
            $this->ez['leftMargin'] = $this->ez['columns']['margins'][0] + ($this->ez['columns']['colNum'] - 1) * ($this->ez['columns']['options']['gap'] + $width);
            $this->ez['rightMargin'] = $this->ez['pageWidth'] - $this->ez['leftMargin'] - $width;
        }

        if ($pageRequired) {
            // make a new page, setting the writing point back to the top
            $this->y = $this->ez['pageHeight'] - $this->ez['topMargin'];
            // make the new page with a call to the basic class.
            ++$this->ezPageCount;
            if (isset($this->ez['insertMode']) && $this->ez['insertMode'] == 1) {
                $id = $this->ezPages[$this->ezPageCount] = $this->newPage(1, $this->ez['insertOptions']['id'], $this->ez['insertOptions']['pos']);
                // then manipulate the insert options so that inserted pages follow each other
                $this->ez['insertOptions']['id'] = $id;
                $this->ez['insertOptions']['pos'] = 'after';
            } else {
                $this->ezPages[$this->ezPageCount] = $this->newPage();
            }
            $this->setBackground();
        } else {
            $this->y = $this->ez['pageHeight'] - $this->ez['topMargin'];
        }
    }

    /**
     * starts to flow text into columns.
     *
     * @param $options array with option for gaps and number of columns - default: ['gap'=>10, 'num'=>2]
     */
    public function ezColumnsStart($options = [])
    {
        // start from the current y-position, make the set number of columne
        if (isset($this->ez['columns']) && $this->ez['columns'] == 1) {
            // if we are already in a column mode then just return.
            return;
        }
        $def = ['gap' => 10, 'num' => 2];
        foreach ($def as $k => $v) {
            if (!isset($options[$k])) {
                $options[$k] = $v;
            }
        }
        // setup the columns
        $this->ez['columns'] = ['on' => 1, 'colNum' => 1];

        // store the current margins
        $this->ez['columns']['margins'] = array(
            $this->ez['leftMargin'],
            $this->ez['rightMargin'],
            $this->ez['topMargin'],
            $this->ez['bottomMargin'],
        );
        // and store the settings for the columns
        $this->ez['columns']['options'] = $options;
        // then reset the margins to suit the new columns
        // safe enough to assume the first column here, but start from the current y-position
        $this->ez['topMargin'] = $this->ez['pageHeight'] - $this->y;
        $width = ($this->ez['pageWidth'] - $this->ez['leftMargin'] - $this->ez['rightMargin'] - ($options['num'] - 1) * $options['gap']) / $options['num'];
        $this->ez['columns']['width'] = $width;
        $this->ez['rightMargin'] = $this->ez['pageWidth'] - $this->ez['leftMargin'] - $width;
    }

    /**
     * stops the multi column mode.
     */
    public function ezColumnsStop()
    {
        if (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1) {
            $this->ez['columns']['on'] = 0;
            $this->ez['leftMargin'] = $this->ez['columns']['margins'][0];
            $this->ez['rightMargin'] = $this->ez['columns']['margins'][1];
            $this->ez['topMargin'] = $this->ez['columns']['margins'][2];
            $this->ez['bottomMargin'] = $this->ez['columns']['margins'][3];
        }
    }

    /**
     * puts the document into insert mode. new pages are inserted until this is re-called with status=0
     * by default pages will be inserted at the start of the document.
     *
     * @param $status
     * @param $pageNum
     * @param $pos
     */
    public function ezInsertMode($status = 1, $pageNum = 1, $pos = 'before')
    {
        switch ($status) {
            case '1':
                if (isset($this->ezPages[$pageNum])) {
                    $this->ez['insertMode'] = 1;
                    $this->ez['insertOptions'] = ['id' => $this->ezPages[$pageNum], 'pos' => $pos];
                }
                break;
            case '0':
                $this->ez['insertMode'] = 0;
                break;
        }
    }

    /**
     * sets the Y position of the document.
     * If Y reaches the bottom margin a new page is generated.
     *
     * @param float $y Y position
     */
    public function ezSetY($y)
    {
        // used to change the vertical position of the writing point.
        $this->y = $y;
        if ($this->y < $this->ez['bottomMargin']) {
            // then make a new page
            $this->ezNewPage();
        }
    }

    /**
     * changes the Y position of the document by writing positive or negative numbers.
     * If Y reaches the bottom margin a new page is generated.
     *
     * @param $dy
     * @param $mod
     */
    public function ezSetDy($dy, $mod = '')
    {
        // used to change the vertical position of the writing point.
        // changes up by a positive increment, so enter a negative number to go
        // down the page
        // if $mod is set to 'makeSpace' and a new page is forced, then the pointed will be moved
        // down on the new page, this will allow space to be reserved for graphics etc.
        $this->y += $dy;
        if ($this->y < $this->ez['bottomMargin']) {
            // then make a new page
            $this->ezNewPage();
            if ($mod == 'makeSpace') {
                $this->y += $dy;
            }
        }
    }

    /**
     * put page numbers on the pages from here.
     * place then on the 'pos' side of the coordinates (x,y).
     * use the given 'pattern' for display, where (PAGENUM} and {TOTALPAGENUM} are replaced
     * as required.
     * Adjust this function so that each time you 'start' page numbers then you effectively start a different batch
     * return the number of the batch, so that they can be stopped in a different order if required.
     *
     * @param float $x X-coordinate
     * @param float $y Y-coordinate
     * @param $size
     * @param string $pos     use either right or left
     * @param string $pattern pattern where {PAGENUM} is the current page number and {TOTALPAGENUM} is the page count in total
     * @param int    $num     optional. make the first page this number, the number of total pages will be adjusted to account for this
     *
     * @return int count of ez['pageNumbering']
     */
    public function ezStartPageNumbers($x, $y, $size, $pos = 'left', $pattern = '{PAGENUM} of {TOTALPAGENUM}', $num = 1)
    {
        if (!$pos || !strlen($pos)) {
            $pos = 'left';
        }
        if (!$pattern || !strlen($pattern)) {
            $pattern = '{PAGENUM} of {TOTALPAGENUM}';
        }
        if (!isset($this->ez['pageNumbering'])) {
            $this->ez['pageNumbering'] = [];
        }
        $i = count($this->ez['pageNumbering']);
        $this->ez['pageNumbering'][$i][$this->ezPageCount] = ['x' => $x, 'y' => $y, 'pos' => $pos, 'pattern' => $pattern, 'num' => $num, 'size' => $size];

        return $i;
    }

    /**
     * returns the number of a page within the specified page numbering system.
     *
     * @param $pageNum
     * @param $i
     *
     * @return int page number
     */
    public function ezWhatPageNumber($pageNum, $i = 0)
    {
        // given a particular generic page number (ie, document numbered sequentially from beginning),
        // return the page number under a particular page numbering scheme ($i)
        $num = 0;
        $start = 1;
        $startNum = 1;
        if (!isset($this->ez['pageNumbering'])) {
            $this->addMessage('WARNING: page numbering called for and wasn\'t started with ezStartPageNumbers');

            return 0;
        }
        foreach ($this->ez['pageNumbering'][$i] as $k => $v) {
            if ($k <= $pageNum) {
                if (is_array($v)) {
                    // start block
                    if (strlen($v['num'])) {
                        // a start was specified
                        $start = $v['num'];
                        $startNum = $k;
                        $num = $pageNum - $startNum + $start;
                    }
                } else {
                    // stop block
                    $num = 0;
                }
            }
        }

        return $num;
    }

    /**
     * receive the current page number.
     *
     * @return int page number
     */
    public function ezGetCurrentPageNumber()
    {
        // return the strict numbering (1,2,3,4..) number of the current page
        return $this->ezPageCount;
    }

    /**
     * stops the custom page numbering.
     *
     * @param $stopTotal
     * @param $next
     * @param $i
     */
    public function ezStopPageNumbers($stopTotal = 0, $next = 0, $i = 0)
    {
        // if stopTotal=1 then the totalling of pages for this number will stop too
        // if $next=1, then do this page, but not the next, else do not do this page either
        // if $i is set, then stop that particular pagenumbering sequence.
        if (!isset($this->ez['pageNumbering'])) {
            $this->ez['pageNumbering'] = [];
        }
        if ($next && isset($this->ez['pageNumbering'][$i][$this->ezPageCount]) && is_array($this->ez['pageNumbering'][$i][$this->ezPageCount])) {
            // then this has only just been started, this will over-write the start, and nothing will appear
            // add a special command to the start block, telling it to stop as well
            if ($stopTotal) {
                $this->ez['pageNumbering'][$i][$this->ezPageCount]['stoptn'] = 1;
            } else {
                $this->ez['pageNumbering'][$i][$this->ezPageCount]['stopn'] = 1;
            }
        } else {
            if ($stopTotal) {
                $this->ez['pageNumbering'][$i][$this->ezPageCount] = 'stopt';
            } else {
                $this->ez['pageNumbering'][$i][$this->ezPageCount] = 'stop';
            }
            if ($next) {
                $this->ez['pageNumbering'][$i][$this->ezPageCount] .= 'n';
            }
        }
    }

    /**
     * internal function to search the page number.
     *
     * @see Cezpdf::ezStartPageNumbers()
     *
     * @param $lbl
     * @param $tmp
     *
     * @return int page number
     */
    private function ezPageNumberSearch($lbl, &$tmp)
    {
        foreach ($tmp as $i => $v) {
            if (is_array($v)) {
                if (isset($v[$lbl])) {
                    return $i;
                }
            } else {
                if ($v == $lbl) {
                    return $i;
                }
            }
        }

        return 0;
    }

    /**
     * save page numbers for paging.
     *
     * @see Cezpdf::ezStartPageNumbers()
     */
    private function addPageNumbers()
    {
        // this will go through the pageNumbering array and add the page numbers are required
        if (isset($this->ez['pageNumbering'])) {
            $totalPages1 = $this->ezPageCount;
            $tmp1 = $this->ez['pageNumbering'];
            $status = 0;
            foreach ($tmp1 as $i => $tmp) {
                // do each of the page numbering systems
                // firstly, find the total pages for this one
                $k = $this->ezPageNumberSearch('stopt', $tmp);
                if ($k && $k > 0) {
                    $totalPages = $k - 1;
                } else {
                    $l = $this->ezPageNumberSearch('stoptn', $tmp);
                    if ($l && $l > 0) {
                        $totalPages = $l;
                    } else {
                        $totalPages = $totalPages1;
                    }
                }
                foreach ($this->ezPages as $pageNum => $id) {
                    if (isset($tmp[$pageNum])) {
                        if (is_array($tmp[$pageNum])) {
                            // then this must be starting page numbers
                            $status = 1;
                            $info = $tmp[$pageNum];
                            $info['dnum'] = $info['num'] - $pageNum;
                            // also check for the special case of the numbering stopping and starting on the same page
                            if (isset($info['stopn']) || isset($info['stoptn'])) {
                                $status = 2;
                            }
                        } elseif ($tmp[$pageNum] == 'stop' || $tmp[$pageNum] == 'stopt') {
                            // then we are stopping page numbers
                            $status = 0;
                        } elseif ($status == 1 && ($tmp[$pageNum] == 'stoptn' || $tmp[$pageNum] == 'stopn')) {
                            // then we are stopping page numbers
                            $status = 2;
                        }
                    }
                    if ($status) {
                        // then add the page numbering to this page
                        if (strlen($info['num'])) {
                            $num = $pageNum + $info['dnum'];
                        } else {
                            $num = $pageNum;
                        }
                        $total = $totalPages + $num - $pageNum;
                        $pat = str_replace('{PAGENUM}', $num, $info['pattern']);
                        $pat = str_replace('{TOTALPAGENUM}', $total, $pat);
                        $this->reopenObject($id);
                        switch ($info['pos']) {
                            case 'left':
                                $this->addText($info['x'], $info['y'], $info['size'], $pat);
                                break;
                            case 'center':
                                $w = $this->getTextWidth($info['size'], $pat);
                                $this->addText($info['x'] - ($w / 2), $info['y'], $info['size'], $pat);
                                break;
                            default:
                                $w = $this->getTextWidth($info['size'], $pat);
                                $this->addText($info['x'] - $w, $info['y'], $info['size'], $pat);
                                break;
                        }
                        $this->closeObject();
                    }
                    if ($status == 2) {
                        $status = 0;
                    }
                }
            }
        }
    }

    /**
     * some clean up function (especially used after paging).
     */
    private function cleanUp()
    {
        $this->addPageNumbers();
    }

    /**
     * internal method to draw different table lines
     * called by ezTable() method.
     *
     * @param array $pos    start position of each column
     * @param float $gap    column gap defined in ezTable()
     * @param float $rowGap row gap defined in ezTable()
     * @param float $x0     some coordinates
     * @param $x1 some X coordinates
     * @param $y0 some Y coordinates
     * @param $y1 some Y coordinates
     * @param $y2 some Y coordinates
     * @param array $col       line color as array
     * @param float $inner     inner line thickness
     * @param float $outer     outer line thickness
     * @param int   $gridlines - what gridlines to display
     */
    protected function ezTableDrawLines($pos, $gap, $rowGap, $x0, $x1, $y0, $y1, $y2, $col, $inner, $outer, $gridlines)
    {
        $x0 = 1000;
        $x1 = 0;
        $this->setStrokeColor($col[0], $col[1], $col[2]);

// Vertical gridlines (including outline)
        $cnt = 0;
        $n = count($pos);
        foreach ($pos as $x) {
            if ($x > $x1) {
                $x1 = $x;
            }
            if ($x < $x0) {
                $x0 = $x;
            }
            ++$cnt;
            if ($cnt == 1 || $cnt == $n) {
                if (!($gridlines & EZ_GRIDLINE_TABLE_V)) {
                    continue;
                }
                $this->setLineStyle($outer);
            } else {
                if (!($gridlines & EZ_GRIDLINE_COLUMNS)) {
                    continue;
                }
                $this->setLineStyle($inner);
            }
            $this->line($x - $gap / 2, $y0, $x - $gap / 2, $y2);
        }

// Top and bottom outline
        $this->setLineStyle($outer);
        if ($gridlines & EZ_GRIDLINE_TABLE_H) {
            $this->line($x0 - $gap / 2 - $outer / 2, $y0, $x1 - $gap / 2 + $outer / 2, $y0);
            $this->line($x0 - $gap / 2 - $outer / 2, $y2, $x1 - $gap / 2 + $outer / 2, $y2);
        }

// Header / data separator
        if ($y0 != $y1 && ($gridlines & EZ_GRIDLINE_HEADERONLY)) {
            $this->line($x0 - $gap / 2, $y1 + $rowGap * 2, $x1 - $gap / 2, $y1 + $rowGap * 2);
        }
    }

    /**
     * used to display the headline of a table
     * called by ezTable() method.
     *
     * @param array $cols column array from ezTable option parameter
     * @param $pos
     * @param float $maxWidth maximum width
     * @param float $height   height of the heading
     * @param $descender
     * @param float $gap
     * @param float $size font size
     * @param float $y    actual Y position
     * @param $optionsAll
     */
    protected function ezTableColumnHeadings($cols, $pos, $maxWidth, $height, $descender, $gap, $size, &$y, $optionsAll = [])
    {
        // uses ezText to add the text, and returns the height taken by the largest heading
        // this page will move the headings to a new page if they will not fit completely on this one
        // transaction support will be used to implement this

        if (isset($optionsAll['cols'])) {
            $options = $optionsAll['cols'];
        } else {
            $options = [];
        }

        $mx = 0;
        $startPage = $this->ezPageCount;
        $secondGo = 0;

        // $y is the position at which the top of the table should start, so the base
        // of the first text, is $y-$height-$gap-$descender, but ezText starts by dropping $height

        // the return from this function is the total cell height, including gaps, and $y is adjusted
        // to be the postion of the bottom line

        // begin the transaction
        $this->transaction('start');
        $ok = 0;
        //$y-=$gap+$descender;
        $y -= $gap;
        while ($ok == 0) {
            $col = $optionsAll['textCol'];
            $this->setColor($col[0], $col[1], $col[2], true);

            foreach ($cols as $colName => $colHeading) {
                $this->ezSetY($y);
                if (!empty($optionsAll['alignHeadings'])) {
                    $justification = $optionsAll['alignHeadings'];
                } elseif (isset($options[$colName]) && isset($options[$colName]['justification'])) {
                    $justification = $options[$colName]['justification'];
                } else {
                    $justification = 'left';
                }
                $this->ezText($colHeading, $size, ['aleft' => $pos[$colName], 'aright' => $maxWidth[$colName] + $pos[$colName], 'justification' => $justification]);
                $dy = $y - $this->y;
                if ($dy > $mx) {
                    $mx = $dy;
                }
            }

            $y = $y - $mx - $gap;

            // now, if this has moved to a new page, then abort the transaction, move to a new page, and put it there
            // do not check on the second time around, to avoid an infinite loop
            if ($this->ezPageCount != $startPage && $secondGo == 0) {
                $this->transaction('rewind');
                $this->ezNewPage();
                $y = $this->y - $gap - $descender;
                $ok = 0;
                $secondGo = 1;
                //      $y = $store_y;
                $mx = 0;
            } else {
                $this->transaction('commit');
                $ok = 1;
            }
        }

        return $mx + $gap + $descender;
    }

    /**
     * calculate the maximum width, taking into account until text may be broken.
     *
     * @param $size
     * @param $text
     *
     * @return float text width
     */
    public function ezGetTextWidth($size, $text)
    {
        $mx = 0;
        //$lines = explode("\n",$text);
        $lines = preg_split("[\r\n|\r|\n]", $text);
        foreach ($lines as $line) {
            $w = $this->getTextWidth($size, $line);
            if ($w > $mx) {
                $mx = $w;
            }
        }

        return $mx;
    }

    /**
     *  add a table of information to the pdf document.
     *
     * **$options**
     * <pre>
     * 'showHeadings' => 0 or 1
     * 'shaded'=> 0,1,2,3 default is 1 (1->alternate lines are shaded, 0->no shading, 2-> both shaded, second uses shadeCol2)
     * 'showBgCol'=> 0,1 default is 0 (1->active bg color column setting. if is set to 1, bgcolor attribute ca be used in 'cols' 0->no active bg color columns setting)
     * 'shadeCol' => (r,g,b) array, defining the colour of the shading, default is (0.8,0.8,0.8)
     * 'shadeCol2' => (r,g,b) array, defining the colour of the shading of the other blocks, default is (0.7,0.7,0.7)
     * 'fontSize' => 10
     * 'textCol' => (r,g,b) array, text colour
     * 'titleFontSize' => 12
     * 'rowGap' => 2 , the space added at the top and bottom of each row, between the text and the lines
     * 'colGap' => 5 , the space on the left and right sides of each cell
     * 'lineCol' => (r,g,b) array, defining the colour of the lines, default, black.
     * 'xPos' => 'left','right','center','centre',or coordinate, reference coordinate in the x-direction
     * 'xOrientation' => 'left','right','center','centre', position of the table w.r.t 'xPos'
     * 'width'=> <number> which will specify the width of the table, if it turns out to not be this wide, then it will stretch the table to fit, if it is wider then each cell will be made proportionalty smaller, and the content may have to wrap.
     * 'maxWidth'=> <number> similar to 'width', but will only make table smaller than it wants to be
     * 'cols' => [<colname>=>array('justification'=>'left','width'=>100,'link'=>linkDataName,'bgcolor'=>array(r,g,b] ),<colname>=>....) allow the setting of other paramaters for the individual columns
     * 'minRowSpace'=> the minimum space between the bottom of each row and the bottom margin, in which a new row will be started if it is less, then a new page would be started, default=-100
     * 'innerLineThickness'=>1
     * 'outerLineThickness'=>1
     * 'splitRows'=>0, 0 or 1, whether or not to allow the rows to be split across page boundaries
     * 'protectRows'=>number, the number of rows to hold with the heading on page, ie, if there less than this number of rows on the page, then move the whole lot onto the next page, default=1
     * 'nextPageY'=> true or false (eg. 0 or 1) Sets the Y Postion of the Table of a newPage to current Table Postion
     * </pre>
     *
     * **since 0.12-rc9** added heading shade.
     * <pre>
     * 'shadeHeadingCol'=>(r,g,b) array, defining the colour of the backgound of headings, default is transparent (empty array)
     * </pre>
     *
     * **since 0.12-rc11** applied patch #19 align all header columns at once
     * <pre>
     * 'gridlines'=> EZ_GRIDLINE_* default is EZ_GRIDLINE_DEFAULT, overrides 'showLines' to provide finer control
     * 'alignHeadings' => 'left','right','center'
     * </pre>
     *
     * **deprecated in 0.12-rc11**
     * <pre>'showLines' in $options - use 'gridline' instead</pre>
     *
     * Note that the user will have had to make a font selection already or this will not // produce a valid pdf file.
     *
     * **Example**
     *
     * <pre>
     * $data = array(
     *    ['num'=>1,'name'=>'gandalf','type'=>'wizard']
     *   ,['num'=>2,'name'=>'bilbo','type'=>'hobbit','url'=>'http://www.ros.co.nz/pdf/']
     *   ,['num'=>3,'name'=>'frodo','type'=>'hobbit']
     *   ,['num'=>4,'name'=>'saruman','type'=>'bad dude','url'=>'http://sourceforge.net/projects/pdf-php']
     *   ,['num'=>5,'name'=>'sauron','type'=>'really bad dude']
     *   );
     * $pdf->ezTable($data);
     * </pre>
     *
     * @param array  $data    the data to fill the table cells as a two dimensional array
     * @param array  $cols    (optional) is an associative array, the keys are the names of the columns from $data to be presented (and in that order), the values are the titles to be given to the columns
     * @param string $title   (optional) is the title to be put on the top of the table
     * @param array  $options all possible options, see description above
     *
     * @return float the actual y position
     */
    public function ezTable(&$data, $cols = '', $title = '', $options = '')
    {
        if (!is_array($data)) {
            return;
        }

        if (!is_array($cols)) {
            // take the columns from the first row of the data set
            $first = array_slice($data, 0, 1);
            $first = array_keys(array_shift($first));
            if (!is_array($first)) {
                return;
            }
            $cols = array_combine($first, $first);
        }

        if (!is_array($options)) {
            $options = [];
        }

        $defaults = array(
            /* shading */
            'shaded' => 1, 'shadeCol' => [0.8, 0.8, 0.8], 'shadeCol2' => [0.7, 0.7, 0.7], 'shadeHeadingCol' => [],
            /* font */
            'fontSize' => 10, 'titleFontSize' => 12, 'textCol' => [0, 0, 0],
            /* border */
            'gridlines' => EZ_GRIDLINE_DEFAULT, 'lineCol' => [0, 0, 0], 'innerLineThickness' => 1, 'outerLineThickness' => 1,
            /* position, size and padding */
            'width' => 0, 'maxWidth' => 0, 'titleGap' => 5, 'gap' => 5, 'xPos' => 'centre', 'xOrientation' => 'centre',
            'minRowSpace' => -100, 'rowGap' => 2, 'colGap' => 5, 'splitRows' => 0, 'protectRows' => 1, 'nextPageY' => 0,
            /* other */
            'showHeadings' => 1, 'cols' => [], 'evenColumns' => 0, 'evenColumnsMin' => 20
        );

        foreach ($defaults as $key => $value) {
            if (!isset($options[$key])) {
                $options[$key] = $value;
            } elseif (is_array($value) && !is_array($options[$key])) {
                $options[$key] = $value;
            }
        }

        // @deprecated Compatibility with 'showLines' option
        if (isset($options['showLines'])) {
            switch ($options['showLines']) {
                case 0:
                    $options['gridlines'] = 0;
                    break;
                case 1:
                    $options['gridlines'] = EZ_GRIDLINE_DEFAULT;
                    break;
                case 2:
                    $options['gridlines'] = EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_ROWS;
                    break;
                case 3:
                    $options['gridlines'] = EZ_GRIDLINE_ROWS;
                    break;
                case 4:
                    $options['gridlines'] = EZ_GRIDLINE_HEADERONLY;
                    break;
                default:
                    $options['gridlines'] = EZ_GRIDLINE_TABLE + EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_COLUMNS;
            }
            unset($options['showLines']);
        }

        $options['gap'] = 2 * $options['colGap'];
        // Use Y Position of Current Page position in Table
        if ($options['nextPageY']) {
            $nextPageY = $this->y;
        }

        $middle = ($this->ez['pageWidth'] - $this->ez['rightMargin']) / 2 + ($this->ez['leftMargin']) / 2;

        if (!$this->numFonts) {
            $this->selectFont('Helvetica');
        }

        // figure out the maximum widths of the text within each column
        $maxWidth = [];
        foreach ($cols as $colName => $colTitle) {
            if (empty($colTitle)) {
                $maxWidth[$colName] = 0;
            }
            $w = $this->ezGetTextWidth($options['fontSize'], (string) $colTitle) * 1.01;
            $maxWidth[$colName] = $w;
        }
        // find the maximum cell widths based on the data
        foreach ($data as $row) {
            foreach ($cols as $colName => $colHeading) {
                // BUGFIX #16 ignore empty columns | thanks jafjaf
                if (empty($row[$colName])) {
                    continue;
                }
                $w = $this->ezGetTextWidth($options['fontSize'], (string) $row[$colName]) * 1.01;
                if ($w > $maxWidth[$colName]) {
                    $maxWidth[$colName] = $w;
                }
            }
        }

        $minFontWidth = intval($this->fonts[$this->currentFont]['FontBBox'][2] / 1000 * $options['fontSize']);

        // calculate the start positions of each of the columns
        $pos = [];
        $x = 0;
        $t = $x;
        $adjustmentWidth = 0;
        $setWidth = 0;
        foreach ($maxWidth as $colName => $w) {
            $pos[$colName] = $t;
            // if the column width has been specified then set that here, also total the
            // width avaliable for adjustment
            if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['width']) && $options['cols'][$colName]['width'] > 0) {
                $t = $t + $options['cols'][$colName]['width'];
                $maxWidth[$colName] = $options['cols'][$colName]['width'] - $options['gap'];
                if ($maxWidth[$colName] < $minFontWidth) {
                    $maxWidth[$colName] = $minFontWidth;
                }
                $setWidth += $options['cols'][$colName]['width'];
            } else {
                $t = $t + $w + $options['gap'];
                $adjustmentWidth += $w;
                $setWidth += $options['gap'];
            }
        }
        $pos['_end_'] = $t;

        // we need to cache the first version of the calculated columns
        $cachepos = $pos;

        if ($options['maxWidth'] == 0) {
            $options['maxWidth'] = $this->ez['pageWidth'] - ($this->ez['rightMargin'] + $this->ez['leftMargin']);
        }
        // if maxWidth is specified, and the table is too wide, and the width has not been set,
        // then set the width.
        if ($options['width'] == 0 && $options['maxWidth'] && ($t - $x) > $options['maxWidth']) {
            // then need to make this one smaller
            $options['width'] = $options['maxWidth'];
        }

        if ($options['width'] && $adjustmentWidth > 0 && $setWidth < $options['width']) {
            // first find the current widths of the columns involved in this mystery
            $cols0 = [];
            $cols1 = [];
            $xq = 0;
            $presentWidth = 0;
            $last = '';
            foreach ($pos as $colName => $p) {
                if (!isset($options['cols'][$last]) || !isset($options['cols'][$last]['width']) || $options['cols'][$last]['width'] <= 0) {
                    if (strlen($last)) {
                        $cols0[$last] = $p - $xq - $options['gap'];
                        $presentWidth += ($p - $xq - $options['gap']);
                    }
                } else {
                    $cols1[$last] = $p - $xq;
                }
                $last = $colName;
                $xq = $p;
            }
            // $cols0 contains the widths of all the columns which are not set
            $neededWidth = $options['width'] - $setWidth;
            // if needed width is negative then add it equally to each column, else get more tricky
            if ($presentWidth < $neededWidth) {
                foreach ($cols0 as $colName => $w) {
                    $cols0[$colName] += ($neededWidth - $presentWidth) / count($cols0);
                }
            } else {
                $cnt = 0;
                while ($presentWidth > $neededWidth && $cnt < 100) {
                    ++$cnt; // insurance policy
                    // find the widest columns, and the next to widest width
                    $aWidest = [];
                    $nWidest = 0;
                    $widest = 0;
                    foreach ($cols0 as $colName => $w) {
                        if ($w > $widest) {
                            $aWidest = [$colName];
                            $nWidest = $widest;
                            $widest = $w;
                        } elseif ($w == $widest) {
                            $aWidest[] = $colName;
                        }
                    }
                    // then figure out what the width of the widest columns would have to be to take up all the slack
                    $newWidestWidth = $widest - ($presentWidth - $neededWidth) / count($aWidest);
                    if ($newWidestWidth > $nWidest) {
                        // then there is space to set them to this
                        foreach ($aWidest as $colName) {
                            $cols0[$colName] = $newWidestWidth;
                        }
                        $presentWidth = $neededWidth;
                    } else {
                        // there is not space, reduce the size of the widest ones down to the next size down, and we
                        // will go round again
                        foreach ($aWidest as $colName) {
                            $cols0[$colName] = $nWidest;
                        }
                        $presentWidth = $presentWidth - ($widest - $nWidest) * count($aWidest);
                    }
                }
            }
            // $cols0 now contains the new widths of the constrained columns.
            // now need to update the $pos and $maxWidth arrays
            $xq = 0;
            foreach ($pos as $colName => $p) {
                $pos[$colName] = $xq;
                if (!isset($options['cols'][$colName]) || !isset($options['cols'][$colName]['width']) || $options['cols'][$colName]['width'] <= 0) {
                    if (isset($cols0[$colName])) {
                        $xq += $cols0[$colName] + $options['gap'];
                        $maxWidth[$colName] = $cols0[$colName];
                    }
                } else {
                    if (isset($cols1[$colName])) {
                        $xq += $cols1[$colName];
                    }
                }
            }

            $t = $x + $options['width'];
            $pos['_end_'] = $t;
        }

        // if the option is set to 2 and one of the columns is too narrow we need to look at recalculating the columns
        if ($options['evenColumns'] == 2) {
            $posVals = [];
            foreach ($pos as $w) {
                array_unshift($posVals, $w);
            }
            $narrowestCol = 9999;
            $last = array_pop($posVals);
            while (sizeof($posVals)) {
                $current = array_pop($posVals);
                $currentWidth = $current - $last;
                if ($narrowestCol > $currentWidth) {
                    $narrowestCol = $currentWidth;
                }
                $last = $current;
            }
            if ($narrowestCol < $options['evenColumnsMin']) {
                $options['evenColumns'] = 1;
            }
        }

        // if the option is turned on we need to look at recalculating the columns
        if ($options['evenColumns'] == 1) {
            // what is the maximum width? it is either specified or the page width between the margins
            $redistribution = $options['maxWidth'];

            // what are the manually specified column widths?
            // what is the narrowest auto column? (columns with a specifically defined width are ignored)
            $manualWidth = 0;
            $manualCount = 0;
            $narrowest = 999999999;
            foreach ($options['cols'] as $colName => $col) {
                if (isset($col['width'])) { // was the width of this column specified?
                    ++$manualCount;
                    $manualWidth += $col['width'] * 1;
                } elseif ($narrowest > $maxWidth[$colName]) {
                    $narrowest = $maxWidth[$colName];
                }
            }
            // the total width to be redistributed
            $redistributedWidth = ($redistribution - $manualWidth) / (sizeof($pos) - 1 - $manualCount);
            // recalculate the x positions of the columnn
            $new = 0;
            foreach ($pos as $key => $old) {
                $pos[$key] = $new;
                if (isset($options['cols'][$key]['width'])) {
                    $new += $options['cols'][$key]['width'];
                } else {
                    $new += $redistributedWidth;
                }
            }
            // recalculate the column widths
            $last = -1;
            $newWidth = [];
            foreach ($pos as $key => $val) {
                if ($last >= 0) {
                    $newWidth[$lastKey] = ($val - $last) - $options['gap'];
                }
                $last = $val;
                $lastKey = $key;
            }
            $maxWidth = $newWidth;
            $t = array_sum($maxWidth) + (sizeof($maxWidth) * 2 * $options['colGap']);
        }

        switch ($options['xPos']) {
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
        switch ($options['xOrientation']) {
            case 'left':
                $dx = $xref - $t;
                break;
            case 'right':
                $dx = $xref;
                break;
            case 'centre':
            case 'center':
                $dx = $xref - $t / 2;
                break;
        }
        // applied patch #18 alignment fixes for tables and images | thank you Emil Totev
        $dx += $options['colGap'];

        foreach ($pos as $k => $v) {
            $pos[$k] = $v + $dx;
        }
        $x0 = $x + $dx;
        $x1 = $t + $dx;

        $baseLeftMargin = $this->ez['leftMargin'];
        $basePos = $pos;
        $baseX0 = $x0;
        $baseX1 = $x1;

        $middle = ($x1 + $x0) / 2;

        // start a transaction which will be used to regress the table, if there are not enough rows protected
        if ($options['protectRows'] > 0) {
            $this->transaction('start');
            $movedOnce = 0;
        }
        $abortTable = 1;
        while ($abortTable) {
            $abortTable = 0;
            $dm = $this->ez['leftMargin'] - $baseLeftMargin;
            foreach ($basePos as $k => $v) {
                $pos[$k] = $v + $dm;
            }
            $x0 = $baseX0 + $dm;
            $x1 = $baseX1 + $dm;
            $middle = ($x1 + $x0) / 2;

            // if the title is set, then do that
            if (strlen($title)) {
                $w = $this->getTextWidth($options['titleFontSize'], $title);
                $this->y -= $this->getFontHeight($options['titleFontSize']);
                if ($this->y < $this->ez['bottomMargin']) {
                    $this->ezNewPage();
                    // margins may have changed on the newpage
                    $dm = $this->ez['leftMargin'] - $baseLeftMargin;
                    foreach ($basePos as $k => $v) {
                        $pos[$k] = $v + $dm;
                    }
                    $x0 = $baseX0 + $dm;
                    $x1 = $baseX1 + $dm;
                    $middle = ($x1 + $x0) / 2;
                    $this->y -= $this->getFontHeight($options['titleFontSize']);
                }
                $this->addText($middle - $w / 2, $this->y, $options['titleFontSize'], $title);
                $this->y -= $options['titleGap'];
            }
            // margins may have changed on the newpage
            $dm = $this->ez['leftMargin'] - $baseLeftMargin;
            foreach ($basePos as $k => $v) {
                $pos[$k] = $v + $dm;
            }
            $x0 = $baseX0 + $dm;
            $x1 = $baseX1 + $dm;

            $y = $this->y; // to simplify the code a bit

            // make the table
            $height = $this->getFontHeight($options['fontSize']);
            $descender = $this->getFontDescender($options['fontSize']);

//          $y0 = $y + $descender; // REPLACED THIS LINE WITH THE FOLLOWING
            $y0 = $y - $options['rowGap'];
            $dy = 0;
            if ($options['showHeadings']) {
                // patch #9 start
                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3) {
                    $this->saveState();
                    $textHeadingsObjectId = $this->openObject();
                    $this->closeObject();
                    $this->addObject($textHeadingsObjectId);
                    $this->reopenObject($textHeadingsObjectId);
                }
                // patch #9 end
                // this function will move the start of the table to a new page if it does not fit on this one
                $headingHeight = $this->ezTableColumnHeadings($cols, $pos, $maxWidth, $height, $descender, $options['rowGap'], $options['fontSize'], $y, $options);
                $y0 = $y + $headingHeight + $options['rowGap'];
                $y1 = $y - $options['rowGap'] * 2;

                $dm = $this->ez['leftMargin'] - $baseLeftMargin;
                foreach ($basePos as $k => $v) {
                    $pos[$k] = $v + $dm;
                }
                $x0 = $baseX0 + $dm;
                $x1 = $baseX1 + $dm;
                // patch #9 start
                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3) {
                    $this->closeObject();
                    $this->setColor($options['shadeHeadingCol'][0], $options['shadeHeadingCol'][1], $options['shadeHeadingCol'][2], 1);
                    $this->filledRectangle($x0 - $options['gap'] / 2, $y + $descender, $x1 - $x0, ($y0 - $y - $descender));
                    $this->reopenObject($textHeadingsObjectId);
                    $this->closeObject();
                    $this->restoreState();
                }
                // patch #9 end
            } else {
                $y1 = $y0 + ($options['rowGap'] / 2);
            }
            $firstLine = 1;

            // open an object here so that the text can be put in over the shading
            $this->saveState();

            if (!$this->IsObjectOpened()) {
                $textObjectId = $this->openObject();
                $this->closeObject();
                $this->addObject($textObjectId);
                $this->reopenObject($textObjectId);
            }

            $cnt = 0;
            $newPage = 0;
            foreach ($data as $row) {
                $rowColShading = [];
                foreach ($cols as $colName => $colHeading) {
                    // grab the defined colors for this cell
                    if (isset($row[$colName.'Fill'])) {
                        $fillColor = $row[$colName.'Fill'];
                    } else {
                        $fillColor = '';
                    }

                    $rowX = $pos[$colName] - ($options['gap'] / 2);
                    $rowY = $y + $descender + $height; // BUGGY
                    $rowW = $maxWidth[$colName] + ($options['colGap'] * 2);

                        // decide which color to use!
                        // specified for the cell is first choice
                    if ($fillColor && count($fillColor) && is_array($fillColor)) {
                        $rowColShading[] = ['x' => $rowX, 'y' => $rowY, 'width' => $rowW, 'color' => $fillColor];
                    } elseif (isset($options['cols']) && isset($options['cols'][$colName]) && isset($options['cols'][$colName]['bgcolor']) && is_array($options['cols'][$colName]['bgcolor'])) {
                        $rowColShading[] = ['x' => $rowX, 'y' => $rowY, 'width' => $rowW, 'color' => $options['cols'][$colName]['bgcolor']];
                    } elseif ($options['shaded'] == 1 && $cnt % 2 == 1) {
                        $rowColShading[] = ['x' => $rowX, 'y' => $rowY, 'width' => $rowW, 'color' => $options['shadeCol']];
                    } elseif (($options['shaded'] == 2) && $cnt % 2 == 0) {
                        $rowColShading[] = ['x' => $rowX, 'y' => $rowY, 'width' => $rowW, 'color' => $options['shadeCol']];
                    } elseif (($options['shaded'] == 2) && $cnt % 2 == 1) {
                        $rowColShading[] = ['x' => $rowX, 'y' => $rowY, 'width' => $rowW, 'color' => $options['shadeCol2']];
                    } else {
                        $rowColShading[] = ['color' => []];
                    }
                }

                ++$cnt;
                // the transaction support will be used to prevent rows being split
                if ($options['splitRows'] == 0) {
                    $pageStart = $this->ezPageCount;
                    if (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1) {
                        $columnStart = $this->ez['columns']['colNum'];
                    }
                    $this->transaction('start');
                    $row_orig = $row;
                    $y_orig = $y;
                    $y0_orig = $y0;
                    $y1_orig = $y1;
                }
                $ok = 0;
                $secondTurn = 0;
                while (!$abortTable && $ok == 0) {
                    $mx = 0;
                    $newRow = 1;
                    while (!$abortTable && ($newPage || $newRow)) {
                        $y -= $height;
                        if ($newPage || $y < $this->ez['bottomMargin'] || (isset($options['minRowSpace']) && $y < ($this->ez['bottomMargin'] + $options['minRowSpace']))) {
                            // check that enough rows are with the heading
                            if ($options['protectRows'] > 0 && $movedOnce == 0 && $cnt <= $options['protectRows']) {
                                // then we need to move the whole table onto the next page
                                $movedOnce = 1;
                                $abortTable = 1;
                            }

                            $y2 = $y - $mx + 2 * $height + $descender - $newRow * $height;
                            if ($options['gridlines']) {
                                $y1 += $descender;
                                if (!$options['showHeadings']) {
                                    $y1 += ($options['rowGap'] / 2); // added line
                                    $y0 = $y1;
                                }
                                $this->ezTableDrawLines($pos, $options['gap'], $options['rowGap'], $x0, $x1, $y0, $y1, $y2, $options['lineCol'], $options['innerLineThickness'], $options['outerLineThickness'], $options['gridlines']);
                            }
                            $this->closeObject();
                            $this->restoreState();
                            $this->ezNewPage();

                            // and the margins may have changed, this is due to the possibility of the columns being turned on
                            // as the columns are managed by manipulating the margins
                            $dm = $this->ez['leftMargin'] - $baseLeftMargin;
                            foreach ($basePos as $k => $v) {
                                $pos[$k] = $v + $dm;
                            }

                            $x0 = $baseX0 + $dm; // even
                            $x1 = $baseX1 + $dm; // even

                            $this->saveState();
                            $textObjectId = $this->openObject();
                            $this->closeObject();
                            $this->addObject($textObjectId);
                            $this->reopenObject($textObjectId);

                            $this->setColor($options['textCol'][0], $options['textCol'][1], $options['textCol'][2], 1);
                            $y = ($options['nextPageY']) ? $nextPageY : ($this->ez['pageHeight'] - $this->ez['topMargin']);
//                          $y0 = $y + $descender; // REPLACED THIS LINE WITH THE FOLLOWING
                            $y0 = $y - $options['rowGap'];
                            $mx = 0;
                            if ($options['showHeadings']) {
                                // patch #9 start
                                if (isset($options['shadeHeadingCol']) && count($options['shadeHeadingCol']) == 3) {
                                    $this->saveState();
                                    $textHeadingsObjectId = $this->openObject();
                                    $this->closeObject();
                                    $this->addObject($textHeadingsObjectId);
                                    $this->reopenObject($textHeadingsObjectId);
                                    $this->closeObject();
                                    $this->setColor($options['shadeHeadingCol'][0], $options['shadeHeadingCol'][1], $options['shadeHeadingCol'][2], 1);
                                    $this->filledRectangle($x0 - $options['gap'] / 2, $y0, $x1 - $x0, -($headingHeight - $descender + $options['rowGap']));
                                    $this->reopenObject($textHeadingsObjectId);
                                    $this->closeObject();
                                    $this->restoreState();
                                }
                                // patch #9 end
                                $this->ezTableColumnHeadings($cols, $pos, $maxWidth, $height, $descender, $options['rowGap'], $options['fontSize'], $y, $options);
                                $y1 = $y - $options['rowGap'] * 2;
                            } else {
                                $y1 = $y0;
                            }
                            $firstLine = 1;
                            $y -= $height;
                        }
                        $newRow = 0;
                        // write the actual data
                        // if these cells need to be split over a page, then $newPage will be set, and the remaining
                        // text will be placed in $leftOvers
                        $newPage = 0;
                        $leftOvers = [];

                        foreach ($cols as $colName => $colTitle) {
                            $this->ezSetY($y + $height);
                            $colNewPage = 0;
                            if (isset($row[$colName])) {
                                if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['link']) && strlen($options['cols'][$colName]['link'])) {
                                    //$lines = explode("\n",$row[$colName]);
                                    $lines = preg_split("[\r\n|\r|\n]", $row[$colName]);
                                    if (isset($row[$options['cols'][$colName]['link']]) && strlen($row[$options['cols'][$colName]['link']])) {
                                        foreach ($lines as $k => $v) {
                                            $lines[$k] = '<c:alink:'.$row[$options['cols'][$colName]['link']].'>'.$v.'</c:alink>';
                                        }
                                    }
                                } else {
                                    //$lines = explode("\n",$row[$colName]);
                                    $lines = preg_split("[\r\n|\r|\n]", $row[$colName]);
                                }
                            } else {
                                $lines = [];
                            }
                            $this->y -= $options['rowGap'];
                            foreach ($lines as $line) {
                                $line = $this->ezProcessText($line);
                                // set the text color
                                // grab the defined colors for this cell
                                if (isset($row[$colName.'Color'])) {
                                    $textColor = $row[$colName.'Color'];
                                    $this->setColor($textColor[0], $textColor[1], $textColor[2], true);
                                    //$line = '<c:color:'.$textColor[0].','.$textColor[1].','.$textColor[2].'>'.$line . '</c:color>';
                                } else {
                                    $this->setColor(0, 0, 0, true);
                                    $this->setStrokeColor(0, 0, 0, true);
                                }

                                $start = 1;
                                while (strlen($line) || $start) {
                                    $start = 0;
                                    if (!$colNewPage) {
                                        $this->y = $this->y - $height;
                                    }
                                    if ($this->y < $this->ez['bottomMargin']) {
                                        // $this->ezNewPage();
                                        $newPage = 1; // whether a new page is required for any of the columns
                                        $colNewPage = 1; // whether a new page is required for this column
                                    }
                                    if ($colNewPage) {
                                        if (isset($leftOvers[$colName])) {
                                            $leftOvers[$colName] .= "\n".$line;
                                        } else {
                                            $leftOvers[$colName] = $line;
                                        }
                                        $line = '';
                                    } else {
                                        if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['justification'])) {
                                            $just = $options['cols'][$colName]['justification'];
                                        } else {
                                            $just = 'left';
                                        }

                                        // grab the defined colors for this cell
                                        if (isset($row[$colName."Color"])) {
                                            $textColor = $row[$colName."Color"];
                                        } else {
                                            $textColor = "";
                                        }

                                        // apply the color to the text
                                        if (is_array($textColor)) {
                                            $this->setColor($textColor[0], $textColor[1], $textColor[2]);
                                            $line = $this->addText($pos[$colName], $this->y, $options['fontSize'], $line, $maxWidth[$colName], $just);
                                        } else {
                                            $this->setColor($options['textCol'][0], $options['textCol'][1], $options['textCol'][2]);
                                            $line = $this->addText($pos[$colName], $this->y, $options['fontSize'], $line, $maxWidth[$colName], $just);
                                        }
                                    }
                                }
                            }

                            $dy = $y + $height - $this->y + $options['rowGap'];
                            if ($dy - $height * $newPage > $mx) {
                                $mx = $dy - $height * $newPage;
                            }
                        }

                        // apply the colours to the cells in the row
                        foreach ($rowColShading as $shadingDetails) {
                            if (sizeof($shadingDetails['color'])) {
                                $this->closeObject();
                                $this->setColor($shadingDetails['color'][0], $shadingDetails['color'][1], $shadingDetails['color'][2], 1);
                                $this->filledRectangle($shadingDetails['x'], $y + $descender + $height - $mx, $shadingDetails['width'], $mx);
                                $this->reopenObject($textObjectId);
                            }
                        }

                        // set $row to $leftOvers so that they will be processed onto the new page (we need to add the colours to the leftovers)
                        foreach ($cols as $colName => $colHeading) {
                            if (isset($row[$colName.'Fill'])) {
                                $leftOvers[$colName.'Fill'] = $row[$colName.'Fill'];
                            }
                            if (isset($row[$colName.'Color'])) {
                                $leftOvers[$colName.'Color'] = $row[$colName.'Color'];
                            }
                        }
                        $row = $leftOvers;

                        if ($options['gridlines'] & EZ_GRIDLINE_ROWS) {
                            // then draw a line on the top of each block
                            // $this->closeObject();
                            $this->saveState();
                            $this->setStrokeColor($options['lineCol'][0], $options['lineCol'][1], $options['lineCol'][2], 1);
                            // $this->line($x0-$options['gap']/2,$y+$descender+$height-$mx,$x1-$x0,$mx);
                            if ($firstLine) {
                                $firstLine = 0;
                            } else {
                                $this->setLineStyle($options['innerLineThickness']);
                                $this->line($x0 - $options['gap'] / 2, $y + $descender + $height, $x1 - $options['gap'] / 2, $y + $descender + $height);
                            }

                            $this->restoreState();
                            // $this->reopenObject($textObjectId);
                        }
                    } // end of while
                    $y = $y - $mx + $height;

                    // checking row split over pages
                    if ($options['splitRows'] == 0) {
                        if ((($this->ezPageCount != $pageStart) || (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1 && $columnStart != $this->ez['columns']['colNum'])) && $secondTurn == 0) {
                            // then we need to go back and try that again !
                            $newPage = 1;
                            $secondTurn = 1;
                            $this->transaction('rewind');
                            $row = $row_orig;
                            $y = $y_orig;
                            $y0 = $y0_orig;
                            $y1 = $y1_orig;
                            $ok = 0;

                            $dm = $this->ez['leftMargin'] - $baseLeftMargin;
                            foreach ($basePos as $k => $v) {
                                $pos[$k] = $v + $dm;
                            }
                            $x0 = $baseX0 + $dm;
                            $x1 = $baseX1 + $dm;
                        } else {
                            $this->transaction('commit');
                            $ok = 1;
                        }
                    } else {
                        $ok = 1; // don't go round the loop if splitting rows is allowed
                    }
                } // end of while to check for row splitting
                if ($abortTable) {
                    if ($ok == 0) {
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

        $y2 = $y + $descender;
        if ($options['gridlines']) {
            $y1 += $descender;
            if (!$options['showHeadings']) {
                $y1 += ($options['rowGap'] / 2); // added line
                $y0 = $y1;
            }
            $this->ezTableDrawLines($pos, $options['gap'], $options['rowGap'], $x0, $x1, $y0, $y1, $y2, $options['lineCol'], $options['innerLineThickness'], $options['outerLineThickness'], $options['gridlines']);
        }
        // close the object for drawing the text on top
        $this->closeObject();
        $this->restoreState();

        $this->y = $y;

        return $y;
    }

    /**
     * internal method to convert some text directives (like custom callbacks).
     *
     * @used-by ezTable()
     * @used-by ezText()
     *
     * @param string $text text to be parsed
     *
     * @return string customized text
     */
    protected function ezProcessText($text)
    {
        // this function will intially be used to implement underlining support, but could be used for a range of other
        // purposes
        $search = ['<u>', '<U>', '</u>', '</U>'];
        $replace = ['<c:uline>', '<c:uline>', '</c:uline>', '</c:uline>'];

        return str_replace($search, $replace, $text);
    }

    /**
     * this will add a string of text to the document, starting at the current drawing
     * position.
     * it will wrap to keep within the margins, including optional offsets from the left
     * and the right, if $size is not specified, then it will be the last one used, or
     * the default value (12 I think).
     * the text will go to the start of the next line when a return code "\n" is found.
     * possible options are:.
     *
     * 'left'=> number, gap to leave from the left margin<br>
     * 'right'=> number, gap to leave from the right margin<br>
     * 'aleft'=> number, absolute left position (overrides 'left')<br>
     * 'aright'=> number, absolute right position (overrides 'right')<br>
     * 'justification' => 'left','right','center','centre','full'<br>
     *
     * only set one of the next two items (leading overrides spacing)<br>
     * 'leading' => number, defines the total height taken by the line, independent of the font height.<br>
     * 'spacing' => a real number, though usually set to one of 1, 1.5, 2 (line spacing as used in word processing)<br>
     *
     * if $test is set then this should just check if the text is going to flow onto a new page or not, returning true or false
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezText('This is a text string\nplus next line', 12, ['justification'=> 'center']);
     * </pre>
     *
     * @param string $text    text string
     * @param float  $size    font size
     * @param array  $options options from above
     * @param bool   $test    is this test output only (to check if it fit to the page for instance)
     *
     * @return float|bool Y position or true/false if $test parameter is set
     */
    public function ezText($text, $size = 0, $options = [], $test = 0)
    {
        // apply the filtering which will make the underlining function.
        $text = $this->ezProcessText($text);

        $newPage = false;
        $store_y = $this->y;

        if (is_array($options) && isset($options['aleft'])) {
            $left = $options['aleft'];
        } else {
            $left = $this->ez['leftMargin'] + ((is_array($options) && isset($options['left'])) ? $options['left'] : 0);
        }
        if (is_array($options) && isset($options['aright'])) {
            $right = $options['aright'];
        } else {
            $right = $this->ez['pageWidth'] - $this->ez['rightMargin'] - ((is_array($options) && isset($options['right'])) ? $options['right'] : 0);
        }
        if ($size <= 0) {
            $size = $this->ez['fontSize'];
        } else {
            $this->ez['fontSize'] = $size;
        }

        if (is_array($options) && isset($options['justification'])) {
            $just = $options['justification'];
        } else {
            $just = 'left';
        }

        // modifications to give leading and spacing based on those given by Craig Heydenburg 1/1/02
        if (is_array($options) && isset($options['leading'])) { //# use leading instead of spacing
            $height = $options['leading'];
        } elseif (is_array($options) && isset($options['spacing'])) {
            $height = $this->getFontHeight($size) * $options['spacing'];
        } else {
            $height = $this->getFontHeight($size);
        }

        $lines = preg_split("[\r\n|\r|\n]", $text);
        $c = count($lines);
        for ($i = 0; $i < $c; $i++) {
            $line = $lines[$i];
            $start = 1;
            while (strlen($line) || $start) {
                $start = 0;
                $this->y = $this->y - $height;
                if ($this->y < $this->ez['bottomMargin']) {
                    if ($test) {
                        $newPage = true;
                    } else {
                        $this->ezNewPage();
                        // and then re-calc the left and right, in case they have changed due to columns
                        $this->y = $this->y - $height;
                    }
                }
                if (is_array($options) && isset($options['aleft'])) {
                    $left = $options['aleft'];
                } else {
                    $left = $this->ez['leftMargin'] + ((is_array($options) && isset($options['left'])) ? $options['left'] : 0);
                }
                if (is_array($options) && isset($options['aright'])) {
                    $right = $options['aright'];
                } else {
                    $right = $this->ez['pageWidth'] - $this->ez['rightMargin'] - ((is_array($options) && isset($options['right'])) ? $options['right'] : 0);
                }
                
                if ($just == 'full' && (empty($lines[$i + 1]) || $c == $i + 1)) {
                    // do not fully justify if its the absolute last line (taking line breaks into account)
                    $tmp = $this->addText($left, $this->y, $size, $line, $right - $left, $just, 0, 0, 1);
                    if (!strlen($tmp)) {
                        $just = "left";
                    }
                }

                $line = $this->addText($left, $this->y, $size, $line, $right - $left, $just, 0, 0, $test);

                if (is_array($options) && isset($options['justification'])) {
                    // recover justification
                    $just = $options['justification'];
                }
            }
        }

        if ($test) {
            $this->y = $store_y;

            return $newPage;
        } else {
            return $this->y;
        }
    }

    /**
     * Used to display images
     * supported images are:
     *  - JPEG
     *  - PNG (transparent)
     *  - GIF (but internally converted into JPEG).
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezImage('file.jpg', 5, 100, 'full', 'right', ['color'=> array(0.2, 0.4, 0.4], 'width'=> 2, 'cap'=>'round'));
     * </pre>
     *
     * @param string $image image file or url path
     * @param float  $pad   image padding
     * @param float  $width max width
     * @param $resize
     * @param string $just   justification of the image ('left', 'right', 'center')
     * @param array  $border border array - see example
     */
    public function ezImage($image, $pad = 5, $width = 0, $resize = 'full', $just = 'center', $angle = 0, $border = '')
    {
        $offset=0;
        $temp = false;
        //beta ezimage function
        if (stristr($image, '://')) { //copy to temp file
            $cont = file_get_contents($image);

            $image = tempnam($this->tempPath, 'ezImg');
            $fp2 = @fopen($image, 'w');
            fwrite($fp2, $cont);
            fclose($fp2);
            $temp = true;
        }

        if (!(file_exists($image))) {
            $this->debug("ezImage: Could not find image '$image'", E_USER_WARNING);

            return false; //return immediately if image file does not exist
        }

        $imageInfo = getimagesize($image);

        if ($imageInfo === false) {
            $this->debug("ezImage: Could not get image info for '$image'", E_USER_ERROR);
        }

        if ($width == 0) {
            $width = $imageInfo[0];
        } //set width
        $ratio = $imageInfo[0] / $imageInfo[1];

        //get maximum width of image
        if (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1) {
            $bigwidth = $this->ez['columns']['width'] - ($pad * 2);
        } else {
            $bigwidth = $this->ez['pageWidth'] - ($pad * 2);
        }
        //fix width if larger than maximum or if $resize=full
        if ($resize == 'full' || ($resize == 'width' && $width > $bigwidth)) {
            $width = $bigwidth - $this->ez['leftMargin'] - $this->ez['rightMargin'];
        }

        $height = ($width / $ratio); //set height

        //fix size if runs off page
        if ($height > ($this->y - $this->ez['bottomMargin'] - ($pad * 2))) {
            if ($resize != 'full') {
                $this->ezNewPage();
            } else {
                $height = ($this->y - $this->ez['bottomMargin'] - ($pad * 2)); //shrink height
                $width = ($height * $ratio); //fix width
            }
        }
        //fix x-offset if image smaller than bigwidth
        if ($width < $bigwidth) {
            //center if justification=center
            if ($just == 'center') {
                $offset = (($bigwidth - $width) / 2) - $this->ez['leftMargin'];
            }
            //move to right if justification=right
            if ($just == 'right') {
                $offset = ($bigwidth - $width) - $this->ez['leftMargin'] - $this->ez['rightMargin'];
            }
            //leave at left if justification=left
            if ($just == 'left') {
                $offset = 0;
            }
        }

        //call appropriate function
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $this->addJpegFromFile($image, $this->ez['leftMargin'] + $pad + $offset, $this->y - $pad - $height, $width, 0, $angle);
                break;
            case IMAGETYPE_PNG:
                $this->addPngFromFile($image, $this->ez['leftMargin'] + $pad + $offset, $this->y - $pad - $height, $width, 0, $angle);
                break;
            case IMAGETYPE_GIF:
                // use GD to convert the GIF image to PNG and allow transparency
                $this->addGifFromFile($image, $this->ez['leftMargin'] + $pad + $offset, $this->y - $pad - $height, $width, 0, $angle);
                break;
            default:
                $this->debug('ezImage: Unsupported image type'.$imageInfo[2], E_USER_WARNING);

                return false; //return if file is not jpg or png
        }

        //draw border
        if ($border != '') {
            if (!(isset($border['color']))) {
                $border['color']['red'] = .5;
                $border['color']['blue'] = .5;
                $border['color']['green'] = .5;
            }
            if (!(isset($border['width']))) {
                $border['width'] = 1;
            }
            if (!(isset($border['cap']))) {
                $border['cap'] = 'round';
            }
            if (!(isset($border['join']))) {
                $border['join'] = 'round';
            }

            $this->setStrokeColor($border['color']['red'], $border['color']['green'], $border['color']['blue']);
            $this->setLineStyle($border['width'], $border['cap'], $border['join']);
            $this->rectangle($this->ez['leftMargin'] + $pad + $offset, $this->y + $this->getFontHeight($this->ez['fontSize']) - $pad - $height, $width, $height);
        }
        // move y below image
        $this->y = $this->y - $pad - $height;
        //remove tempfile for remote images
        if ($temp == true) {
            unlink($image);
        }
    }

    /**
     * Output the PDF content as stream.
     *
     * $options
     *
     * 'compress' => 0/1 to enable compression. For compression level please use $this->options['compression'] = <level> at the very first point. Default: 1<br>
     * 'download' => 0/1 to display inline (in browser) or as download. Default: 0<br>
     *
     * @param array $options options array from above
     */
    public function ezStream($options = '')
    {
        $this->cleanUp();
        $this->stream($options);
    }

    /**
     * return the pdf output as string.
     *
     * @param bool $debug uncompressed output for debugging purposes
     *
     * @return string pdf document
     */
    public function ezOutput($debug = false)
    {
        $this->cleanUp();

        return $this->output($debug);
    }

    /**
     * note that templating code is still considered developmental - have not really figured
     * out a good way of doing this yet.
     *
     * this function will load the requested template ($file includes full or relative pathname)
     * the code for the template will be modified to make it name safe, and then stored in
     * an array for later use
     *
     * The id of the template will be returned for the user to operate on it later
     *
     * SECURITY NOTICE: php function 'eval' is used in execTemplate
     *
     * @param string $templateFile php script to be execupte
     *
     * @return int object number?!
     *
     * @deprecated method deprecated in 0.12.0
     */
    public function loadTemplate($templateFile)
    {
        if (!file_exists($templateFile)) {
            return -1;
        }

        $code = implode('', file($templateFile));
        if (!strlen($code)) {
            return;
        }

        $code = trim($code);
        if (substr($code, 0, 5) == '<?php') {
            $code = substr($code, 5);
        }
        if (substr($code, -2) == '?>') {
            $code = substr($code, 0, strlen($code) - 2);
        }
        if (isset($this->ez['numTemplates'])) {
            $newNum = $this->ez['numTemplates'];
            ++$this->ez['numTemplates'];
        } else {
            $newNum = 0;
            $this->ez['numTemplates'] = 1;
            $this->ez['templates'] = [];
        }

        $this->ez['templates'][$newNum]['code'] = $code;

        return $newNum;
    }

    /**
     * executes the template.
     *
     * @param $id
     * @param $data
     * @param $options
     *
     * @deprecated method deprecated in 0.12.0
     */
    public function execTemplate($id, $data = [], $options = [])
    {
        // execute the given template on the current document.
        if (!isset($this->ez['templates'][$id])) {
            return;
        }
        eval($this->ez['templates'][$id]['code']);
    }

    /**
     * callback function for internal links.
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezText('<c:ilink:destName>Internal Link</c:ilink>');
     * </pre>
     *
     * @param $info
     */
    public function ilink($info)
    {
        $this->alink($info, 1);
    }

    /**
     * callback function for external links.
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezText('&lt;c:alink:www.google.de&gt;Hello google&lt;/c:alink&gt;');<br>
     * </pre>
     *
     * @param array $info callback info array
     * @param $internal
     */
    public function alink($info, $internal = 0)
    {
        // a callback function to support the formation of clickable links within the document
        $lineFactor = 0.05; // the thickness of the line as a proportion of the height. also the drop of the line.
        switch ($info['status']) {
            case 'start':
            case 'sol':
                // the beginning of the link
                // this should contain the URl for the link as the 'p' entry, and will also contain the value of 'nCallback'
                if (!isset($this->ez['links'])) {
                    $this->ez['links'] = [];
                }
                $this->ez['links'][] = ['x' => $info['x'], 'y' => $info['y'], 'angle' => $info['angle'], 'descender' => $info['descender'], 'height' => $info['height'], 'url' => $info['p']];
                if ($internal == 0) {
                    $this->saveState();
                    $this->setColor(0, 0, 1);
                    $this->setStrokeColor(0, 0, 1);
                    $thick = $info['height'] * $lineFactor;
                    $this->setLineStyle($thick);
                }
                break;
            case 'end':
            case 'eol':
                // the end of the link
                // assume that it is the most recent opening which has closed
                $start = array_shift($this->ez['links']);
                // add underlining
                if ($internal) {
                    $this->addInternalLink($start['url'], $start['x'], $start['y'] + $start['descender'], $info['x'], $start['y'] + $start['descender'] + $start['height']);
                } else {
                    $a = deg2rad((float) $start['angle'] - 90.0);
                    $drop = $start['height'] * $lineFactor * 1.5;
                    $dropx = cos($a) * $drop;
                    $dropy = -sin($a) * $drop;
                    $this->line($start['x'] - $dropx, $start['y'] - $dropy, $info['x'] - $dropx, $info['y'] - $dropy);
                    $this->addLink($start['url'], $start['x'], $start['y'] + $start['descender'], $info['x'], $start['y'] + $start['descender'] + $start['height']);
                    $this->restoreState();
                }
                break;
        }
    }

    /**
     * a callback function to support underlining.
     *
     * @param array $info callback info array
     */
    public function uline($info)
    {
        $lineFactor = 0.05; // the thickness of the line as a proportion of the height. also the drop of the line.
        switch ($info['status']) {
            case 'start':
            case 'sol':
                // the beginning of the underline zone
                if (!isset($this->ez['links'])) {
                    $this->ez['links'] = [];
                }

                $this->ez['links'][] = ['x' => $info['x'], 'y' => $info['y'], 'angle' => $info['angle'], 'descender' => $info['descender'], 'height' => $info['height']];
                $thick = $info['height'] * $lineFactor;
                $this->setLineStyle($thick);
                $this->saveState();
                break;
            case 'end':
            case 'eol':
                // the end of the link
                // assume that it is the most recent opening which has closed
                $start = array_shift($this->ez['links']);
                // add underlining
                $a = deg2rad((float) $start['angle'] - 90.0);
                $drop = $start['height'] * $lineFactor * 1.5;
                $dropx = cos($a) * $drop;
                $dropy = -sin($a) * $drop;
                $this->line($start['x'] - $dropx, $start['y'] - $dropy, $info['x'] - $dropx, $info['y'] - $dropy);
                $this->restoreState();
                break;
        }
    }

    /**
     * a callback function to support comment annotation.
     *
     * @param $info callback info array
     */
    public function comment(&$info)
    {
        if (isset($info)) {
            $offsetY = $info['y'];
            // split title and text content use '|' char
            $commentPart = preg_split("/\|/", $info['p']);
            if (is_array($commentPart) && count($commentPart) > 1) {
                $this->addComment($commentPart[0], $commentPart[1], $info['x'], $offsetY);
            } else {
                $this->addComment('Comment', $info['p'], $info['x'], $offsetY);
            }
            $info['x'] += 15;
        }
    }

    /**
     * another callback function to provide coloured text
     * Usage: $pdf->ezText("<c:color:r,g,b>some coloured text</c:color>");.
     *
     * Please make sure $pdf->allowedTags is set properly
     *
     * @param $info callback info array
     */
    public function color($info)
    {
        // a callback function to support the inline coloring of text
        switch ($info['status']) {
            case 'start':
            case 'sol':
                $this->saveState();
                $colAry = explode(',', $info['p']);
                $this->setColor($colAry[0], $colAry[1], $colAry[2]);
                break;
            case 'end':
            case 'eol':
                // the end of the link
                // assume that it is the most recent opening which has closed
                //$this->setColor(0, 0, 0);
                $this->restoreState();
                break;
        }
    }
}
