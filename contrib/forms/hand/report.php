<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
function hand_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $cols = 2;
    $data = formFetch("form_hand", $id);
    $width = 100 / $cols;
    if ($data) {
        ?>

      <table class='text' border='0px' cellpadding='2px' cellspacing='0px'>
        <tr>
            <td width='80px'><b>&nbsp;</td>
            <td width='80px'><b>1st</b></td>
            <td width='80px'><b>2nd</b></td>
            <td width='80px'><b>3rd</b></td>
        </tr>
        <tr>
            <td>(L) Hand</td>
            <td><?php echo $data['left_1'] ? text($data['left_1']) . " fp" : "-"; ?></td>
            <td><?php echo $data['left_2'] ? text($data['left_2']) . " fp" : "-"; ?></td>
            <td><?php echo $data['left_3'] ? text($data['left_3']) . " fp" : "-"; ?></td>
        </tr>
        <tr>
            <td>(R) Hand</td>
            <td><?php echo $data['right_1'] ? text($data['right_1']) . " fp" : "-"; ?></td>
            <td><?php echo $data['right_2'] ? text($data['right_2']) . " fp" : "-"; ?></td>
            <td><?php echo $data['right_3'] ? text($data['right_3']) . " fp" : "-"; ?></td>
        </tr>
        <tr>
            <td>Handedness:</td>
            <td colspan='3'><?php echo text($data['handedness']); ?></td>
        </tr>

    </table>

        <?php if ($data['notes'] != '') {?>
    </p>
    <table border='0' cellpadding='0' cellspacing='0' class='text'>
        <tr class='text'>
            <td><b>NOTES</b></td>
        </tr>
        <tr class='text'>
            <td><p align='left'><?php echo text($data['notes']); ?>&nbsp;</p></td>
        </tr>
    </table>
        <?php } ?>

        <?php
    }
}
?>
