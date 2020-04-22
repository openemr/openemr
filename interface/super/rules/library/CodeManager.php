<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of CodeManager
 *
 * @author aron
 */
class CodeManager
{
    const SQL_SELECT =
    "SELECT id,
            code,
            code_text,
            code_types.ct_key as code_type
       FROM codes JOIN code_types on codes.code_type = code_types.ct_id";

    const SQL_WHERE_SEARCH =
    "WHERE id LIKE ? OR code_text LIKE ? OR code_text_short LIKE ? OR code LIKE ? OR code_types.ct_key LIKE ?";

    const SQL_WHERE_GET =
    "WHERE id = ?";


    function __construct()
    {
    }

    /**
     * Returns an array of Code
     * @param string $searchTerm
     */
    function search($searchTerm)
    {
        $stmt = sqlStatement(
            self::SQL_SELECT . " " . self::SQL_WHERE_SEARCH,
            array( "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%" )
        );

        $codes = array();

        for ($iter = 0; $row = sqlFetchArray($stmt); $iter++) {
            $code = new Code($row['id'], $row['code'], $row['code_text'], $row['code_type']);
            array_push($codes, $code);
        }

        return $codes;
    }

    /**
     * @return Code
     */
    function get($id)
    {
        $row = sqlQuery(self::SQL_SELECT . " " . self::SQL_WHERE_GET, array( $id ));
        if (!$row) {
            return null;
        }

        $code = new Code($row['id'], $row['code'], $row['code_text'], $row['code_type']);
        return $code;
    }
}
