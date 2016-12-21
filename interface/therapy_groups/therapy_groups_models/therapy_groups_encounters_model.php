<?php
/**
 * interface/therapy_groups/therapy_groups_models/therapy_groups_encounters_model.php contains the model for therapy group encounters.
 *
 * This model fetches the encounters for the therapy group from the DB.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shachar058@gmail.com>
 * Copyright (C) 2016 Amiel Elboim <amielboim@gmail.com>
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
 * @author  Shachar Zilbershlag <shachar058@gmail.com>
 * @author  Amiel Elboim <amielboim@gmail.com>
 * @link    http://www.open-emr.org
 */

 class Therapy_Groups_Encounters{

     const TABLE = 'form_groups_encounter';

     /**
      * Get all encounters of specified group.
      * @param $gid
      * @return ADORecordSet_mysqli
      */
     public function getGroupEncounters($gid){
         $sql = "SELECT * FROM " . SELF::TABLE . " WHERE group_id = ? AND date >= CURDATE();";
         $result = sqlStatement($sql, array($gid));
         while($row = sqlFetchArray($result)){
             $encounters[] = $row;
         }
         return $encounters;
     }



 }
