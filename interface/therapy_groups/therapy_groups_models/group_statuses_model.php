<?php

/**
 * interface/therapy_groups/therapy_groups_models/group_statuses_model.php contains the model for therapy group statuses.
 *
 * This model fetches the statuses list for therapy group appointments from the DB.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

class Group_Statuses
{
    const TABLE = 'list_options';

    /**
     * Gets group appointment statuses
     * @return ADORecordSet_mysqli
     */
    public function getGroupStatuses()
    {
        $sql = 'SELECT  option_id, title FROM ' . self::TABLE . ' WHERE list_id = ?;';
        $result = sqlStatement($sql, array('groupstat'));
        $final_result = array();
        while ($row = sqlFetchArray($result)) {
            $final_result[] = $row;
        }

        return $final_result;
    }

    /**
     * Gets group meeting attendance statuses
     * @return ADORecordSet_mysqli
     */
    public function getGroupAttendanceStatuses()
    {
        $sql = 'SELECT  option_id, title FROM ' . self::TABLE . ' WHERE list_id = ?;';
        $result = sqlStatement($sql, array('attendstat'));
        $final_result = array();
        while ($row = sqlFetchArray($result)) {
            $row['title'] = xla(trim($row['title']));
            $final_result[] = $row;
        }

        return $final_result;
    }
}
