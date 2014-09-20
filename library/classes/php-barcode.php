<?php
/*
 * BarCode Coder Library (BCC Library)
 * BCCL Version 2.0
 *
 * Porting : PHP
 * Version : 2.0.3
 *
 * Date    : 2013-01-06
 * Author  : DEMONTE Jean-Baptiste <jbdemonte@gmail.com>
 *           HOUREZ Jonathan
 *
 * Web site: http://barcode-coder.com/
 * dual licence :  http://www.cecill.info/licences/Licence_CeCILL_V2-fr.html
 *                 http://www.gnu.org/licenses/gpl.html
 * BarCode Coder Library
 * @package BCC Library
 * @author DEMONTE Jean-Baptiste <jbdemonte@gmail.com>
 * @author HOUREZ Jonathan
 * @copyright 2013 
 *
 * Added to Openemr by Terry Hill terry@lillysystems.com
 * this is from the barcode-coder website
 *
 */
 

class Barcode {

    static public function gd($res, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null){
        return self::_draw(__FUNCTION__, $res, $color, $x, $y, $angle, $type, $datas, $width, $height);
    }

    static public function fpdf($res, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null){
        return self::_draw(__FUNCTION__, $res, $color, $x, $y, $angle, $type, $datas, $width, $height);
    }

    static private function _draw($call, $res, $color, $x, $y, $angle, $type, $datas, $width, $height){
        $digit = '';
        $hri   = '';
        $code  = '';
        $crc   = true;
        $rect  = false;
        $b2d   = false;

        if (is_array($datas)){
            foreach(array('code' => '', 'crc' => true, 'rect' => false) as $v => $def){
                $$v = isset($datas[$v]) ? $datas[$v] : $def;
            }
            $code = $code;
        } else {
            $code = $datas;
        }
        if ($code == '') return false;
        $code = (string) $code;

        $type = strtolower($type);

        switch($type){
            case 'std25':
            case 'int25':
                $digit = BarcodeI25::getDigit($code, $crc, $type);
                $hri = BarcodeI25::compute($code, $crc, $type);
                break;
            case 'ean8':
            case 'ean13':
                $digit = BarcodeEAN::getDigit($code, $type);
                $hri = BarcodeEAN::compute($code, $type);
                break;
            case 'upc':
                $digit = BarcodeUPC::getDigit($code);
                $hri = BarcodeUPC::compute($code);
                break;
            case 'code11':
                $digit = Barcode11::getDigit($code);
                $hri = $code;
                break;
            case 'code39':
                $digit = Barcode39::getDigit($code);
                $hri = $code;
                break;
            case 'code93':
                $digit = Barcode93::getDigit($code, $crc);
                $hri = $code;
                break;
            case 'code128':
                $digit = Barcode128::getDigit($code);
                $hri = $code;
                break;
            case 'codabar':
                $digit = BarcodeCodabar::getDigit($code);
                $hri = $code;
                break;
            case 'msi':
                $digit = BarcodeMSI::getDigit($code, $crc);
                $hri = BarcodeMSI::compute($code, $crc);
                break;
            case 'datamatrix':
                $digit = BarcodeDatamatrix::getDigit($code, $rect);
                $hri = $code;
                $b2d = true;
                break;
        }

        if ($digit == '') return false;

        if ( $b2d ){
            $width = is_null($width) ? 5 : $width;
            $height = $width;
        } else {
            $width = is_null($width) ? 1 : $width;
            $height = is_null($height) ? 50 : $height;
            $digit = self::bitStringTo2DArray($digit);
        }

        if ( $call == 'gd' ){
            $result = self::digitToGDRenderer($res, $color, $x, $y, $angle, $width, $height, $digit);
        } else if ( $call == 'fpdf' ){
            $result = self::digitToFPDFRenderer($res, $color, $x, $y, $angle, $width, $height, $digit);
        }

        $result['hri'] = $hri;
        return $result;
    }

    // convert a bit string to an array of array of bit char
    private static function bitStringTo2DArray( $digit ){
        $d = array();
        $len = strlen($digit);
        for($i=0; $i<$len; $i++) $d[$i] = $digit[$i];
        return(array($d));
    }

    private static function digitToRenderer($fn, $xi, $yi, $angle, $mw, $mh, $digit){
        $lines = count($digit);
        $columns = count($digit[0]);
        $angle = deg2rad(-$angle);
        $cos = cos($angle);
        $sin = sin($angle);

        self::_rotate($columns * $mw / 2, $lines * $mh / 2, $cos, $sin , $x, $y);
        $xi -=$x;
        $yi -=$y;
        for($y=0; $y<$lines; $y++){
            $x = -1;
            while($x <$columns) {
                $x++;
                if ($digit[$y][$x] == '1') {
                    $z = $x;
                    while(($z + 1 <$columns) && ($digit[$y][$z + 1] == '1')) {
                        $z++;
                    }
                    $x1 = $x * $mw;
                    $y1 = $y * $mh;
                    $x2 = ($z + 1) * $mw;
                    $y2 = ($y + 1) * $mh;
                    self::_rotate($x1, $y1, $cos, $sin, $xA, $yA);
                    self::_rotate($x2, $y1, $cos, $sin, $xB, $yB);
                    self::_rotate($x2, $y2, $cos, $sin, $xC, $yC);
                    self::_rotate($x1, $y2, $cos, $sin, $xD, $yD);
                    $fn(array(
                        $xA + $xi, $yA + $yi,
                        $xB + $xi, $yB + $yi,
                        $xC + $xi, $yC + $yi,
                        $xD + $xi, $yD + $yi
                    ));
                    $x = $z + 1;
                }
            }
        }
        return self::result($xi, $yi, $columns, $lines, $mw, $mh, $cos, $sin);
    }

