<?php            
//  ------------------------------------------------------------------------ //
//                OpenEMR Electronic Medical Records System                  //
//                 Copyright (c) 2012 tajemo.co.za                      //
//                     <http://www.tajemo.co.za/>                            //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA // 
// --------------------------------------------------------------------------//
// Original Author of this file: Craig Bezuidenhout (Tajemo Enterprises)     //
// Purpose of this file: Used for displaying log of dated reminders          //
// --------------------------------------------------------------------------//

  $fake_register_globals=false;
  $sanitize_all_escapes=true;

  require_once("../../globals.php");
  require_once("$srcdir/htmlspecialchars.inc.php");
  require_once("$srcdir/acl.inc");    
  require_once("$srcdir/dated_reminders.php"); 
  
  
  $isAdmin =acl_check('admin', 'users');  
  
  // Temporary for allowing all users to see this
  $isAdmin = true;
?>
<?php
  /*
    -------------------  HANDLE POST ---------------------
  */
  if($_GET){ 
    echo '<table border="1" width="100%" cellpadding="5px" id="logTable">
            <thead>
              <tr>
                <th>'.xlt('ID').'</th>
                <th>'.xlt('Sent Date').'</th>
                <th>'.xlt('From').'</th>
                <th>'.xlt('To').'</th>
                <th>'.xlt('Patient').'</th>
                <th>'.xlt('Message').'</th>
                <th>'.xlt('Due Date').'</th>
                <th>'.xlt('Processed Date').'</th>
                <th>'.xlt('Processed By').'</th>
              </tr>
            </thead>
            <tbody>';
    $remindersArray = logRemindersArray();
    foreach($remindersArray as $RA){ 
      echo '<tr class="heading">
              <td>',text($RA['messageID']),'</td>
              <td>',text($RA['sDate']),'</td>
              <td>',text($RA['fromName']),'</td>
              <td>',text($RA['ToName']),'</td>
              <td>',text($RA['PatientName']),'</td>     
              <td>',text($RA['message']),'</td>    
              <td>',text($RA['dDate']),'</td>    
              <td>',text($RA['pDate']),'</td>      
              <td>',text($RA['processedByName']),'</td>
            </tr>';
    }
    echo '</tbody></table>'; 
    
    die;
  }
?> 
<html>
  <head>                                    
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css"> 
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>  
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-calendar.js"></script>   
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.grouprows.js"></script>     
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/grouprows.js"></script> 
    <script language="JavaScript">   
      $(document).ready(function (){  
        $('#sentTo_all').click(function(){ 
          $('.sentTo').attr('checked',"checked");
        })    
        $('#sentBy_all').click(function(){ 
          $('.sentBy').attr('checked',"checked");
        })
        $("#submitForm").click(function(){ 
          // top.restoreSession(); --> can't use this as it negates this ajax refresh
          $.get("dated_reminders_log.php?"+$("#logForm").serialize(), 
               function(data) {
                  $("#resultsDiv").html(data);  
                	return false;
               }
             )   
          return false;
        })
      }) 
    </script> 
  </head>
  <body class="body_top"> 
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>


<?php 
  if($isAdmin){
    $allUsers = array();
    $uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `active` = 1 AND id != ?',array(intval($_SESSION['authId'])));
    for($i=0; $uRow=sqlFetchArray($uSQL); $i++){ $allUsers[] = $uRow; } 
?>     
    <form method="get" id="logForm" onsubmit="return top.restoreSession()">         
      <h1><?php echo xlt('Dated Message Log') ?></h1>  
      <h2><?php echo xlt('filters') ?> :</h2>
      <blockquote><?php echo xlt('Date The Message Was Sent') ?><br />
<!----------------------------------------------------------------------------------------------------------------------------------------------------->  
      <?php echo xlt('Start Date') ?> : <input id="sd" type="text" name="sd" value="" onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo xla('yyyy-mm-dd'); ?>' />   &nbsp;&nbsp;&nbsp;  
<!----------------------------------------------------------------------------------------------------------------------------------------------------->   
      <?php echo xlt('End Date') ?> : <input id="ed" type="text" name="ed" value="" onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo xla('yyyy-mm-dd'); ?>' />   <br /><br />
<!----------------------------------------------------------------------------------------------------------------------------------------------------->   
      </blockquote>
      <p style="line-height:1.8em;">       
        <?php echo xlt('Sent By') ?> :                                     
        <input type="checkbox" id="sentBy_all"><label for="sentBy_all"><?php echo xlt('Select All') ?></label><br />
        <input class="sentBy" type="checkbox" name="sentBy_me" value="<?php echo attr(intval($_SESSION['authId'])) ?>" id="sentBy_me"><label for="sentBy_me"><?php echo xlt('Me') ?></label>&nbsp;&nbsp;&nbsp;&nbsp;   
        <?php //  
            $i = 2;   
            foreach($allUsers as $user){
              echo '<input class="sentBy" type="checkbox" name="sentBy_',$i,'" id="sentBy_',$i,'" value="',attr($user['id']),'"><label for="sentBy_',$i,'">',text($user['fname'].' '.$user['mname'].' '.$user['lname']),'</label>&nbsp;&nbsp;&nbsp;&nbsp; ';
              // line break for every 4 users
              if($i % 4 == 0) echo "<br />";  
              $i++; 
            }
        ?>    
      </p>         
<!----------------------------------------------------------------------------------------------------------------------------------------------------->     
      <p style="line-height:1.8em;">  
      <?php echo xlt('Sent To') ?> :     
        <input type="checkbox" id="sentTo_all"><label for="sentTo_all"><?php echo xlt('Select All') ?></label><br />
        <input class="sentTo" type="checkbox" name="sentTo_me" value="<?php echo attr(intval($_SESSION['authId'])) ?>" id="sentTo_me"><label for="sentTo_me"><?php echo xlt('Me') ?></label>&nbsp;&nbsp;&nbsp;&nbsp;   
        <?php //  
            $i = 2;   
            foreach($allUsers as $user){
              echo '<input class="sentTo" type="checkbox" name="sentTo_',$i,'" id="sentTo_',$i,'" value="',attr($user['id']),'"><label for="sentTo_',$i,'">',text($user['fname'].' '.$user['mname'].' '.$user['lname']),'</label>&nbsp;&nbsp;&nbsp;&nbsp; ';
              // line break for every 4 users
              if($i % 4 == 0) echo "<br />";  
              $i++; 
            }
        ?>  
      </p>    
<!-----------------------------------------------------------------------------------------------------------------------------------------------------> 
      <input type="checkbox" name="processed" id="processed"><label for="processed"><?php echo xlt('Processed') ?></label>      
<!-----------------------------------------------------------------------------------------------------------------------------------------------------> 
      <input type="checkbox" name="pending" id="pending"><label for="pending"><?php echo xlt('Pending') ?></label>          
<!----------------------------------------------------------------------------------------------------------------------------------------------------->  
      <br /><br />  
      <button value="Refresh" id="submitForm"><?php echo xlt('Refresh') ?></button>
    </form>
    
    <div id="resultsDiv"></div> 
<?php      
  }else{
    echo xlt('Permissions Error').'.';
  }
?>   
  </body> 
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script language="Javascript"> 
  Calendar.setup({inputField:"sd", ifFormat:"%Y-%m-%d", button:"img_begin_date", showsTime:'false'});  
  Calendar.setup({inputField:"ed", ifFormat:"%Y-%m-%d", button:"img_begin_date", showsTime:'false'}); 
</script>
</html> 