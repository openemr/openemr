<?php

class Therapy_groups_participants{

    const TABLE = 'therapy_groups_participants';
    const PATIENT_TABLE = 'patient_data';

    public function getParticipants($groupId){

        $sql = "SELECT gp.*, p.fname, p.lname FROM " . self::TABLE . " AS gp ";
        $sql .= "JOIN " . self::PATIENT_TABLE . " AS p ON gp.pid = p.id ";
        $sql .= "WHERE gp.group_id = ?";

        $groupParticipants = array();
        $result = sqlStatement($sql, array($groupId));
        while($gp = sqlFetchArray($result)){
            $groupParticipants[] = $gp;
        }
        return $groupParticipants;
    }

    public function updateParticipant(array $participant, $patientId ,$groupId){

        if(empty($participant['group_patient_end'])){
            $participant['group_patient_end'] = NULL;
        }

        $sql = "UPDATE " . self::TABLE . " SET ";
        foreach($participant as $key => $value){
            $sql .= $key . '=?,';
        }
        $sql = substr($sql,0, -1);
        $sql .= ' WHERE pid = ? AND group_id = ?';

        $data = array_merge($participant, array($patientId, $groupId));
        $result = sqlStatement($sql, $data);
        return !$result ? false :true;
    }

    public function removeParticipant($groupId,$pid){

        $sql = "DELETE FROM " . self::TABLE . " WHERE group_id = ? AND pid = ?";
        $result = sqlStatement($sql, array($groupId, $pid));
        return !$result ? false :true;
    }

    public function isAlreadyRegistered($pid, $groupId){

        $sql = "SELECT COUNT(*) AS count FROM " . self::TABLE . " WHERE pid = ? AND group_id = ?";

        $result = sqlStatement($sql, array($pid, $groupId));
        $count = sqlFetchArray($result);
        return($count['count'] > 0) ? true : false;
    }

    public function saveParticipant($participantData){
           // print_r($participantData);die;
        $sql = "INSERT INTO " .self::TABLE . " VALUES(?,?,?,?,?,?);";
        $data[] = $participantData['group_id'];
        $data[] = $participantData['pid'];
        $data[] = $participantData['group_patient_status'];
        $data[] = $participantData['group_patient_start'];
        $data[] = $participantData['group_patient_end'];
        $data[] = $participantData['group_patient_comment'];

        $result = sqlStatement($sql, $data);

        return !$result ? false :true;
    }
}