    // GD barcode renderer
    private static function digitToGDRenderer($gd, $color, $xi, $yi, $angle, $mw, $mh, $digit){
        $fn = function($points) use ($gd, $color) {
            imagefilledpolygon($gd, $points, 4, $color);
        };
        return self::digitToRenderer($fn, $xi, $yi, $angle, $mw, $mh, $digit);
    }
    // FPDF barcode renderer
    private static function digitToFPDFRenderer($pdf, $color, $xi, $yi, $angle, $mw, $mh, $digit){
        if (!is_array($color)){
            if (preg_match('`([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})`i', $color, $m)){
                $color = array(hexdec($m[1]),hexdec($m[2]),hexdec($m[3]));
            } else {
                $color = array(0,0,0);
            }
        }
        $color = array_values($color);
        $pdf->SetDrawColor($color[0],$color[1],$color[2]);
        $pdf->SetFillColor($color[0],$color[1],$color[2]);

        $fn = function($points) use ($pdf) {
            $op = 'f';
            $h = $pdf->h;
            $k = $pdf->k;
            $points_string = '';
            for($i=0; $i < 8; $i+=2){
                $points_string .= sprintf('%.2F %.2F', $points[$i]*$k, ($h-$points[$i+1])*$k);
                $points_string .= $i ? ' l ' : ' m ';
            }
            $pdf->_out($points_string . $op);
        };
        return self::digitToRenderer($fn, $xi, $yi, $angle, $mw, $mh, $digit);
    }

    static private function result($xi, $yi, $columns, $lines, $mw, $mh, $cos, $sin){
        self::_rotate(0, 0, $cos, $sin , $x1, $y1);
        self::_rotate($columns * $mw, 0, $cos, $sin , $x2, $y2);
        self::_rotate($columns * $mw, $lines * $mh, $cos, $sin , $x3, $y3);
        self::_rotate(0, $lines * $mh, $cos, $sin , $x4, $y4);

        return array(
            'width' => $columns * $mw,
            'height'=> $lines * $mh,
            'p1' => array(
                'x' => $xi + $x1,
                'y' => $yi + $y1
            ),
            'p2' => array(
                'x' => $xi + $x2,
                'y' => $yi + $y2
            ),
            'p3' => array(
                'x' => $xi + $x3,
                'y' => $yi + $y3
            ),
            'p4' => array(
                'x' => $xi + $x4,
                'y' => $yi + $y4
            )
        );
    }

    static private function _rotate($x1, $y1, $cos, $sin , &$x, &$y){
        $x = $x1 * $cos - $y1 * $sin;
        $y = $x1 * $sin + $y1 * $cos;
    }

    static public function rotate($x1, $y1, $angle , &$x, &$y){
        $angle = deg2rad(-$angle);
        $cos = cos($angle);
        $sin = sin($angle);
        $x = $x1 * $cos - $y1 * $sin;
        $y = $x1 * $sin + $y1 * $cos;
    }
}

class BarcodeI25 {
    static private $encoding = array('NNWWN', 'WNNNW', 'NWNNW', 'WWNNN', 'NNWNW', 'WNWNN', 'NWWNN', 'NNNWW', 'WNNWN','NWNWN');

    static public function compute($code, $crc, $type){
        if (! $crc) {
            if (strlen($code) % 2) $code = '0' . $code;
        } else {
            if ( ($type == 'int25') && (strlen($code) % 2 == 0) ) $code = '0' . $code;
            $odd = true;
            $sum = 0;
            for($i=strlen($code)-1; $i>-1; $i--){
                $v = intval($code[$i]);
                $sum += $odd ? 3 * $v : $v;
                $odd = ! $odd;
            }
            $code .= (string) ((10 - $sum % 10) % 10);
        }
        return($code);
    }

    static public function getDigit($code, $crc, $type){
        $code = self::compute($code, $crc, $type);
        if ($code == '') return($code);
        $result = '';

        if ($type == 'int25') { // Interleaved 2 of 5
            // start
            $result .= '1010';

            // digits + CRC
            $end = strlen($code) / 2;
            for($i=0; $i<$end; $i++){
                $c1 = $code[2*$i];
                $c2 = $code[2*$i+1];
                for($j=0; $j<5; $j++){
                    $result .= '1';
                    if (self::$encoding[$c1][$j] == 'W') $result .= '1';
                    $result .= '0';
                    if (self::$encoding[$c2][$j] == 'W') $result .= '0';
                }
            }
            // stop
            $result .= '1101';
        } else if ($type == 'std25') {
            // Standard 2 of 5 is a numeric-only barcode that has been in use a long time.
            // Unlike Interleaved 2 of 5, all of the information is encoded in the bars; the spaces are fixed width and are used only to separate the bars.
            // The code is self-checking and does not include a checksum.

            // start
            $result .= '11011010';

            // digits + CRC
            $end = strlen($code);
            for($i=0; $i<$end; $i++){
                $c = $code[$i];
                for($j=0; $j<5; $j++){
                    $result .= '1';
                    if (self::$encoding[$c][$j] == 'W') $result .= '11';
                    $result .= '0';
                }
            }
            // stop
            $result .= '11010110';
        }
        return($result);
    }
}


class BarcodeEAN {
    static private $encoding = array(
        array('0001101', '0100111', '1110010'),
        array('0011001', '0110011', '1100110'),
        array('0010011', '0011011', '1101100'),
        array('0111101', '0100001', '1000010'),
        array('0100011', '0011101', '1011100'),
        array('0110001', '0111001', '1001110'),
        array('0101111', '0000101', '1010000'),
        array('0111011', '0010001', '1000100'),
        array('0110111', '0001001', '1001000'),
        array('0001011', '0010111', '1110100')
    );

    static private $first = array('000000','001011','001101','001110','010011','011001','011100','010101','010110','011010');

