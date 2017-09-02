<?php

function getTransById($id, $cols = "*")
{
    $row = sqlQuery("SELECT $cols FROM transactions WHERE id = ?", array($id));
    $fres = sqlStatement("SELECT field_id, field_value FROM lbt_data WHERE form_id = ?", array($id));
    while ($frow = sqlFetchArray($fres)) {
        $row[$frow['field_id']] = $frow['field_value'];
    }

    return $row;
}

function getTransByPid($pid, $cols = "*")
{
    $res = sqlStatement("select $cols from transactions where pid = ? " .
    "order by date DESC", array($pid));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $fres = sqlStatement(
            "SELECT field_id, field_value FROM lbt_data WHERE form_id = ?",
            array($row['id'])
        );
        while ($frow = sqlFetchArray($fres)) {
              $row[$frow['field_id']] = $frow['field_value'];
        }

        $all[$iter] = $row;
    }

    return $all;
}

function newTransaction(
    $pid,
    $body,
    $title,
    $authorized = "0",
    $status = "1",
    $assigned_to = "*"
) {

    $body = add_escape_custom($body);
    $id = sqlInsert("insert into transactions ( " .
    "date, title, pid, user, groupname, authorized " .
    ") values ( " .
    "NOW(), '$title', '$pid', '" . $_SESSION['authUser'] .
    "', '" . $_SESSION['authProvider'] . "', '$authorized' " .
    ")");
    sqlStatement(
        "INSERT INTO lbt_data (form_id, field_id, field_value) VALUES (?, ?, ?)",
        array($id, 'body', $body)
    );
    return $id;
}

function authorizeTransaction($id, $authorized = "1")
{
    sqlQuery("update transactions set authorized = '$authorized' where " .
    "id = '$id'");
}
