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
//
// +------------------------------------------------------------------------------+



include_once('../../interface/globals.php');
$sql = "select distinct tu_user_id from template_users";
$rs = SqlStatement($sql);
while ($row = SqlFetchArray($rs)) {
    $sql = "select * from template_users join customlists on cl_list_slno=tu_template_id where
 cl_deleted=0 and tu_user_id=?";
    $rs2 = SqlStatement($sql, array($row['tu_user_id']));
    while ($row2 = SqlFetchArray($rs2)) {
        $sql = "select cl_list_slno from customlists where cl_deleted=0 and cl_list_id=?";
        $rs3 = SqlStatement($sql, array($row2['cl_list_slno']));
        while ($row3 = SqlFetchArray($rs3)) {
            SqlStatement("insert into template_users (tu_template_id,tu_user_id) values(?,?)", array($row3['cl_list_slno'],$row['tu_user_id']));
        }
    }
}