    static public function getDigit($code, $type){
        // Check len (12 for ean13, 7 for ean8)
        $len = $type == 'ean8' ? 7 : 12;
        $code = substr($code, 0, $len);
        if (!preg_match('`[0-9]{'.$len.'}`', $code)) return('');

        // get checksum
        $code = self::compute($code, $type);

        // process analyse
        $result = '101'; // start

        if ($type == 'ean8'){
            // process left part
            for($i=0; $i<4; $i++){
                $result .= self::$encoding[intval($code[$i])][0];
            }

            // center guard bars
            $result .= '01010';

            // process right part
            for($i=4; $i<8; $i++){
                $result .= self::$encoding[intval($code[$i])][2];
            }

        } else { // ean13
            // extract first digit and get sequence
            $seq = self::$first[ intval($code[0]) ];

            // process left part
            for($i=1; $i<7; $i++){
                $result .= self::$encoding[intval($code[$i])][ intval($seq[$i-1]) ];
            }

            // center guard bars
            $result .= '01010';

            // process right part
            for($i=7; $i<13; $i++){
                $result .= self::$encoding[intval($code[$i])][ 2 ];
            }
        } // ean13

        $result .= '101'; // stop
        return($result);
    }

    static public function compute($code, $type){
        $len = $type == 'ean13' ? 12 : 7;
        $code = substr($code, 0, $len);
        if (!preg_match('`[0-9]{'.$len.'}`', $code)) return('');
        $sum = 0;
        $odd = true;
        for($i=$len-1; $i>-1; $i--){
            $sum += ($odd ? 3 : 1) * intval($code[$i]);
            $odd = ! $odd;
        }
        return($code . ( (string) ((10 - $sum % 10) % 10)));
    }
}

class BarcodeUPC {

    static public function getDigit($code){
        if (strlen($code) < 12) {
            $code = '0' . $code;
        }
        return BarcodeEAN::getDigit($code, 'ean13');
    }

    static public function compute($code){
        if (strlen($code) < 12) {
            $code = '0' . $code;
        }
        return substr(BarcodeEAN::compute($code, 'ean13'), 1);
    }
}

class BarcodeMSI {
    static private $encoding = array(
        '100100100100', '100100100110', '100100110100', '100100110110',
        '100110100100', '100110100110', '100110110100', '100110110110',
        '110100100100', '110100100110');

    static public function compute($code, $crc){
        if (is_array($crc)){
            if ($crc['crc1'] == 'mod10'){
                $code = self::computeMod10($code);
            } else if ($crc['crc1'] == 'mod11'){
                $code = self::computeMod11($code);
            }
            if ($crc['crc2'] == 'mod10'){
                $code = self::computeMod10($code);
            } else if ($crc['crc2'] == 'mod11'){
                $code = self::computeMod11($code);
            }
        } else if ($crc){
            $code = self::computeMod10($code);
        }
        return($code);
    }

    static private function computeMod10($code){
        $len = strlen($code);
        $toPart1 = $len % 2;
        $n1 = 0;
        $sum = 0;
        for($i=0; $i<$len; $i++){
            if ($toPart1) {
                $n1 = 10 * $n1 + intval($code[$i]);
            } else {
                $sum += intval($code[$i]);
            }
            $toPart1 = ! $toPart1;
        }
        $s1 = (string) (2 * $n1);
        $len = strlen($s1);
        for($i=0; $i<$len; $i++){
            $sum += intval($s1[$i]);
        }
        return($code . ( (string) (10 - $sum % 10) % 10));
    }

    static private function computeMod11($code){
        $sum = 0;
        $weight = 2;
        for($i=strlen($code)-1; $i>-1; $i--){
            $sum += $weight * intval($code[$i]);
            $weight = $weight == 7 ? 2 : $weight + 1;
        }
        return($code . ( (string) (11 - $sum % 11) % 11) );
    }

    static public function getDigit($code, $crc){
        if (preg_match('`[^0-9]`', $code)) return '';
        $index = 0;
        $result = '';

        $code = self::compute($code, false);

        // start
        $result = '110';

        // digits
        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            $result .= self::$encoding[ intval($code[$i]) ];
        }

        // stop
        $result .= '1001';

        return($result);
    }
}

class Barcode11 {
    static private $encoding = array(
        '101011', '1101011', '1001011', '1100101',
        '1011011', '1101101', '1001101', '1010011',
        '1101001', '110101', '101101');

    static public function getDigit($code){
        if (preg_match('`[^0-9\-]`', $code)) return '';
        $result = '';
        $intercharacter = '0';

        // start
        $result = '1011001' . $intercharacter;

        // digits
        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            $index = $code[$i] == '-' ? 10 : intval($code[$i]);
            $result .= self::$encoding[ $index ] . $intercharacter;
        }

        // checksum
        $weightC    = 0;
        $weightSumC = 0;
        $weightK    = 1; // start at 1 because the right-most character is 'C' checksum
        $weightSumK = 0;
        for($i=$len-1; $i>-1; $i--){
            $weightC = $weightC == 10 ? 1 : $weightC + 1;
            $weightK = $weightK == 10 ? 1 : $weightK + 1;

            $index = $code[$i] == '-' ? 10 : intval($code[$i]);

            $weightSumC += $weightC * $index;
            $weightSumK += $weightK * $index;
        }

        $c = $weightSumC % 11;
        $weightSumK += $c;
        $k = $weightSumK % 11;

        $result .= self::$encoding[$c] . $intercharacter;

        if ($len >= 10){
            $result .= self::$encoding[$k] . $intercharacter;
        }

        // stop
        $result  .= '1011001';

        return($result);
    }
}

