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
//           Jacob T Paul <jacob@zhservices.com>
//           Ajil P.M     <ajilpm@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

class UserMail {


  public function getMails($data){
    global $pid;
    if(UserService::valid($data[0])=='existingpatient'){
      require_once("../../library/pnotes.inc");
      if($data[2] == "inbox"){
        if($data[3] && $data[4]){
          $result_notes = getPatientNotes($pid,'','0',$data[3]);
          $result_notifications = getPatientNotifications($pid,'','0',$data[4]);
          $result = array_merge((array)$result_notes,(array)$result_notifications);
        }else{
          $result_notes = getPatientNotes($pid);
          $result_notifications = getPatientNotifications($pid);
          $result = array_merge((array)$result_notes,(array)$result_notifications);
        }
        return $result;
      }elseif($data[2] == "sent"){
        if($data[3]){
          $result_sent_notes = getPatientSentNotes($pid,'','0',$data[3]);
        }else{
          $result_sent_notes = getPatientSentNotes($pid);
        }
        return $result_sent_notes;
      }
    }else{
      throw new SoapFault("Server", "credentials failed");
    }
  }

  



  public function getMailDetails($data){
    if(UserService::valid($data[0])=='existingpatient'){
      require_once("../../library/pnotes.inc");
      $result = getPnoteById($data[1]);
      if($result['assigned_to'] == '-patient-' && $result['message_status'] == 'New'){
        updatePnoteMessageStatus($data[1],'Read');
      }
      return $result;
    }else{
      throw new SoapFault("Server", "credentials failed");
    }
  }

  



  public function sendMail($data){
	global $pid;
    if(UserService::valid($data[0])=='existingpatient'){
      require_once("../../library/pnotes.inc");
      $to_list = explode(';',$data[2]);
      foreach($to_list as $to){
        addMailboxPnote($pid,$data[4],'1','1',$data[3],$to);
      }
      return 1;
    }else{
      throw new SoapFault("Server", "credentials failed");
    }
  }

  



  public function updateStatus($data){
    if(UserService::valid($data[0])=='existingpatient'){
      require_once("../../library/pnotes.inc");
      foreach($data[1] as $id){
        updatePnoteMessageStatus($id,$data[2]);
      }
    }else{
      throw new SoapFault("Server", "credentials failed");
    }
  }
}
?>