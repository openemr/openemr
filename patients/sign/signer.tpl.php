<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
$sanitize_all_escapes=true;
$fake_register_globals=false;
?>

<div id="openModal" class="modal right fade" role="dialog">
    <div class="modal-dialog" >
        <div class="modal-content">
            <!-- <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Sign</h4>
            </div> -->
            <div class="modal-body clearfix" >

                <form name="signit" id="signit" class="sigPad" >
                    <input type="hidden" name="name" id="name" class="name">
                    <ul class="sigNav">
                        <label><input type="checkbox" class="" id="isAdmin"
                            name'="isAdmin" /><?php echo xlt('Is Examiner Signature'); ?></label>
                        <li class="clearButton"><a href="#clear"><button><?php echo xlt('Clear Signature'); ?></button></a></li>
                    </ul>
                    <div class="sig sigWrapper">
                        <div class="typed"></div>
                        <canvas class="pad" id="drawpad" width="765" height="325"
                            style="border: 3px solid #000000; left: 0px;"></canvas>
                        <img id="loading"
                            style="display: none; position: absolute; TOP: 240px; LEFT: 455px; WIDTH: 100px; HEIGHT: 100px"
                            src="sign/assets/loading.gif" /> <input type="hidden" id="output"
                            name="output" class="output">
                    </div>
                    <input type="hidden" name="type" id="type"
                        value="patient-signature">
                    <button type="button" onclick="signDoc(this)"><?php echo xlt('I accept the terms of    this agreement'); ?>.</button>
                </form>
                
            </div><!-- Modal body -->
            
        </div><!-- Modal content -->
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo xlt('Close'); ?></button>
        </div>
    </div><!-- Modal dialog -->
</div><!-- Modal -->

<img id="waitend"
    style="display: none; position: absolute; top: 100px; left: 360px; width: 100px; height: 100px"
    src="sign/assets/loading.gif" />