class Barcode39 {
    static private $encoding = array(
        '101001101101', '110100101011', '101100101011', '110110010101',
        '101001101011', '110100110101', '101100110101', '101001011011',
        '110100101101', '101100101101', '110101001011', '101101001011',
        '110110100101', '101011001011', '110101100101', '101101100101',
        '101010011011', '110101001101', '101101001101', '101011001101',
        '110101010011', '101101010011', '110110101001', '101011010011',
        '110101101001', '101101101001', '101010110011', '110101011001',
        '101101011001', '101011011001', '110010101011', '100110101011',
        '110011010101', '100101101011', '110010110101', '100110110101',
        '100101011011', '110010101101', '100110101101', '100100100101',
        '100100101001', '100101001001', '101001001001', '100101101101');
    static public function getDigit($code){
        $table = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%*';
        $result = '';
        $intercharacter = '0';

        if (strpos($code, '*') !== false) return('');

        // Add Start and Stop charactere : *
        $code = strtoupper('*' . $code . '*');

        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            $index = strpos($table, $code[$i]);
            if ($index === false) return('');
            if ($i > 0) $result .= $intercharacter;
            $result .= self::$encoding[ $index ];
        }
        return($result);
    }
}

class Barcode93{
    static private $encoding = array(
        '100010100', '101001000', '101000100', '101000010',
        '100101000', '100100100', '100100010', '101010000',
        '100010010', '100001010', '110101000', '110100100',
        '110100010', '110010100', '110010010', '110001010',
        '101101000', '101100100', '101100010', '100110100',
        '100011010', '101011000', '101001100', '101000110',
        '100101100', '100010110', '110110100', '110110010',
        '110101100', '110100110', '110010110', '110011010',
        '101101100', '101100110', '100110110', '100111010',
        '100101110', '111010100', '111010010', '111001010',
        '101101110', '101110110', '110101110', '100100110',
        '111011010', '111010110', '100110010', '101011110');

    static public function getDigit($code, $crc){
        $table = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%____*'; // _ => ($), (%), (/) et (+)
        $result = '';

        if (strpos($code, '*') !== false) return('');

        $code = strtoupper($code);

        // start :  *
        $result  .= self::$encoding[47];

        // digits
        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            $c = $code[$i];
            $index = strpos($table, $c);
            if ( ($c == '_') || ($index === false) ) return('');
            $result .= self::$encoding[ $index ];
        }

        // checksum
        if ($crc){
            $weightC    = 0;
            $weightSumC = 0;
            $weightK    = 1; // start at 1 because the right-most character is 'C' checksum
            $weightSumK = 0;
            for($i=$len-1; $i>-1; $i--){
                $weightC = $weightC == 20 ? 1 : $weightC + 1;
                $weightK = $weightK == 15 ? 1 : $weightK + 1;

                $index = strpos($table, $code[$i]);

                $weightSumC += $weightC * $index;
                $weightSumK += $weightK * $index;
            }

            $c = $weightSumC % 47;
            $weightSumK += $c;
            $k = $weightSumK % 47;

            $result .= self::$encoding[$c];
            $result .= self::$encoding[$k];
        }

        // stop : *
        $result  .= self::$encoding[47];

        // Terminaison bar
        $result  .= '1';
        return($result);
    }
}

class Barcode128 {
    static private $encoding = array(
        '11011001100', '11001101100', '11001100110', '10010011000',
        '10010001100', '10001001100', '10011001000', '10011000100',
        '10001100100', '11001001000', '11001000100', '11000100100',
        '10110011100', '10011011100', '10011001110', '10111001100',
        '10011101100', '10011100110', '11001110010', '11001011100',
        '11001001110', '11011100100', '11001110100', '11101101110',
        '11101001100', '11100101100', '11100100110', '11101100100',
        '11100110100', '11100110010', '11011011000', '11011000110',
        '11000110110', '10100011000', '10001011000', '10001000110',
        '10110001000', '10001101000', '10001100010', '11010001000',
        '11000101000', '11000100010', '10110111000', '10110001110',
        '10001101110', '10111011000', '10111000110', '10001110110',
        '11101110110', '11010001110', '11000101110', '11011101000',
        '11011100010', '11011101110', '11101011000', '11101000110',
        '11100010110', '11101101000', '11101100010', '11100011010',
        '11101111010', '11001000010', '11110001010', '10100110000',
        '10100001100', '10010110000', '10010000110', '10000101100',
        '10000100110', '10110010000', '10110000100', '10011010000',
        '10011000010', '10000110100', '10000110010', '11000010010',
        '11001010000', '11110111010', '11000010100', '10001111010',
        '10100111100', '10010111100', '10010011110', '10111100100',
        '10011110100', '10011110010', '11110100100', '11110010100',
        '11110010010', '11011011110', '11011110110', '11110110110',
        '10101111000', '10100011110', '10001011110', '10111101000',
        '10111100010', '11110101000', '11110100010', '10111011110',
        '10111101110', '11101011110', '11110101110', '11010000100',
        '11010010000', '11010011100', '11000111010');
    static public function getDigit($code){
        $tableB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
        $result = "";
        $sum = 0;
        $isum = 0;
        $i = 0;
        $j = 0;
        $value = 0;

        // check each characters
        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            if (strpos($tableB, $code[$i]) === false) return("");
        }

        // check firsts characters : start with C table only if enought numeric
        $tableCActivated = $len> 1;
        $c = '';
        for($i=0; $i<3 && $i<$len; $i++){
            $tableCActivated &= preg_match('`[0-9]`', $code[$i]);
        }

        $sum = $tableCActivated ? 105 : 104;

        // start : [105] : C table or [104] : B table
        $result = self::$encoding[ $sum ];

