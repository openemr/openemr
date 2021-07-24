<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
function rom_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_rom", $id);
    if ($data) {
        ?>

  <table border='1' bordercolor='#000000' cellpadding='7' cellspacing='0'  class='text'>
        <col>
        <col>
        <col>
        <col>
        <tbody>

        <tr valign='top'>
            <td colspan='4'>
                <p align='left'><b>Neck</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>ACTIVE</b></p>
            </td>
            <td>
                <p align='center'><b>PASSIVE</b></p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-50°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_1_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_1_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Extension</p>
            </td>
            <td>
                <p align='center'><b>0-60°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_2_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_2_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Right Lateral Bending</p>
            </td>
            <td>
                <p align='center'><b>0-45°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_3_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_3_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Left Lateral Bending</p>
            </td>
            <td>
                <p align='center'><b>0-45°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_4_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_4_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Right Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-80°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_5_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_5_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Left Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-80°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_6_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_6_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='4'>
                <p align='left'><b>Back</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>ACTIVE</b></p>
            </td>
            <td>
                <p align='center'><b>PASSIVE</b></p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-90°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_7_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_7_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Extension</p>
            </td>
            <td>
                <p align='center'><b>0-25°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_8_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_8_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Right Lateral Bending</p>
            </td>
            <td>
                <p align='center'><b>0-25°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_9_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_9_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Left Lateral Bending</p>
            </td>
            <td>
                <p align='center'><b>0-25°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_10_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_10_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Right Lateral Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-30°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_11_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_11_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td>
                <p align='center'>Left Lateral Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-30°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_12_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r1_12_passive']); ?>&nbsp;</p>
            </td>
        </tr>
</tbody></table>
</p>
    <table border='1' bordercolor='#000000' cellpadding='7' cellspacing='0'  class='text'>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
<tr valign='top'>
            <td>
                <p align='center'>Straight Leg Raising (supine)</p>
            </td>
            <td>
                <p align='center'><b>0-90°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_1_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_1_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_1_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_1_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Straight Leg Raising (sitting)</p>
            </td>
            <td>
                <p align='center'><b>0-90°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_2_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_2_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_2_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_2_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Shoulder</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Abduction</p>
            </td>
            <td>
                <p align='center'><b>0-150°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_3_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_3_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_3_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_3_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Forward Elevation</p>
            </td>
            <td>
                <p align='center'><b>0-150°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_4_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_4_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_4_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_4_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Internal Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-80°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_5_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_5_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_5_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_5_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>External Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-90°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_6_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_6_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_6_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r2_6_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
</tbody></table>
</p>
    <table border='1' bordercolor='#000000' cellpadding='7' cellspacing='0'  class='text'>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Elbow and Forearm</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-150°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_1_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_1_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_1_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_1_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Extension</p>
            </td>
            <td>
                <p align='center'><b>0°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_2_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_2_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_2_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_2_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Supination</p>
            </td>
            <td>
                <p align='center'><b>0-80°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_3_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_3_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_3_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_3_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Pronation</p>
            </td>
            <td>
                <p align='center'><b>0-80°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_4_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_4_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_4_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_4_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Wrist</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Dorsiflexion</p>
            </td>
            <td>
                <p align='center'><b>0-60°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_5_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_5_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_5_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_5_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Palmar Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-60°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_6_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_6_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_6_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_6_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Radial Deviation</p>
            </td>
            <td>
                <p align='center'><b>0-20°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_7_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_7_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_7_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_7_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Ulnar Deviation</p>
            </td>
            <td>
                <p align='center'><b>0-30°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_8_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_8_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_8_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_8_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Hip</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Abduction</p>
            </td>
            <td>
                <p align='center'><b>0-40°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_9_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_9_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_9_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_9_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Adduction</p>
            </td>
            <td>
                <p align='center'><b>0-20°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_10_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_10_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_10_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_10_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-100°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_11_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_11_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_11_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_11_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Extension</p>
            </td>
            <td>
                <p align='center'><b>0-30°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_12_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_12_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_12_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_12_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Internal Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-40°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_13_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_13_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_13_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_13_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>External Rotation</p>
            </td>
            <td>
                <p align='center'><b>0-50°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_14_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_14_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_14_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_14_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Knee</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-150°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_15_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_15_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_15_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_15_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Extension</p>
            </td>
            <td>
                <p align='center'><b>0°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_16_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_16_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_16_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_16_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Ankle and Foot</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Dorsiflexion</p>
            </td>
            <td>
                <p align='center'><b>0-20°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_17_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_17_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_17_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_17_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Plantar Flexion</p>
            </td>
            <td>
                <p align='center'><b>0-40°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_18_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_18_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_18_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_18_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Inversion</p>
            </td>
            <td>
                <p align='center'><b>0-30°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_19_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_19_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_19_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_19_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>Eversion</p>
            </td>
            <td>
                <p align='center'><b>0-20°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_20_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_20_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_20_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r3_20_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
</tbody></table>
</p>
    <table border='1' bordercolor='#000000' cellpadding='7' cellspacing='0'  class='text'>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>

        <tr valign='top'>
            <td colspan='6'>
                <p align='left'><b>Hands and Fingers</b></p>
            </td>
        </tr>
        <tr valign='top'>
            <td>
                <p align='center'>&nbsp;</b></p>
            </td>
            <td>
                <p align='center'><b>NORMAL</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(R) Passive</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Active</b></p>
            </td>
            <td>
                <p align='center'><b>(L) Passive</b></p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>MCP Flexion thumb</p>
            </td>
            <td>
                <p align='center'><b>0-90°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_1_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_1_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_1_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_1_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>MCP flexion rest fingers</p>
            </td>
            <td>
                <p align='center'><b>0-90</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_2_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_2_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_2_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_2_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>MCP extension</p>
            </td>
            <td>
                <p align='center'><b>0°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_3_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_3_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_3_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_3_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>PIP Flexion all fingers</p>
            </td>
            <td>
                <p align='center'><b>0-100°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_4_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_4_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_4_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_4_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>PIP extension</p>
            </td>
            <td>
                <p align='center'><b>0°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_5_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_5_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_5_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_5_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>DIP flexion</p>
            </td>
            <td>
                <p align='center'><b>0-70°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_6_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_6_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_6_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_6_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
<tr valign='top'>
            <td>
                <p align='center'>DIP extension</p>
            </td>
            <td>
                <p align='center'><b>0°</b></p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_7_rt_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_7_rt_passive']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_7_lf_active']); ?>&nbsp;</p>
            </td>
            <td>
                <p align='center'><?php echo text($data['r4_7_lf_passive']); ?>&nbsp;</p>
            </td>
        </tr>
</tbody></table>
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
