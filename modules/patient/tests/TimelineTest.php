<?php
/**
 * OpenEMR (http://open-emr.org).
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Patient\Timeline;

class TimelineTest extends PHPUnit_Framework_TestCase
{

    public function testCanGetForms()
    {
        $expected = [
            [
                'id' => '1',
                'date' => '2017-04-10 00:00:00',
                'encounter' => '2',
                'form_name' => 'New Patient Encounter',
                'form_id' => '1',
                'user' => 'rdown',
                'authorized' => '1',
                'deleted' => '0',
                'formdir' => 'newpatient',
                'therapy_group_id' => null
            ]
        ];
        $t = new Timeline(1);
        $actual = $t->forms();
        $this->assertEquals($expected, $actual);
    }

    public function testCanGetFormsSubset()
    {
        $t = new Timeline(1);
        $actual = $t->forms(['id']);
        $this->assertEquals([['id' => '1']], $actual);
    }

    public function testPidWithNoForms()
    {
        // There should never be a PID with a value of -1, so test with that
        $t = new Timeline(-1);
        $actual = $t->forms();
        $this->assertEquals([], $actual);
    }
}
