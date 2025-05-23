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

class HCFAInfo
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
        $this->row = $row;
        $this->column = $column;
        $this->width = $width;
        $this->info = $info;
    }

    /**
     * getters for properties
     *
     */
    public function getRow()
    {
        return $this->row;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Determine relative position of an element
     *
     * @return type integer
     */
    private function getPosition()
    {
        return $this->row * 100 + $this->column;
    }

    /**
     * comparator function for hfca_info class to allow proper sorting
     *
     * @param type $first
     * @param type $second
     * @return int
     */
    public static function cmpHcfaInfo($first, $second)
    {
        $first_value = $first->getPosition();
        $second_value = $second->getPosition();

        if ($first_value == $second_value) {
            return 0;
        }

        return $first_value < $second_value ? -1 : 1;
    }
}