        $i = 0;
        while( $i < $len ){
            if (! $tableCActivated){
                $j = 0;
                // check next character to activate C table if interresting
                while ( ($i + $j < $len) && preg_match('`[0-9]`', $code[$i+$j]) ) $j++;

                // 6 min everywhere or 4 mini at the end
                $tableCActivated = ($j > 5) || (($i + $j - 1 == $len) && ($j > 3));

                if ( $tableCActivated ){
                    $result .= self::$encoding[ 99 ]; // C table
                    $sum += ++$isum * 99;
                }
                // 2 min for table C so need table B
            } else if ( ($i == $len - 1) || (preg_match('`[^0-9]`', $code[$i])) || (preg_match('`[^0-9]`', $code[$i+1])) ) { //todo : verifier le JS : len - 1!!! XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
                $tableCActivated = false;
                $result .= self::$encoding[ 100 ]; // B table
                $sum += ++$isum * 100;
            }

            if ( $tableCActivated ) {
                $value = intval(substr($code, $i, 2)); // Add two characters (numeric)
                $i += 2;
            } else {
                $value = strpos($tableB, $code[$i]); // Add one character
                $i++;
            }
            $result  .= self::$encoding[ $value ];
            $sum += ++$isum * $value;
        }

        // Add CRC
        $result  .= self::$encoding[ $sum % 103 ];

        // Stop
        $result .= self::$encoding[ 106 ];

        // Termination bar
        $result .= '11';

        return($result);
    }
}

class BarcodeCodabar {
    static private $encoding = array(
        '101010011', '101011001', '101001011', '110010101',
        '101101001', '110101001', '100101011', '100101101',
        '100110101', '110100101', '101001101', '101100101',
        '1101011011', '1101101011', '1101101101', '1011011011',
        '1011001001', '1010010011', '1001001011', '1010011001');

    static public function getDigit($code){
        $table = '0123456789-$:/.+';
        $result = '';
        $intercharacter = '0';

        // add start : A->D : arbitrary choose A
        $result .= self::$encoding[16] . $intercharacter;

        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            $index = strpos($table, $code[$i]);
            if ($index === false) return('');
            $result .= self::$encoding[ $index ] . $intercharacter;
        }

        // add stop : A->D : arbitrary choose A
        $result .= self::$encoding[16];
        return($result);
    }
}

