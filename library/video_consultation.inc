<?php
/**
 * library/video_consultation.inc Functions for BigBlueButton integration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Toam Jose <pithonsoft@gmail.com>
 * @copyright Copyright (c) 2020 pithonsoft@gmail.com
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 
require_once($GLOBALS['srcdir'].'/patient.inc');
function BBB_duplicateCheck($meetingID){
  $res=sqlQuery('select count(*) as cnt from video where meeting_id=?',array($meetingID));
  return $res['cnt'];
}
function BBB_getCurrentMeetingStatus($pc_eid){
  $tmp=sqlQuery("select * from openemr_postcalendar_events pc left join video v on 
                 pc.pc_video_table_id=v.id  where pc.pc_eid=?",array($pc_eid));
  return $tmp;  
  }
function BBB_getRecurringMeetingDetails($parentID){
  $tmp=sqlQuery("select pc_video_table_id from openemr_postcalendar_events where pc_eid=?",array($parentID));
    $tmp2=0;
    if($tmp['pc_video_table_id']>0){
      $tmp2=sqlQuery("select * from video where id=?",array($tmp['pc_video_table_id']));
    }
return $tmp2;    
  }
function BBB_createMeetingID(){
  $tmp=0;
  while($tmp<3){
    $tmpMeetingID=rand(1000000000,9000000000);
    $cnt=BBB_duplicateCheck($tmpMeetingID);
    $meetingID=0;
    if($cnt==0){
      $meetingID=$tmpMeetingID;
      break;
      }
     $tmp++; 
     }
    return $meetingID;
}
function BBB_createMeeting($pc_eid){
  $create=true;
  $provider_pwd=rand(100000,999999);
  $patient_pwd=rand(100000,999999);
     $currStat = BBB_getCurrentMeetingStatus($pc_eid);
     $pid=$currStat['pc_aid'];
     $provider_id=$currStat['pc_pid'];
     if($currStat['meeting_status']=='scheduledCreated'){
      $md['status']='Meeting_Already_Scheduled';
       return $md; 
     }
     elseif($currStat['pc_parent_eid']=='0' && $currStat['pc_recurrtype'] >0 && $currStat['meeting_status']=='scheduledNotCreated'){//recurring event meetinfg details already exist
        $md['status']='Meeting_Already_Exist_for_the_Event';
       return $md;
     }
     elseif($currStat['pc_parent_eid'] > 0 && $currStat['pc_recurrtype'] ==0 && $currStat['meeting_status']==''){//child of recurring event
        $recurParent=BBB_getCurrentMeetingStatus($currStat['pc_parent_eid']);
        $meetingID=$recurParent['meeting_id'];
      $provider_pwd=$recurParent['moderator_pwd'];
      $patient_pwd=$recurParent['attendee_pwd'];
     }
     elseif($currStat['pc_recurrtype']=='0' && $currStat['meeting_status']=='scheduledNotCreated'){ 
      $meetingID=$currStat['meeting_id'];
      $provider_pwd=$currStat['moderator_pwd'];
      $patient_pwd=$currStat['attendee_pwd'];
      $editMode=1;
     }
    if(!$meetingID){
      $meetingID=BBB_createMeetingID();
    }
    if(!$meetingID){
      $md['status']='FAILED';
      $md['reason']='Duplicate Check Failed';
      return $md;
    }
  $urlString='meetingID='.$meetingID."&moderatorPW=".$provider_pwd."&attendeePW=".$patient_pwd;
  $provName=getProviderName($provider_id);
  $provName=str_replace(" ","+",$provName);
  $patName=getPatientData($pid);
  $patName=$patName['lname'].",".$patName['fname'];
  $provider_url="meetingID=".$meetingID."&password=".$provider_pwd."&fullName=".$provName."&joinViaHtml5=true";
  $patient_url="meetingID=".$meetingID."&password=".$patient_pwd."&fullName=".$patName."&joinViaHtml5=true";
  if($recurrspec=$currStat['pc_recurrtype']){ //if recurring meeting return will be greater than zero
  $create=false;
  $meeting_status='scheduledNotCreated';
  }
    if($create==true){
      $result=BBB_serverCall($urlString,'create');
        if($result=='err'){//if the creation fails because of curl errors disregard this. Try to creating this when Meeting Starts
          $meeting_status='scheduledNotCreated';
        }
        else{
        $result=simplexml_load_string($result);
        }
        }
       elseif($create==false){
        $meeting_status='scheduledNotCreated';
       }
  if($result->returncode=='SUCCESS'){
  $meeting_status='scheduledCreated';
  }
  if($editMode<>1){
  $q='insert into video(vendor_id,provider_id, patient_id,meeting_id, meeting_status,provider_url,patient_url,is_recurring,moderator_pwd,attendee_pwd) values(?,?,?,?,?,?,?,?,?,?)';  
  $tmpArr=array('BBB',$provider_id,$pid,$meetingID,$meeting_status,$provider_url,$patient_url,$recurrspec,$provider_pwd,$patient_pwd);
  $md['insertId']=sqlInsert($q,$tmpArr);
  $q="update openemr_postcalendar_events set pc_video_meeting_id=?,pc_video_table_id=? where pc_eid=?";
  $arTmp=array($meetingID,$md['insertId'],$pc_eid);
	SqlStatement($q,$arTmp);
  }
  elseif($editMode==1){
    $q=sqlStatement("update video set meeting_status=? where id=?",array($meeting_status,$currStat['pc_video_table_id']));
  }
  $md['meetingId']=$meetingID;
  $md['meeting_status']=$meeting_status;
  return $md;
}
function BBB_startMeeting($pc_eid){
  $currStat = BBB_getCurrentMeetingStatus($pc_eid);
  if($currStat['meeting_status']=='scheduledNotCreated'){
    $md=BBB_createMeeting($pc_eid);
    if($md['meeting_status']=='scheduledCreated'){
      $currStat=BBB_getCurrentMeetingStatus($pc_eid);
    }
  }
  if($currStat['meeting_status']=='scheduledCreated'){
    $urlString=$currStat['provider_url'];
    $arr=BBB_getCredentials();
    if($arr['bbb_video_username']){
      $tmp=$urlString."&userName=".$arr['bbb_video_username'];
    }
    if($arr['bbb_video_pwd']){
      $tmp=$urlString."&userPwd=".$arr['bbb_video_pwd'];
    }
    $tmp=$urlString.$arr['bbb_video_secret'];
    $checksum=sha1("join".$tmp);
    $url=$arr['bbb_video_url']."api/join".'?'.$urlString.'&checksum='.$checksum;
    $q="update video set meeting_status=? where id=?";
    sqlStatement($q,array('started',$calRes['pc_video_table_id']));
    return $url;
  }
  return false;
}
function BBB_playRecording(){





}
function BBB_getMeetingInfo($meetingID=''){
if($meetingID){
 
}
else{
	$q="select id,meeting_id from video where meeting_status='started'";
	$md=sqlStatement($q);
	while ($r=sqlFetchArray($md)){
		$url="meetingID=".$r['meeting_id'];
		$xml=BBB_serverCall($url,'getMeetingInfo');
		$result=simplexml_load_string($result);
		if($result->running==false){
		$q=sqlStatement("update video set meeting_info=? where id=?",array($xml,$r['id']));
		}

	} 
}
}
function BBB_getCredentials(){
$q = sqlStatement("SELECT * from globals where gl_name like 'BBB%'" );
      while ($current = sqlFetchArray($q)){
        if($current['gl_name']=='bbb_video_pwd'){
          $arr['bbb_video_pwd'] = $current['gl_value'];
        }
        elseif($current['gl_name']=='bbb_video_secret'){
          $arr['bbb_video_secret'] = $current['gl_value'];
        }
        elseif($current['gl_name']=='bbb_video_username'){
           $arr['bbb_video_username'] = $current['gl_value'];
        }
        elseif($current['gl_name']=='bbb_video_url'){
           $arr['bbb_video_url'] = $current['gl_value'];
        }
		}
		return $arr;
		}
function BBB_serverCall($url,$action){
  $arr=BBB_getCredentials();
  if($arr['bbb_video_username']){
      $tmp=$url."&userName=".$arr['bbb_video_username'];
    }
    if($arr['bbb_video_pwd']){
      $tmp=$url."&userPwd=".$arr['bbb_video_pwd'];
    }
  $encodedUrl=$url.$arr['bbb_video_secret'];
  $checksum=sha1($action.$encodedUrl);
  $url=$action.'?'.$url.'&checksum='.$checksum;
  $ch = curl_init();
  $url = $arr['bbb_video_url'].'api/'. $url;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, 0); // set true for look see
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch,CURLOPT_COOKIESESSION, true);
  curl_setopt($ch,CURLOPT_COOKIEJAR,'cookie');
  curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $result = curl_exec($ch);
    if(curl_errno($ch)){
      curl_close($ch);
      return "err";
    }
    curl_close($ch);
  return $result;
  
}
function getMeetingDetails($eid)
{
$q="select pc_eventDate,pc_startTime,patient_url from openemr_postcalendar_events op
      left join video v on op.pc_video_table_id=v.id where pc_eid=?";
	 $res=sqlQuery($q,array($eid));
     if($res['patient_url'])
	 {
	   $bbbcred=BBB_getCredentials();
	   $urlString=$res['patient_url'].$bbbcred['bbb_video_secret'];
		$checksum=sha1("join".$urlString);
		$url=$bbbcred['bbb_video_url']."api/join".'?'.$res['patient_url'].'&checksum='.$checksum;
	 }
	 $det="Appointment Date: ".$res['pc_eventDate']."\nAppointment Time: ".$res['pc_startTime']."\n";
	 if($url){
	 $det .="Meeting Method :Video Consultation\nMeeting URL: ".$url;
	 }
	 return $det;
}
?>