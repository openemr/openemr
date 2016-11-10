<?php

class Therapy_groups_participants{

    const TABLE = 'therapy_groups_participants';
    const PATIENT_TABLE = 'patient_data';

    public function getParticipants($groupId){

        $sql = "SELECT gd.*, p.fname, p.lname FROM " . self::TABLE . " AS gp ";
        $sql .= "JOIN " . self::PATIENT_TABLE . " AS p ON gp.pid = p.id ";
        $sql .= "WHERE gp.group_id = ?";

        $result = sqlStatement($sql, $groupId);
        while($gp = sqlFetchArray($result)){
            $groupParticipants[] = $gp;
        }
        return $groupParticipants;

    }
}