class BarcodeDatamatrix {
    static private $lengthRows = array(
        10, 12, 14, 16, 18, 20, 22, 24, 26,  // 24 squares et 6 rectangular
        32, 36, 40, 44, 48, 52, 64, 72, 80,  88, 96, 104, 120, 132, 144,
        8, 8, 12, 12, 16, 16);
    static private $lengthCols = array(
        10, 12, 14, 16, 18, 20, 22, 24, 26,  // Number of columns for the entire datamatrix
        32, 36, 40, 44, 48, 52, 64, 72, 80, 88, 96, 104, 120, 132, 144,
        18, 32, 26, 36, 36, 48);
    static private $dataCWCount = array(
        3, 5, 8, 12,  18,  22,  30,  36,  // Number of data codewords for the datamatrix
        44, 62, 86, 114, 144, 174, 204, 280, 368, 456, 576, 696, 816, 1050,
        1304, 1558, 5, 10, 16, 22, 32, 49);
    static private $solomonCWCount = array(
        5, 7, 10, 12, 14, 18, 20, 24, 28, // Number of Reed-Solomon codewords for the datamatrix
        36, 42, 48, 56, 68, 84, 112, 144, 192, 224, 272, 336, 408, 496, 620,
        7, 11, 14, 18, 24, 28);
    static private $dataRegionRows = array(
        8, 10, 12, 14, 16, 18, 20, 22, // Number of rows per region
        24, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 18, 20, 22,
        6,  6, 10, 10, 14, 14);
    static private $dataRegionCols = array(
        8, 10, 12, 14, 16, 18, 20, 22, // Number of columns per region
        24, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 18, 20, 22,
        16, 14, 24, 16, 16, 22);
    static private $regionRows = array(
        1, 1, 1, 1, 1, 1, 1, 1, // Number of regions per row
        1, 2, 2, 2, 2, 2, 2, 4, 4, 4, 4, 4, 4, 6, 6, 6,
        1, 1, 1, 1, 1, 1);
    static private $regionCols = array(
        1, 1, 1, 1, 1, 1, 1, 1, // Number of regions per column
        1, 2, 2, 2, 2, 2, 2, 4, 4, 4, 4, 4, 4, 6, 6, 6,
        1, 2, 1, 2, 2, 2);
    static private $interleavedBlocks = array(
        1, 1, 1, 1, 1, 1, 1, 1, // Number of blocks
        1, 1, 1, 1, 1, 1, 2, 2, 4, 4, 4, 4, 6, 6, 8, 8,
        1, 1, 1, 1, 1, 1);
    static private $logTab = array(
        -255, 255, 1, 240, 2, 225, 241, 53, 3,  // Table of log for the Galois field
        38, 226, 133, 242, 43, 54, 210, 4, 195, 39, 114, 227, 106, 134, 28,
        243, 140, 44, 23, 55, 118, 211, 234, 5, 219, 196, 96, 40, 222, 115,
        103, 228, 78, 107, 125, 135, 8, 29, 162, 244, 186, 141, 180, 45, 99,
        24, 49, 56, 13, 119, 153, 212, 199, 235, 91, 6, 76, 220, 217, 197,
        11, 97, 184, 41, 36, 223, 253, 116, 138, 104, 193, 229, 86, 79, 171,
        108, 165, 126, 145, 136, 34, 9, 74, 30, 32, 163, 84, 245, 173, 187,
        204, 142, 81, 181, 190, 46, 88, 100, 159, 25, 231, 50, 207, 57, 147,
        14, 67, 120, 128, 154, 248, 213, 167, 200, 63, 236, 110, 92, 176, 7,
        161, 77, 124, 221, 102, 218, 95, 198, 90, 12, 152, 98, 48, 185, 179,
        42, 209, 37, 132, 224, 52, 254, 239, 117, 233, 139, 22, 105, 27, 194,
        113, 230, 206, 87, 158, 80, 189, 172, 203, 109, 175, 166, 62, 127,
        247, 146, 66, 137, 192, 35, 252, 10, 183, 75, 216, 31, 83, 33, 73,
        164, 144, 85, 170, 246, 65, 174, 61, 188, 202, 205, 157, 143, 169, 82,
        72, 182, 215, 191, 251, 47, 178, 89, 151, 101, 94, 160, 123, 26, 112,
        232, 21, 51, 238, 208, 131, 58, 69, 148, 18, 15, 16, 68, 17, 121, 149,
        129, 19, 155, 59, 249, 70, 214, 250, 168, 71, 201, 156, 64, 60, 237,
        130, 111, 20, 93, 122, 177, 150);
    static private $aLogTab = array(
        1, 2, 4, 8, 16, 32, 64, 128, 45, 90, // Table of aLog for the Galois field
        180, 69, 138, 57, 114, 228, 229, 231, 227, 235, 251, 219, 155, 27, 54,
        108, 216, 157, 23, 46, 92, 184, 93, 186, 89, 178, 73, 146, 9, 18, 36,
        72, 144, 13, 26, 52, 104, 208, 141, 55, 110, 220, 149, 7, 14, 28, 56,
        112, 224, 237, 247, 195, 171, 123, 246, 193, 175, 115, 230, 225, 239,
        243, 203, 187, 91, 182, 65, 130, 41, 82, 164, 101, 202, 185, 95, 190,
        81, 162, 105, 210, 137, 63, 126, 252, 213, 135, 35, 70, 140, 53, 106,
        212, 133, 39, 78, 156, 21, 42, 84, 168, 125, 250, 217, 159, 19, 38, 76,
        152, 29, 58, 116, 232, 253, 215, 131, 43, 86, 172, 117, 234, 249, 223,
        147, 11, 22, 44, 88, 176, 77, 154, 25, 50, 100, 200, 189, 87, 174, 113,
        226, 233, 255, 211, 139, 59, 118, 236, 245, 199, 163, 107, 214, 129,
        47, 94, 188, 85, 170, 121, 242, 201, 191, 83, 166, 97, 194, 169, 127,
        254, 209, 143, 51, 102, 204, 181, 71, 142, 49, 98, 196, 165, 103, 206,
        177, 79, 158, 17, 34, 68, 136, 61, 122, 244, 197, 167, 99, 198, 161,
        111, 222, 145, 15, 30, 60, 120, 240, 205, 183, 67, 134, 33, 66, 132,
        37, 74, 148, 5, 10, 20, 40, 80, 160, 109, 218, 153, 31, 62, 124, 248,
        221, 151, 3, 6, 12, 24, 48, 96, 192, 173, 119, 238, 241, 207, 179, 75,
        150, 1);
    static private function champGaloisMult($a, $b){  // MULTIPLICATION IN GALOIS FIELD GF(2^8)
        if(!$a || !$b) return 0;
        return self::$aLogTab[(self::$logTab[$a] + self::$logTab[$b]) % 255];
    }
    static private function champGaloisDoub($a, $b){  // THE OPERATION a * 2^b IN GALOIS FIELD GF(2^8)
        if (!$a) return 0;
        if (!$b) return $a;
        return self::$aLogTab[(self::$logTab[$a] + $b) % 255];
    }
    static private function champGaloisSum($a, $b){ // SUM IN GALOIS FIELD GF(2^8)
        return $a ^ $b;
    }
    static private function selectIndex($dataCodeWordsCount, $rectangular){ // CHOOSE THE GOOD INDEX FOR TABLES
        if (($dataCodeWordsCount<1 || $dataCodeWordsCount>1558) && !$rectangular) return -1;
        if (($dataCodeWordsCount<1 || $dataCodeWordsCount>49) && $rectangular)  return -1;

        $n = $rectangular ? 24 : 0;

        while (self::$dataCWCount[$n] < $dataCodeWordsCount) $n++;
        return $n;
    }
    static private function encodeDataCodeWordsASCII($text) {
        $dataCodeWords = array();
        $n = 0;
        $len = strlen($text);
        for ($i=0; $i<$len; $i++){
            $c = ord($text[$i]);
            if ($c > 127) {
                $dataCodeWords[$n] = 235;
                $c -= 127;
                $n++;
            } else if (($c>=48 && $c<=57) && ($i+1<$len) && (preg_match('`[0-9]`', $text[$i+1]))) {
                $c = (($c - 48) * 10) + intval($text[$i+1]);
                $c += 130;
                $i++;
            } else $c++;
            $dataCodeWords[$n] = $c;
            $n++;
        }
        return $dataCodeWords;
    }
    static private function addPadCW(&$tab, $from, $to){
        if ($from >= $to) return ;
        $tab[$from] = 129;
        for ($i=$from+1; $i<$to; $i++){
            $r = ((149 * ($i+1)) % 253) + 1;
            $tab[$i] = (129 + $r) % 254;
        }
    }
    static private function calculSolFactorTable($solomonCWCount){ // CALCULATE THE REED SOLOMON FACTORS
        $g = array_fill(0, $solomonCWCount+1, 1);
        for($i = 1; $i <= $solomonCWCount; $i++) {
            for($j = $i - 1; $j >= 0; $j--) {
                $g[$j] = self::champGaloisDoub($g[$j], $i);
                if($j > 0) $g[$j] = self::champGaloisSum($g[$j], $g[$j-1]);
            }
        }
        return $g;
    }
    static private function addReedSolomonCW($nSolomonCW, $coeffTab, $nDataCW, &$dataTab, $blocks){ // Add the Reed Solomon codewords
        $errorBlocks = $nSolomonCW / $blocks;
        $correctionCW = array();

        for($k = 0; $k < $blocks; $k++) {
            for ($i=0; $i < $errorBlocks; $i++) $correctionCW[$i] = 0;

            for ($i=$k; $i<$nDataCW; $i+=$blocks){
                $temp = self::champGaloisSum($dataTab[$i], $correctionCW[$errorBlocks-1]);
                for ($j=$errorBlocks-1; $j>=0; $j--){
                    if ( !$temp ) {
                        $correctionCW[$j] = 0;
                    } else {
                        $correctionCW[$j] = self::champGaloisMult($temp, $coeffTab[$j]);
                    }
                    if ($j>0) $correctionCW[$j] = self::champGaloisSum($correctionCW[$j-1], $correctionCW[$j]);
                }
            }
            // Renversement des blocs calcules
            $j = $nDataCW + $k;
            for ($i=$errorBlocks-1; $i>=0; $i--){
                $dataTab[$j] = $correctionCW[$i];
                $j=$j+$blocks;
            }
        }
        return $dataTab;
    }
    static private function getBits($entier){ // Transform integer to tab of bits
        $bits = array();
        for ($i=0; $i<8; $i++){
            $bits[$i] = $entier & (128 >> $i) ? 1 : 0;
        }
        return $bits;
    }
    static private function next($etape, $totalRows, $totalCols, $codeWordsBits, &$datamatrix, &$assigned){ // Place codewords into the matrix
        $chr = 0; // Place of the 8st bit from the first character to [4][0]
        $row = 4;
        $col = 0;

        do {
            // Check for a special case of corner
            if(($row == $totalRows) && ($col == 0)){
                self::patternShapeSpecial1($datamatrix, $assigned, $codeWordsBits[$chr], $totalRows, $totalCols);
                $chr++;
            } else if(($etape<3) && ($row == $totalRows-2) && ($col == 0) && ($totalCols%4 != 0)){
                self::patternShapeSpecial2($datamatrix, $assigned, $codeWordsBits[$chr], $totalRows, $totalCols);
                $chr++;
            } else if(($row == $totalRows-2) && ($col == 0) && ($totalCols%8 == 4)){
                self::patternShapeSpecial3($datamatrix, $assigned, $codeWordsBits[$chr], $totalRows, $totalCols);
                $chr++;
            }
            else if(($row == $totalRows+4) && ($col == 2) && ($totalCols%8 == 0)){
                self::patternShapeSpecial4($datamatrix, $assigned, $codeWordsBits[$chr], $totalRows, $totalCols);
                $chr++;
            }

            // Go up and right in the datamatrix
            do {
                if(($row < $totalRows) && ($col >= 0) && (!isset($assigned[$row][$col]) || $assigned[$row][$col]!=1)) {
                    self::patternShapeStandard($datamatrix, $assigned, $codeWordsBits[$chr], $row, $col, $totalRows, $totalCols);
                    $chr++;
                }
                $row -= 2;
                $col += 2;
            } while (($row >= 0) && ($col < $totalCols));
            $row += 1;
            $col += 3;

            // Go down and left in the datamatrix
            do {
                if(($row >= 0) && ($col < $totalCols) && (!isset($assigned[$row][$col]) || $assigned[$row][$col]!=1)){
                    self::patternShapeStandard($datamatrix, $assigned, $codeWordsBits[$chr], $row, $col, $totalRows, $totalCols);
                    $chr++;
                }
                $row += 2;
                $col -= 2;
            } while (($row < $totalRows) && ($col >=0));
            $row += 3;
            $col += 1;
        } while (($row < $totalRows) || ($col < $totalCols));
    }
    static private function patternShapeStandard(&$datamatrix, &$assigned, $bits, $row, $col, $totalRows, $totalCols){ // Place bits in the matrix (standard or special case)
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[0], $row-2, $col-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[1], $row-2, $col-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[2], $row-1, $col-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[3], $row-1, $col-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[4], $row-1, $col, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[5], $row, $col-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[6], $row, $col-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[7], $row, $col, $totalRows, $totalCols);
    }
    static private function patternShapeSpecial1(&$datamatrix, &$assigned, $bits, $totalRows, $totalCols ){
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[0], $totalRows-1,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[1], $totalRows-1,  1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[2], $totalRows-1,  2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[3], 0, $totalCols-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[4], 0, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[5], 1, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[6], 2, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[7], 3, $totalCols-1, $totalRows, $totalCols);
    }
    static private function patternShapeSpecial2(&$datamatrix, &$assigned, $bits, $totalRows, $totalCols ){
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[0], $totalRows-3,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[1], $totalRows-2,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[2], $totalRows-1,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[3], 0, $totalCols-4, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[4], 0, $totalCols-3, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[5], 0, $totalCols-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[6], 0, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[7], 1, $totalCols-1, $totalRows, $totalCols);
    }
    static private function patternShapeSpecial3(&$datamatrix, &$assigned, $bits, $totalRows, $totalCols ){
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[0], $totalRows-3,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[1], $totalRows-2,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[2], $totalRows-1,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[3], 0, $totalCols-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[4], 0, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[5], 1, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[6], 2, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[7], 3, $totalCols-1, $totalRows, $totalCols);
    }
    static private function patternShapeSpecial4(&$datamatrix, &$assigned, $bits, $totalRows, $totalCols ){
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[0], $totalRows-1,  0, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[1], $totalRows-1, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[2], 0, $totalCols-3, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[3], 0, $totalCols-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[4], 0, $totalCols-1, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[5], 1, $totalCols-3, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[6], 1, $totalCols-2, $totalRows, $totalCols);
        self::placeBitInDatamatrix($datamatrix, $assigned, $bits[7], 1, $totalCols-1, $totalRows, $totalCols);
    }
    static private function placeBitInDatamatrix(&$datamatrix, &$assigned, $bit, $row, $col, $totalRows, $totalCols){ // Put a bit into the matrix
        if ($row < 0) {
            $row += $totalRows;
            $col += 4 - (($totalRows+4)%8);
        }
        if ($col < 0) {
            $col += $totalCols;
            $row += 4 - (($totalCols+4)%8);
        }
        if (!isset($assigned[$row][$col]) || $assigned[$row][$col] != 1) {
            $datamatrix[$row][$col] = $bit;
            $assigned[$row][$col] = 1;
        }
    }
    static private function addFinderPattern($datamatrix, $rowsRegion, $colsRegion, $rowsRegionCW, $colsRegionCW){ // Add the finder pattern
        $totalRowsCW = ($rowsRegionCW+2) * $rowsRegion;
        $totalColsCW = ($colsRegionCW+2) * $colsRegion;

        $datamatrixTemp = array();
        $datamatrixTemp[0] = array_fill(0, $totalColsCW+2, 0);

        for ($i=0; $i<$totalRowsCW; $i++){
            $datamatrixTemp[$i+1] = array();
            $datamatrixTemp[$i+1][0] = 0;
            $datamatrixTemp[$i+1][$totalColsCW+1] = 0;
            for ($j=0; $j<$totalColsCW; $j++){
                if ($i%($rowsRegionCW+2) == 0){
                    if ($j%2 == 0){
                        $datamatrixTemp[$i+1][$j+1] = 1;
                    } else {
                        $datamatrixTemp[$i+1][$j+1] = 0;
                    }
                } else if ($i%($rowsRegionCW+2) == $rowsRegionCW+1){
                    $datamatrixTemp[$i+1][$j+1] = 1;
                } else if ($j%($colsRegionCW+2) == $colsRegionCW+1){
                    if ($i%2 == 0){
                        $datamatrixTemp[$i+1][$j+1] = 0;
                    } else {
                        $datamatrixTemp[$i+1][$j+1] = 1;
                    }
                } else if ($j%($colsRegionCW+2) == 0){
                    $datamatrixTemp[$i+1][$j+1] = 1;
                } else{
                    $datamatrixTemp[$i+1][$j+1] = 0;
                    $datamatrixTemp[$i+1][$j+1] = $datamatrix[$i-1-(2*(floor($i/($rowsRegionCW+2))))][$j-1-(2*(floor($j/($colsRegionCW+2))))]; // todo : parseInt => ?
                }
            }
        }
        $datamatrixTemp[$totalRowsCW+1] = array();
        for ($j=0; $j<$totalColsCW+2; $j++){
            $datamatrixTemp[$totalRowsCW+1][$j] = 0;
        }
        return $datamatrixTemp;
    }
    static public function getDigit($text, $rectangular){
        $dataCodeWords = self::encodeDataCodeWordsASCII($text); // Code the text in the ASCII mode
        $dataCWCount = count($dataCodeWords);
        $index = self::selectIndex($dataCWCount, $rectangular); // Select the index for the data tables
        $totalDataCWCount = self::$dataCWCount[$index]; // Number of data CW
        $solomonCWCount = self::$solomonCWCount[$index]; // Number of Reed Solomon CW
        $totalCWCount = $totalDataCWCount + $solomonCWCount; // Number of CW
        $rowsTotal = self::$lengthRows[$index]; // Size of symbol
        $colsTotal = self::$lengthCols[$index];
        $rowsRegion = self::$regionRows[$index]; // Number of region
        $colsRegion = self::$regionCols[$index];
        $rowsRegionCW = self::$dataRegionRows[$index];
        $colsRegionCW = self::$dataRegionCols[$index];
        $rowsLengthMatrice = $rowsTotal-2*$rowsRegion; // Size of matrice data
        $colsLengthMatrice = $colsTotal-2*$colsRegion;
        $blocks = self::$interleavedBlocks[$index];  // Number of Reed Solomon blocks
        $errorBlocks = $solomonCWCount / $blocks;

        self::addPadCW($dataCodeWords, $dataCWCount, $totalDataCWCount); // Add codewords pads

        $g = self::calculSolFactorTable($errorBlocks); // Calculate correction coefficients

        self::addReedSolomonCW($solomonCWCount, $g, $totalDataCWCount, $dataCodeWords, $blocks); // Add Reed Solomon codewords

        $codeWordsBits = array(); // Calculte bits from codewords
        for ($i=0; $i<$totalCWCount; $i++){
            $codeWordsBits[$i] = self::getBits($dataCodeWords[$i]);
        }

        $datamatrix = array_fill(0, $colsLengthMatrice, array());
        $assigned = array_fill(0, $colsLengthMatrice, array());

        // Add the bottom-right corner if needed
        if ( (($rowsLengthMatrice * $colsLengthMatrice) % 8) == 4) {
            $datamatrix[$rowsLengthMatrice-2][$colsLengthMatrice-2] = 1;
            $datamatrix[$rowsLengthMatrice-1][$colsLengthMatrice-1] = 1;
            $datamatrix[$rowsLengthMatrice-1][$colsLengthMatrice-2] = 0;
            $datamatrix[$rowsLengthMatrice-2][$colsLengthMatrice-1] = 0;
            $assigned[$rowsLengthMatrice-2][$colsLengthMatrice-2] = 1;
            $assigned[$rowsLengthMatrice-1][$colsLengthMatrice-1] = 1;
            $assigned[$rowsLengthMatrice-1][$colsLengthMatrice-2] = 1;
            $assigned[$rowsLengthMatrice-2][$colsLengthMatrice-1] = 1;
        }

        // Put the codewords into the matrix
        self::next(0,$rowsLengthMatrice,$colsLengthMatrice, $codeWordsBits, $datamatrix, $assigned);

        // Add the finder pattern
        $datamatrix = self::addFinderPattern($datamatrix, $rowsRegion, $colsRegion, $rowsRegionCW, $colsRegionCW);

        return $datamatrix;
    }
}
?>