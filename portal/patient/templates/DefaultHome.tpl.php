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

$this->assign('title', xlt('Patient Portal') . " | " . xlt('Home'));
$this->assign('nav', 'home');

$this->display('_Header.tpl.php');
?>
<div class="modal fade" id="formdialog" tabindex="-1" role="dialog"	aria-hidden="true">
    <div class="modal-dialog modal-lg" style="background:white">
        <div class="modal-content">
            <div class="modal-header">
                <!-- --><button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo xlt('About Your Portal'); ?></h4>
            </div>
        </div>
        <div class="modal-body">
            <div><span><?php echo xlt('Help content goes here'); ?></span></div>
        </div>
        <div class="modal-footer">
            <button id="okButton" data-dismiss="modal" class="btn btn-secondary"><?php echo xlt('Close'); ?>...</button>
        </div>
    </div>
</div>
<div class="container">
    <div class='well'>
    <div class="jumbotron">
        <h1>
            <?php echo xlt('Onsite Portal'); ?><i class="fa fa-user-md pull-right" style="font-size:60px;color:red"></i>
        </h1>
        <a class="btn btn-primary btn-lg" data-toggle="modal"
            data-target="#formdialog" href="#"><?php echo xlt('Tell me more'); ?> »</a>
    </div>
</div>
<div class='well'>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <h2>
                <i class="icon-cogs"></i> <?php echo xlt('Latest Health Alerts'); ?>
            </h2>
        </div>
        <div class="col-sm-3 col-md-3">
            <h2>
                <i class="icon-th"></i> <?php echo xlt('The Patients Rights'); ?>
            </h2>

        </div>
        <div class="col-sm-6 col-md-6">
            <h2>
                <i class="icon-signin"></i><?php echo xlt('Access Your Medical Records'); ?>
            </h2>
            <p></p>
            <p>
                <!-- <a class="btn btn-default" href="loginform">Sign In »</a> -->
                <a class="btn btn-default" href="../index.php"><?php echo xlt('Sign In'); ?> »</a>
            </p>
        </div>

    </div>
</div>
</div>
<!-- /container -->
<?php
$this->display('_Footer.tpl.php');
?>
