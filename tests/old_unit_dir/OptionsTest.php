<?php

/* Copyright © 2010 by Andrew Moore <amoore@cpan.org> */
/* Licensing information appears at the end of this file. */
require_once dirname(__FILE__) . '/BaseHarness.class.php';

class OptionsTest extends BaseHarness
{
    public function test_disp_end_cell()
    {
        global $item_count;
        $item_count = 1;
        $expected = '</td>';
        ob_start();
        disp_end_cell();
        $captured = ob_get_clean();
        $this->assertEquals($expected, $captured);
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
