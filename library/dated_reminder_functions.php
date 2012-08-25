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
// Purpose of this file: Contains functions used in the dated reminders      //
// --------------------------------------------------------------------------//
 require_once("$srcdir/htmlspecialchars.inc.php");

// ------------------------------------------------
// @ RemindersArray function
// @ returns array with reminders for specified user, defaults to current user if none specified
// ------------------------------------------------   
   function RemindersArray($days_to_show,$today,$alerts_to_show,$userID = false){
        if(!$userID) $userID = $_SESSION['authId'];    
        global $hasAlerts;
// ----- define a blank reminders array
        $reminders = array();         
        
// ----- sql statement for getting uncompleted reminders (sorts by date, then by priority)  
          $drSQL = sqlStatement(
                            "SELECT 
                                    dr.pid, dr.dr_id, dr.dr_message_text,dr.dr_message_due_date, 
                                    u.fname ffname, u.mname fmname, u.lname flname
                            FROM `dated_reminders` dr 
                            JOIN `users` u ON dr.dr_from_ID = u.id 
                            JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id  
                            WHERE drl.to_id = ? 
                            AND dr.`message_processed` = 0
                            AND dr.`dr_message_due_date` < ADDDATE(NOW(), INTERVAL $days_to_show DAY) 
                            ORDER BY `dr_message_due_date` ASC , `message_priority` ASC LIMIT 0,$alerts_to_show"
                            , array($userID)
                            );                       
        
// --------- loop through the results
        for($i=0; $drRow=sqlFetchArray($drSQL); $i++){  
// --------- need to run patient query seperately to allow for reminders not linked to a patient  
          $pRow = array();
          if($drRow['pid'] > 0){ 
            $pSQL = sqlStatement("SELECT pd.title ptitle, pd.fname pfname, pd.mname pmname, pd.lname plname FROM `patient_data` pd WHERE pd.pid = ?",array($drRow['pid']));  
            $pRow = sqlFetchArray($pSQL);
           }
               
// --------- fill the $reminders array 
      		$reminders[$i]['messageID'] = $drRow['dr_id'];  
          $reminders[$i]['PatientID'] = $drRow['pid'];
          
// -------------------------------------  if there was a patient linked, set the name, else set it to blank          
          $reminders[$i]['PatientName'] = (empty($pRow) ? '' : $pRow['ptitle'].' '.$pRow['pfname'].' '.$pRow['pmname'].' '.$pRow['plname']);  
// -------------------------------------
          
      		$reminders[$i]['message'] = $drRow['dr_message_text'];             
      		$reminders[$i]['dueDate'] = $drRow['dr_message_due_date'];  
      		$reminders[$i]['fromName'] = $drRow['ffname'].' '.$drRow['fmname'].' '.$drRow['flname'];     
           
// --------- if the message is due or overdue set $hasAlerts to true, this will stop autohiding of reminders
          if(strtotime($drRow['dr_message_due_date']) <= $today) $hasAlerts = true;  
      	} 
// --------- END OF loop through the results
         
        return $reminders;
     }   
// ------------------------------------------------
// @ END OF RemindersArray function 
// ------------------------------------------------   




// ------------------------------------------------
// @ GetDueReminder function
// @ returns int with number of due reminders for specified user, defaults to current user if none specified
// ------------------------------------------------   
   function GetDueReminderCount($days_to_show,$today,$userID = false){
        if(!$userID) $userID = $_SESSION['authId']; 
        
// ----- sql statement for getting uncompleted reminders (sorts by date, then by priority)  
          $drSQL = sqlStatement(
                            "SELECT count(dr.dr_id) c
                            FROM `dated_reminders` dr 
                            JOIN `users` u ON dr.dr_from_ID = u.id 
                            JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id  
                            WHERE drl.to_id = ? 
                            AND dr.`message_processed` = 0
                            AND dr.`dr_message_due_date` < ADDDATE(NOW(), INTERVAL $days_to_show DAY)"
                            , array($userID)
                            );                       
        
          $drRow=sqlFetchArray($drSQL);  
          return $drRow['c'];
     }  
// ------------------------------------------------
// @ END OF GetDueReminder function 
// ------------------------------------------------   

