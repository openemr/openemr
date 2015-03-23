<?php
/**
 * Utilities to support HCFA 1500 02/12 Version
 * For details on format refer to: 
 * <http://www.nucc.org/index.php?option=com_content&view=article&id=186&Itemid=138>
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

/**
 * 
 * @return type Is the system configured to use the 02/12 version of the form
 */
function hcfa_1500_version_02_12()
{
    return $GLOBALS['cms_1500']=='1';
}


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
    public function __construct($row,$column,$width,$info)
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
        put_hcfa($this->row,$this->column,$this->width,$this->info,'/#/');
    }
}

/**
 * comparator function for hfca_info class to allow proper sorting
 * 
 * @param type $first
 * @param type $second
 * @return int
 */
function cmp_hcfa_info($first,$second)
{
    $first_value=$first->get_position();
    $second_value=$second->get_position();
    if($first_value==$second_value)
    {
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
function add_diagnosis(&$hcfa_entries,$number,$diag)
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
    $row_pos=38+$row_num;
    $hcfa_entries[]=new hcfa_info($row_pos,$col_pos,6,$diag);
    
    
}

/**
 * Process the diagnoses for a given claim. log any errors
 * 
 * @param type $claim
 * @param string $log
 */
function process_diagnoses_02_12(&$claim,&$log)
{

    $hcfa_entries=array();
    $diags = $claim->diagArray(false);
    if($claim->diagtype=='ICD10')
    {
        $icd_indicator='0';        
    }
    else
    {
        $icd_indicator='9';
    }
    
    $hcfa_entries[]=new hcfa_info(37,42,1,$icd_indicator);
    
    // Box 22. Medicaid Resubmission Code and Original Ref. No.
    $hcfa_entries[]=new hcfa_info(38,50,10,$claim->medicaidResubmissionCode());
    $hcfa_entries[]=new hcfa_info(38,62,10,$claim->medicaidOriginalReference());
    
    // Box 23. Prior Authorization Number
    $hcfa_entries[]=new hcfa_info(40,50,28,$claim->priorAuth());
    
    $diag_count=0;
    foreach($diags as $diag)
    {
        if($diag_count<12)
        {
            add_diagnosis($hcfa_entries,$diag_count,$diag);        
        }
        else
        {
            $log.= "***Too many diagnoses ".($diag_count+1).":".$diag;
        }
        $diag_count++;
    }
    
    // Sort the entries to put them in the page base sequence.
    usort($hcfa_entries,"cmp_hcfa_info");
    
    foreach($hcfa_entries as $hcfa_entry)
    {
        $hcfa_entry->put();
    }
}
?>
