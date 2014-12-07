<?php
/*
//////////////This code can be modified to send email  or dated reminders according to conditional  statements.
//////////////The below example checks a non-standard users table field called "c_super",
////////////// where that person's supervisor user ID is stored...which is not in the code base:  so take that into account!
/////////////  It also checks for the encounter category  with the string "Event" in it's name, and 
/////////////  triggers the code if that is true and if it is a new (first time saving) of the encounter.
/////////////  This is used for alerting  supervisory staff when something like 'Event:  Baker Act' happens!
/////////////  Art@starfrontiers.org
$events = sqlStatement("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid = '".$pc_catid."'");
$event_res= SqlFetchArray($events);
$event_name=$event_res['pc_catname'];
$EVENT = "Event";

if(stripos($event_name,$EVENT)!==FALSE) {
                                  
      //Write a new line to dated reminders to alert counseling  supers.                  
    sqlStatement("INSERT INTO dated_reminders SET " . 						
 						"dr_from_id = '" . add_escape_custom($_SESSION['authUserID']) . "', " .
 						"dr_message_text = '" . add_escape_custom('New EVENT for this patient!') . "', " .
 						"dr_message_sent_date = NOW(), " .
 						"dr_message_due_date = NOW(), " .
						"pid = '" .$_SESSION["pid"] . "', " .
                        "message_priority = 3, " .
                        "message_processed = 0, " . 
                        "processed_date = '" . add_escape_custom('0000-00-00 00:00:00') . "', " .
                        "dr_processed_by = 0 "); 
     //find the id of the last reminder so we can add it to a line for the dated reminders link table.                   
    $inc2  = sqlStatement("SELECT MAX(dr_id)as linknum FROM dated_reminders");
    $piece  = sqlStatement("SELECT c_super FROM users WHERE id = '".$_SESSION['authUserID']."'");
    
$increment2= SqlFetchArray($inc2);
$poo2=0+$increment2['linknum'];

 $superlink = SqlFetchArray($piece);
$super =$superlink['c_super'];

//add to dated reminders link table
         
 						sqlStatement("INSERT INTO dated_reminders_link SET " . 						
 						"dr_id = '" .$poo2 . "', " .
 						"to_id = 8");
 						sqlStatement("INSERT INTO dated_reminders_link SET " . 						
 						"dr_id = '" .$poo2 . "', " .
 						"to_id = 68");
 						if ($super>0 && $super !=8 && $super !=68){
 						  sqlStatement("INSERT INTO dated_reminders_link SET " . //good						
 						"dr_id = '" .$poo2 . "', " .							//good
 						"to_id = '$super'");}
 		//echo($_SESSION['authUserID']);	//test	
      
 $emailarray = sqlStatement("SELECT email FROM users WHERE id = '".$superlink['c_super']."'");
 $email= SqlFetchArray($emailarray);
 $superemail=$email['email'];
 //define the receiver of the email
$to = "person1@oemr.org,person2@oemr.org,person2@oemr.org,".$superemail;
//define the subject of the email
$subject = 'ALERT: EVENT in OpenEMR for PID:'.$_SESSION["pid"];
//define the message to be sent. Each line should be separated with \n
$message = "There is a new EVENT encounter in OpenEMR.  Check your messages in the EMR for details.";
//define the headers we want passed. Note that they are separated with \r\n
$headers = "From: person1@oemr.org\r\nReply-To:person2@oemr.org";
//send the email
$mail_sent = mail( $to, $subject, $message, $headers );
		

$file = 'test.txt';
// Open the file to get existing  content
$current = file_get_contents($file);
// Append a new message to the file content string
$current .="\n  MESSAGE:". "\n".$to."\n".$subject."\n".$message."\n".$headers."\n"."SUPERVISOR:".$superemail."\n";

// Write the contents back to the file
file_put_contents($file, $current);
 		
 			
 						} 
 						*/?>