// ------------------------------------------------
// @ GetAllReminderCount function
// @ returns int with number of unprocessed reminders for specified user, defaults to current user if none specified
// ------------------------------------------------   
   function GetAllReminderCount($userID = false){
        if(!$userID) $userID = $_SESSION['authId']; 
        
// ----- sql statement for getting uncompleted reminders  
          $drSQL = sqlStatement(
                            "SELECT count(dr.dr_id) c
                            FROM `dated_reminders` dr 
                            JOIN `users` u ON dr.dr_from_ID = u.id 
                            JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id  
                            WHERE drl.to_id = ? 
                            AND dr.`message_processed` = 0"
                            , array($userID)
                            );                       
        
          $drRow=sqlFetchArray($drSQL);  
          return $drRow['c'];
     }  
// ------------------------------------------------
// @ END OF GetAllReminderCount function 
// ------------------------------------------------  

// ------------------------------------------------
// @ getRemindersHTML(array $reminders)
// @ returns HTML as a string, for printing 
// ------------------------------------------------   
     function getRemindersHTML($reminders = array(),$today){ 
       global $hasAlerts;      
// --- initialize the string as blank
       $pdHTML = '';       
// --- loop through the $reminders
       foreach($reminders as $r){ 
// --- initialize $warning as the date, this is placed in front of the message
            $warning = text($r['dueDate']);                 
// --- initialize $class as 'text dr', this is the basic class
            $class='text dr'; 
             
// --------- check if reminder is  overdue   
            if(strtotime($r['dueDate']) < $today){
              $warning = '! '.xlt('OVERDUE');   
              $class = 'bold alert dr';
            }         
// --------- check if reminder is due 
            elseif(strtotime($r['dueDate']) == $today){
              $warning = xlt('TODAY');                                              
              $class='bold alert dr'; 
            } 
            // end check if reminder is due or overdue
            // apend to html string 
            $pdHTML .= '<p id="p_'.attr($r['messageID']).'">
                          <a class="dnRemover css_button_small" onclick="updateme('."'".attr($r['messageID'])."'".')" id="'.attr($r['messageID']).'" href="#">
                            <span>'.xlt('Set As Completed').'</span>
                          </a> 
                          <span title="'.($r['PatientID'] > 0 ? xla('Click Patient Name to Open Patient File') : '').'" class="'.attr($class).'">'. 
                            $warning.'         
                            <span onclick="goPid('.attr($r['PatientID']).')" class="patLink" id="'.attr($r['PatientID']).'">'.
                              text($r['PatientName']).'
                            </span> '.
                            text($r['message']).' - ['.text($r['fromName']).']
                          </span> -----> 
                          <a onclick="openAddScreen('.attr($r['messageID']).')" class="dnForwarder" id="'.attr($r['messageID']).'" href="#">[ '.xlt('Forward').' ]</a>
                        </p>';
          }
      return ($pdHTML == '' ? '<p class="alert"><br />'.xlt('No Reminders').'</p>' : $pdHTML); 
    }        
// ------------------------------------------------
// @ END OF getRemindersHTML function 
// ------------------------------------------------  
   

// ------------------------------------------------
// @ setReminderAsProccessed(int $rID)
// @ marks reminder as processed
// ------------------------------------------------   
function setReminderAsProcessed($rID,$userID = false){
  if(!$userID) $userID = $_SESSION['authId'];    
  if(is_numeric($rID) and $rID > 0){  
// --- check if this user can remove this message
// --- need a better way of checking the current user, I don't like using $_SESSION for checks
      $rdrSQL = sqlStatement("SELECT count(dr.dr_id) c FROM `dated_reminders` dr JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id WHERE drl.to_id = ? AND dr.`dr_id` = ? LIMIT 0,1", array($userID,$rID));  
      $rdrRow=sqlFetchArray($rdrSQL);
    
// --- if this user can delete this message (ie if it was sent to this user)
      if($rdrRow['c'] == 1){  
// ----- update the data, set the message to proccesses
        sqlStatement("UPDATE `dated_reminders` SET  `message_processed` = 1, `processed_date` = NOW(), `dr_processed_by` = ? WHERE `dr_id` = ? ", array(intval($userID),intval($rID)));  
      } 
   }
}    
// ------------------------------------------------
// @ END OF setReminderAsProccessed function 
// ------------------------------------------------  


