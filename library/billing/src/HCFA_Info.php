<?php
/**
 * Helper class to manage rows and columns for the information on
 * the HCFA 1500 02/12 claim form.
 * This allows "out of order" creation of the content.
 *
 * @package OpenEMR
 * @author Kevin Yeh <kevin.y@integralemr.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use OpenEMR\Billing\HCFA_1500;

class HCFA_Info
{
    protected $row;
    protected $column;
    protected $width;
    protected $info;

    /**
     *
     * @param type $row    Which row to put this data on
     * @param type $column Which column to put this data in
     * @param type $width  How many characters max to print on
     * @param type $info   The text to print on the form at the specified location
     */
    public function __construct($row, $column, $width, $info)
    {
        $this->row=$row;
        $this->column=$column;
        $this->width=$width;
        $this->info=$info;
    }

    /**
     * Determine relative position of an element
     *
     * @return type integer
     */
    public function get_position()
    {
        return $this->row*100+$this->column;
    }

    /**
     * Add the info to the form
     */
    public function put()
    {
        // Override the default value for "strip" with put_hcfa to keep periods

        HCFA_1500::put_hcfa($this->row, $this->column, $this->width, $this->info, '/#/');
    }
    /**
     * comparator function for hfca_info class to allow proper sorting
     *
     * @param type $first
     * @param type $second
     * @return int
     */
}
