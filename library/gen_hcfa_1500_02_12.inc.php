<?php
/*
 * Utilities to support HCFA 1500 02/12 Version
 * For details on format refer to:
 * <http://www.nucc.org/index.php?option=com_content&view=article&id=186&Itemid=138>
 *
 * @package OpenEMR
 * @author Kevin Yeh <kevin.y@integralemr.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Helper class to manage which rows and columns information belong in.
 * This allows "out of order" creation of the content.
 */
class hcfa_info
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
        put_hcfa($this->row, $this->column, $this->width, $this->info, '/#/');
    }
}

/**
 * comparator function for hfca_info class to allow proper sorting
 *
 * @param type $first
 * @param type $second
 * @return int
 */
function cmp_hcfa_info($first, $second)
{
    $first_value=$first->get_position();
    $second_value=$second->get_position();
    if ($first_value==$second_value) {
        return 0;
    }

    return $first_value<$second_value ? -1 : 1;
}

/**
 * calculate where on the form a given diagnosis belongs and add it to the entries
 *
 * @param array $hcfa_entries
 * @param type $number
 * @param type $diag
 */
function add_diagnosis(&$hcfa_entries, $number, $diag)
{
    /*
     * The diagnoses go across the page.
     * Positioned
     *  A B C D
     *  E F G H
     *  I J K L
     */
    $column_num = ($number%4);
    $row_num = (int)($number / 4);

    // First column is at location 3, each column is 13 wide
    $col_pos=3+13*$column_num;

    // First diagnosis row is 38
    $strip='/[.#]/';
    $diag = preg_replace($strip, '', strtoupper($diag));
    $row_pos=38+$row_num;
    $hcfa_entries[]=new hcfa_info($row_pos, $col_pos, 8, $diag);
}

/**
 * Process the diagnoses for a given claim. log any errors
 *
 * @param type $claim
 * @param string $log
 */
function process_diagnoses_02_12(&$claim, &$log)
{

    $hcfa_entries=array();
    $diags = $claim->diagArray(false);
    if ($claim->diagtype=='ICD10') {
        $icd_indicator='0';
    } else {
        $icd_indicator='9';
    }

    $hcfa_entries[]=new hcfa_info(37, 42, 1, $icd_indicator);

    // Box 22. Medicaid Resubmission Code and Original Ref. No.
    $hcfa_entries[]=new hcfa_info(38, 50, 10, $claim->medicaidResubmissionCode());
    $hcfa_entries[]=new hcfa_info(38, 62, 15, $claim->medicaidOriginalReference());

    // Box 23. Prior Authorization Number
    $hcfa_entries[]=new hcfa_info(40, 50, 28, $claim->priorAuth());

    $diag_count=0;
    foreach ($diags as $diag) {
        if ($diag_count<12) {
            add_diagnosis($hcfa_entries, $diag_count, $diag);
        } else {
            $log.= "***Too many diagnoses ".($diag_count+1).":".$diag;
        }

        $diag_count++;
    }

    // Sort the entries to put them in the page base sequence.
    usort($hcfa_entries, "cmp_hcfa_info");

    foreach ($hcfa_entries as $hcfa_entry) {
        $hcfa_entry->put();
    }
}