// ------------------------------------------------
// @ getReminderById(int $mID)
// @ returns an array with message details for forwarding
// ------------------------------------------------ 
function getReminderById($mID,$userID = false){
  if(!$userID) $userID = $_SESSION['authId'];   
  $rdrSQL = sqlStatement("SELECT * FROM `dated_reminders` dr 
                            JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id  
                            WHERE drl.to_id = ? AND dr.`dr_id` = ? LIMIT 0,1", array($userID,$mID));  
  $rdrRow=sqlFetchArray($rdrSQL);
  if(!empty($rdrRow)){ 
    return $rdrRow;  
  }
  return false;   
}     
// ------------------------------------------------
// @ END OF getReminderById function 
// ------------------------------------------------  


// ------------------------------------------------
// @ getReminderById(
//                    array $sendTo 
//                    int $fromID    
//                    string $message  
//                    date $dueDate    
//                    int $patID       
//                    int $priority
//                   )
// @ returns an array with message details for forwarding
// ------------------------------------------------ 
function sendReminder($sendTo,$fromID,$message,$dueDate,$patID,$priority){
    if(          
// ------- Should run data checks before running this function for more accurate error reporting
// ------- check sendTo is not empty 
           !empty($sendTo) and    
// ------- check dueDate, only allow valid dates, todo -> enhance date checker 
           preg_match('/\d{4}[-]\d{2}[-]\d{2}/',$dueDate) and     
// ------- check priority, only allow 1-3 
           intval($priority) <= 3 and       
// ------- check message, only up to 255 characters
           strlen($message) <= 255 and strlen($message) > 0 and 
// ------- check if PatientID is set and in numeric
           is_numeric($patID)                 
         ){    
// ------- check for valid recipient           
             $cRow=sqlFetchArray(sqlStatement('SELECT count(id) FROM  `users` WHERE  `id` = ?',array($sendDMTo)));    
             if($cRow == 0){ 
               return false;
             }           
          // ------- if no errors  
          // --------- insert the new message
              $mID = sqlInsert("INSERT INTO `dated_reminders` 
                             (`dr_from_ID` ,`dr_message_text` ,`dr_message_sent_date` ,`dr_message_due_date` ,`pid` ,`message_priority` ,`message_processed` ,`processed_date`)
                              VALUES (?, ?, NOW( ), ?, ?, ?, '0', '');",
                              array($fromID,$message,$dueDate,$patID,$priority));
               
               foreach($sendTo as $st){ 
                 sqlInsert("INSERT INTO `dated_reminders_link` 
                            (`dr_id` ,`to_id`)
                            VALUES (?, ?);",
                                array($mID,$st));               
               }; 
               return true;
             } //---- end of if block
             return false;  
}

// ------- get current patient name
// ---- returns string, blank if no current patient
function getPatName($patientID){  
  $patientID = intval($patientID);
  $pSQL = sqlStatement("SELECT pd.title ptitle, pd.fname pfname, pd.mname pmname, pd.lname plname FROM `patient_data` pd WHERE pd.pid = ?",array($patientID));  
  $pRow = sqlFetchArray($pSQL); 
  return (empty($pRow) ? '' : $pRow['ptitle'].' '.$pRow['pfname'].' '.$pRow['pmname'].' '.$pRow['plname']);
}   

// -- log reminders array function uses $_GET to filter
function logRemindersArray(){   
        
        // set blank array for data to be parsed to sql 
        $input = array(); 
        // set blank string for the query
        $where = '';        
        $sentBy = $_GET['sentBy'];       
        $sentTo = $_GET['sentTo'];  
//------------------------------------------
// ----- HANDLE SENT BY FILTER 
          if(!empty($sentBy)){
            $sbCount = 0;  
            foreach($sentBy as $sb){
              $where .= ($sbCount == 0 ? '(' : ' OR ').'dr.dr_from_ID = ? ';
              $sbCount++;
              $input[] = $sb;
            }
            $where .= ')';
          }   
//------------------------------------------   
// ----- HANDLE SENT TO FILTER 
          if(!empty($sentTo)){
            $where = ($where == '' ? '' : $where.' AND ');
            $stCount = 0;  
            foreach($sentTo as $st){
              $where .= ($stCount == 0 ? '(' : ' OR ').'drl.to_id = ? ';
              $stCount++;
              $input[] = $st;
            }
            $where .= ')';
          }        
//------------------------------------------   
// ----- HANDLE PROCCESSED/PENDING FILTER ONLY RUN THIS IF BOTH ARE NOT SET        
          if(isset($_GET['processed']) and !isset($_GET['pending'])){ 
            $where = ($where == '' ? 'dr.message_processed = 1' : $where.' AND dr.message_processed = 1'); 
          } 
          elseif(!isset($_GET['processed']) and isset($_GET['pending'])){ 
            $where = ($where == '' ? 'dr.message_processed = 0' : $where.' AND dr.message_processed = 0'); 
          }   
//------------------------------------------   
// ----- HANDLE DATE RANGE FILTERS
          if(isset($_GET['sd']) and $_GET['sd'] != ''){  
            $where = ($where == '' ? 'dr.dr_message_sent_date >= ?' : $where.' AND dr.dr_message_sent_date >= ?'); 
            $input[] = $_GET['sd'].' 00:00:00';
          }  
          if(isset($_GET['ed']) and $_GET['ed'] != ''){  
            $where = ($where == '' ? 'dr.dr_message_sent_date <= ?' : $where.' AND dr.dr_message_sent_date <= ?'); 
            $input[] = $_GET['ed'].' 24:00:00';
          }      
//------------------------------------------  
          
           
//-------- add the "WHERE" the string if string is not blank, avoid sql errors for blannk WHERE statements 
          $where = ($where == '' ? '' : 'WHERE '.$where);  
            
// ----- define a blank reminders array
        $reminders = array();  
        
// ----- sql statement for getting uncompleted reminders (sorts by date, then by priority)  
          $drSQL = sqlStatement(       
                            "SELECT 
                                    dr.pid, dr.dr_id, dr.dr_message_text, dr.dr_message_due_date dDate, dr.dr_message_sent_date sDate,dr.processed_date processedDate, dr.dr_processed_by,
                                    u.fname ffname, u.mname fmname, u.lname flname,
                                    tu.fname tfname, tu.mname tmname, tu.lname tlname 
                            FROM `dated_reminders` dr       
                            JOIN `dated_reminders_link` drl ON dr.dr_id = drl.dr_id        
                            JOIN `users` u ON dr.dr_from_ID = u.id  
                            JOIN `users` tu ON drl.to_id = tu.id        
                            $where"   
                            ,$input);
// --------- loop through the results
        for($i=0; $drRow=sqlFetchArray($drSQL); $i++){  
// --------- need to run patient query seperately to allow for messages not linked to a patient  
          $pSQL = sqlStatement("SELECT pd.title ptitle, pd.fname pfname, pd.mname pmname, pd.lname plname FROM `patient_data` pd WHERE pd.pid = ?",array($drRow['pid']));  
          $pRow = sqlFetchArray($pSQL);   
           
          $prSQL = sqlStatement("SELECT u.fname pfname, u.mname pmname, u.lname plname FROM `users` u WHERE u.id = ?",array($drRow['dr_processed_by']));  
          $prRow = sqlFetchArray($prSQL );
          
// --------- fill the $reminders array 
      		$reminders[$i]['messageID'] = $drRow['dr_id'];
          $reminders[$i]['PatientID'] = $drRow['pid'];
          
          $reminders[$i]['pDate'] = ($drRow['processedDate'] == '0000-00-00 00:00:00' ? 'N/A' : $drRow['processedDate']);              
      		$reminders[$i]['sDate'] = $drRow['sDate'];                    
      		$reminders[$i]['dDate'] = $drRow['dDate'];               
          
// -------------------------------------  if there was a patient linked, set the name, else set it to blank          
          $reminders[$i]['PatientName'] = (empty($pRow) ? 'N/A' : $pRow['ptitle'].' '.$pRow['pfname'].' '.$pRow['pmname'].' '.$pRow['plname']);  
// -------------------------------------
          
      		$reminders[$i]['message'] = $drRow['dr_message_text'];      
      		$reminders[$i]['fromName'] = $drRow['ffname'].' '.$drRow['fmname'].' '.$drRow['flname']; 
          $reminders[$i]['ToName'] = $drRow['tfname'].' '.$drRow['tmname'].' '.$drRow['tlname'];  
          $reminders[$i]['processedByName'] = (empty($prRow) ? 'N/A' : $prRow['ptitle'].' '.$prRow['pfname'].' '.$prRow['pmname'].' '.$prRow['plname']);  
      	} 
// --------- END OF loop through the results

        return $reminders;
     }     
?>
