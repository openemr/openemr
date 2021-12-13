<?php

/* Copyright © 2010 by Andrew Moore <amoore@cpan.org> */
/* Licensing information appears at the end of this file. */

error_reporting(E_ALL);
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../library/formatting.inc.php';

class FormattingTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider example_american_two_decimal
   */
    public function testAmericanTwoDecimal($amount, $formatted)
    {
        $GLOBALS['currency_decimals']       = '2';
        $GLOBALS['currency_dec_point']      = '.';
        $GLOBALS['currency_thousands_sep']  = ',';
        $this->assertEquals($formatted, oeFormatMoney($amount), "'$amount' converts to '$formatted'");
    }

    public static function example_american_two_decimal()
    {

        return array( array(0,          '0.00'),
                  array(1,          '1.00'),
                  array(11,         '11.00'),
                  array('12.3',     '12.30'),
                  array('12.34',    '12.34'),
                  array('12.344',   '12.34'), // round down
                  array('12.345',   '12.35'), // round up
                  array('123.45',   '123.45'),
                  array('1234.56',  '1,234.56'),
                  array('12345.67', '12,345.67'),
                  );
    }
}

/*
This file is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as publish by the Free Software
Foundation.

This file is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU Gneral Public License for more details.

You should have received a copy of the GNU General Public Licence along with
this file.  If not see <http://www.gnu.org/licenses/>.
*/
