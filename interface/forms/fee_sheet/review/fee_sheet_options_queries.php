<?php
/**
 * Utility functions for retrieving fee sheet options.
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
 * Object representing the code,description and price for a fee sheet option
 * (typically a procedure code).
 */
class fee_sheet_option
{
    function __construct($c,$ct,$desc,$price,$category)
    {
        $this->code=$c;
        $this->code_type=$ct;
        $this->description=$desc;
        $this->price=$price;
        $this->category=$category;
        if($price==null)
        {
            $this->price=xl("Not Specified");
        }
    }
    public $code;    
    public $code_type;
    public $description;
    public $price;
    public $fee_display;
    public $category;

}
/**
 * get a list of fee sheet options
 * 
 * @param string $pricelevel which pricing level to retrieve
 * @return an array containing the options
 */
function load_fee_sheet_options($pricelevel)
{
    $clFSO_code_type='substring_index(fso.fs_codes,"|",1)';
    $clFSO_code='replace(substring_index(fso.fs_codes,"|",-2),"|","")';
    
    $sql= "SELECT codes.code,code_types.ct_key as code_type,codes.code_text,pr_price,fso.fs_category"
        . " FROM fee_sheet_options as fso, code_types "
        . " ,codes LEFT JOIN prices ON (codes.id=prices.pr_id AND prices.pr_level=?)"    
        . " WHERE codes.code=".$clFSO_code
        . " AND code_types.ct_key=".$clFSO_code_type
        . " AND codes.code_type=code_types.ct_id"
        . " ORDER BY fso.fs_category,fso.fs_option";
    
    $results=sqlStatement($sql,array($pricelevel));

    $retval=array();
    while($res=sqlFetchArray($results))
    {
        $fso=new fee_sheet_option($res['code'],$res['code_type'],$res['code_text'],$res['pr_price'],$res['fs_category']);
        $retval[]=$fso;
    }
    
    return $retval;
}
?>
