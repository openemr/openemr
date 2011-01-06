<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com> 
//
// +------------------------------------------------------------------------------+
?>
<!-- Common php functions are stored in this page.  -->
<?php
function PrepareSearchItem($SearchItem)
 {//Parses the search value part of the criteria and prepares for sql.
  $SplitArray=split(' like ',$SearchItem);
  if(isset($SplitArray[1]))
   {
    $SplitArray[1] = substr($SplitArray[1], 0, -1); 
    $SplitArray[1] = substr($SplitArray[1], 1); 
    $SearchItem=$SplitArray[0].' like '."'".formDataCore($SplitArray[1])."'";
   }
  else
   {
      $SplitArray=split(' = ',$SearchItem);
      if(isset($SplitArray[1]))
       {
        $SplitArray[1] = substr($SplitArray[1], 0, -1); 
        $SplitArray[1] = substr($SplitArray[1], 1); 
        $SearchItem=$SplitArray[0].' = '."'".formDataCore($SplitArray[1])."'";
       }
   }
  return($SearchItem);
 }
function BuildArrayForReport($Query)
 {//Parses the database value and prepares for display.
  $array_data=array();
  $res = sqlStatement($Query);
  while($row=sqlFetchArray($res))
   {
    $array_data[$row['id']]=htmlspecialchars($row['name'],ENT_QUOTES);
   }
  return $array_data;
 }
function InsuranceCompanyDisplay()
 {//The criteria  "Insurance Company" is coded here.The ajax one
  global $ThisPageSearchCriteriaDisplay,$ThisPageSearchCriteriaKey,$ThisPageSearchCriteriaIndex,$web_root;
?>
<table width="140" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="140" colspan="2">
            <iframe id="frame_to_hide" style="position:absolute;display:none; width:240px; height:100px" frameborder=0 
                scrolling=no marginwidth=0 src="" marginheight=0>hello</iframe>
        <input type="hidden" id="hidden_ajax_close_value" value="<?php echo formData('type_code') ?>" /><input name='type_code'  id='type_code' class="text "
        style=" width:140px;"  title="<?php echo htmlspecialchars(xl("Type Id or Name.3 characters minimum (including spaces)."), ENT_QUOTES) ?>"
        onfocus="hide_frame_to_hide();appendOptionTextCriteria('<?php echo $ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex] ?>',
                                    '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',
                                    document.getElementById('type_code').value,document.getElementById('div_insurance_or_patient').innerHTML,
                                    ' = ',
                                    'text')" onblur="show_frame_to_hide()" onKeyDown="PreventIt(event)" value="<?php echo formData('type_code') ?>"  autocomplete="off"   /><br> 
        <!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
            <div id='ajax_div_insurance_section'>
            <div id='ajax_div_insurance_error'>            </div>
            <div id="ajax_div_insurance" style="display:none;"></div>
            </div>
            </div>        </td>
      </tr>
      <tr height="5"><td colspan="2"></td></tr>
      <tr>
        <td><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:50px; height:17px;"><?php echo formData('hidden_type_code') ?></div><input type="hidden" name="description"  id="description" /></td>
        <td><a href="#" onClick="CleanUpAjax('<?php echo $ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex] ?>',
                                    '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',' = ')"><img src="<?php echo $web_root ?>/interface/pic/Clear.gif" border="0" /></a></td>
      </tr>
    </table>
<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo formData('hidden_type_code') ?>"/>
<?php
 }
?>