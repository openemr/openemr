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


require_once ("verify_session.php");
$_SESSION ['whereto'] = 'profilepanel';

require_once ("$srcdir/acl.inc");
require_once ("$srcdir/patient.inc");
require_once ("$srcdir/options.inc.php");
require_once ("$srcdir/classes/Document.class.php");
require_once ("./lib/portal_pnotes.inc"); // rem to fix pnotes pd.id in mainline codebase

$docid = empty ( $_REQUEST ['docid'] ) ? 0 : intval ( $_REQUEST ['docid'] );
$orderid = empty ( $_REQUEST ['orderid'] ) ? 0 : intval ( $_REQUEST ['orderid'] );
//$result = getPnotesByDate ( "", 1, "id,date,body,user,title,assigned_to,message_status", $pid, "all", 0, '', $docid, '', $orderid );
//$result = getPortalPatientNotes($pid);

$result = getMails($pid,'inbox','','');
$arr = array ();
foreach ( $result as $iter ) {
	$arr [] = $iter;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="" />
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

<style type="text/css">
/* CSS here to override bootstrap.css */
</style>
</head>

<body style='background:#f1f2f7;'>
	<ng ng-app="emrMessageApp">
	<div class="container">
		<div class='header logo'>
		<h2><img style='width:25%;height:auto;' class='logo' src='./images/logo-full-con.png'/>  <?php echo xlt('Patient Messaging'); ?></h2>
		</div>
		<!-- -->
		<div class="row" ng-controller="inboxCtrl">
			<aside class="col-md-2 pad-right-0">
				<ul class="nav nav-pills nav-stacked" >
					<li data-toggle="pill" class="active bg-info"><a href="javascript:;"
						ng-click="isInboxSelected()"><span class="badge pull-right">{{inboxItems.length}}</span>
							<?php echo xlt('Inbox'); ?> </a></li>
					<li data-toggle="pill" class="bg-info"><a href="javascript:;" ng-click="isAllSelected()"><span
							class="badge pull-right">{{allItems.length}}</span><?php echo xlt('All Mail'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="javascript:;" ng-click="isSentSelected()"><span
							class="badge pull-right">{{sentItems.length}}</span><?php echo xlt('Sent Mail'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="#"><span class="badge pull-right">0</span><?php echo xlt('Drafts'); ?></a></li>
					<li data-toggle="pill" class="bg-info"><a href="#"><span class="badge pull-right">0</span><?php echo xlt('Deleted'); ?></a></li>
					<li data-toggle="pill" class="bg-danger"><a href="javascript:;"
									onclick='window.location.replace("./home.php")'><?php echo xlt('Exit Mail'); ?></a></li>
				</ul>
			</aside>
			<div class="col-md-10">
				<!--inbox toolbar-->
				<div class="row" ng-show="!isMessageSelected()">
					<div class="col-xs-12">
						<a class="btn btn-default btn-lg" data-toggle="tooltip"
							title="Refresh" id="refreshInbox" href="javascript:;"
							onclick='window.location.replace("./messages.php")'> <span
							class="fa fa-refresh fa-lg"></span>
						</a>
						<button class="btn btn-default btn-lg" title="New Note"
							data-mode="add" data-toggle="modal" data-target="#modalCompose">
							<span class="fa fa-edit fa-lg"></span>
						</button>
						<div class="btn-group btn-group pull-right">
							<button type="button" class="btn btn-primary dropdown-toggle"
								data-toggle="dropdown">
								<?php echo xlt('Actions'); ?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li><a href="javascript:;" ng-click="readAll()"><?php echo xlt('Mark all as read'); ?></a></li>
								<li class="divider"></li>
								<li><a href="" data-mode="add" data-toggle="modal"
									data-target="#modalCompose"><?php echo xlt('Compose new'); ?></a></li>
								<li><a href="javascript:;"
									onclick='window.location.replace("./home.php")'
									class="text-muted"><?php echo xlt('Return Home'); ?></a></li>
							</ul>
						</div>
					</div>
					<!--/col-->
					<div class="col-xs-12 spacer5"></div>
				</div>
				<!--/row-->
				<!--/inbox toolbar-->
				<div class="panel panel-default inbox" id="inboxPanel">
					<!--message list-->
					<div class="table-responsive" ng-show="!isMessageSelected()">
						<table
							class="table table-striped table-hover refresh-container pull-down">
							<thead class="bg-info hidden-xs">
								<tr>
									<td class="col-sm-1"><a href="javascript:;"><strong><?php echo xlt('Id'); ?></strong></a></td>
									<td class="col-sm-1"><a href="javascript:;"><strong><?php echo xlt('Status'); ?></strong></a></td>
									<td class="col-sm-1"><a href="javascript:;"><strong><?php echo xlt('When'); ?></strong></a></td>
									<td class="col-sm-2"><a href="javascript:;"><strong><?php echo xlt('From'); ?></strong></a></td>
									<td class="col-sm-1"><a href="javascript:;"><strong><?php echo xlt('Titled'); ?></strong></a></td>
									<td class="col-sm-6"><a href="javascript:;"><strong><?php echo xlt('Messages'); ?></strong></a></td>
									<!-- <td class="col-sm-1"></td> -->
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="item in pagedItems[currentPage]">
									<!--  | orderBy:sortingOrder:reverse"> -->
									<td class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.id}}</span></td>
									<td class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.message_status}}</span></td>
									<td class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.date | date:'yyyy-MM-dd
											hh:mm'}}</span></td>
									<td class="col-sm-2 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.user}}</span></td>
									<td class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.title}}</span></td>
									<td class="col-sm-6 " ng-click="readMessage($index)"><span
										ng-class="{strong: !item.read}">{{item.body}}</span></td>
									<!-- <td class="col-sm-1 " ng-click="readMessage($index)"><span
										ng-show="item.attachment"
										class="glyphicon glyphicon-paperclip pull-right"></span> <span
										ng-show="item.priority==1"
										class="pull-right glyphicon glyphicon-warning-sign text-danger"></span></td> -->
								</tr>
							</tbody>
						</table>
					</div>
					<!--message detail-->
					<div class="container-fluid" ng-show="isMessageSelected()">
						<div class="row" ng-controller="messageCtrl">
							<div class="col-xs-12">
								<h3 title="subject">
									<button type="button" class="btn btn-danger btn-sm pull-right"
										ng-click="closeMessage()"><?php echo xlt('Inbox'); ?></button>
									<a href="javascript:;" ng-click="groupToPages()"><?php echo xlt('Inbox'); ?></a> &gt;
									{{selected.title}}
								</h3>
							</div>
							<div class="col-md-8">
								<blockquote class="bg-info small">
									<span>Message id:{{selected.id}} From:</span> <strong>{{selected.user}}</strong>
									&lt;{{selected.title}}&gt; on {{selected.date | date:'yyyy-MM-dd hh:mm'}}
								</blockquote>
							</div>
							<div class="col-md-4">
								<div class="btn-group btn-group pull-right">
									<button class="btn btn-primary" title="Reply to this message"
										data-toggle="modal" data-mode="reply"
										data-whoto={{selected.user}} data-mtitle={{selected.title}}
										data-target="#modalCompose">
										<i class="fa fa-reply"></i> <?php echo xlt('Reply'); ?>
									</button>
									<button class="btn btn-primary dropdown-toggle"
										data-toggle="dropdown" title="More options">
										<i class="fa fa-angle-down"></i>
									</button>
									<ul class="dropdown-menu pull-right">
										<li><a href="javascript:;"><i class="fa fa-reply"></i> <?php echo xlt('Reply'); ?></a></li>
										<li><a href="javascript:;"><i class="fa fa-mail-forward"></i>
												<?php echo xlt('Forward'); ?></a></li>
										<li><a href="javascript:;"><i class="fa fa-print"></i> <?php echo xlt('Print'); ?></a></li>
										<li class="divider"></li>
										<li><a href="javascript:;"><i class="fa fa-trash-o"></i> <?php echo xlt('Send
												to Trash'); ?></a></li>
									</ul>
								</div>
								<div class="spacer5 pull-right"></div>
								<button class="btn btn-md btn-primary pull-right"
									ng-click="deleteItem(selected.$index)"
									title="Delete this message" data-toggle="tooltip">
									<i class="fa fa-trash-o fa-1x"></i>
								</button>
							</div>
							<!-- <div class="col-xs-12">
								<hr>
							</div> -->
							<div class="col-xs-12">

									<table
										class="table table-striped table-hover refresh-container pull-down">
										<thead><p class=' col-sm-12 bg-info' style='font-size:16px' ng-bind-html=renderMessageBody(selected.body)></p></thead>
										<tbody>
											<tr
												ng-repeat="item in allItems | filter: { portal_relation: selected.id } | orderBy:sortingOrder:reverse"">
												<td class="col-sm-1 " ng-click="readMessage($index)"><span
													ng-class="{strong: !item.read}">{{item.id}}</span></td>
												<td class="col-sm-1 col-xs-4" ng-click="readMessage($index)"><span
													ng-class="{strong: !item.read}">{{item.message_status}}</span></td>
												<td class="col-sm-3 col-xs-4" ng-click="readMessage($index)"><span
													ng-class="{strong: !item.read}">{{item.date |
														date:'yyyy-MM-dd hh:mm'}}</span></td>
												<td class="col-sm-2 col-xs-4" ng-click="readMessage($index)"><span
													ng-class="{strong: !item.read}">{{item.title}}</span></td>
												<td class="col-sm-4 col-xs-6" ng-click="readMessage($index)"><span
													ng-class="{strong: !item.read}">{{item.body}}</span></td>
											</tr>
										</tbody>
									</table>

							</div>
							<!--/message body-->
						</div>
						<!--/row-->
					</div>
				</div>
				<!--/inbox panel-->
				<div class="well well-s text-right">
					<em>Inbox last updated: <span id="lastUpdated">{{date |
							date:'MM-dd-yyyy HH:mm:ss'}}</span></em>
				</div>
				<!--paging-->
				<div class="pull-right">
					<span class="text-muted"><b>{{(itemsPerPage * currentPage) + 1}}</b>–<b>{{(itemsPerPage
							* currentPage) + pagedItems[currentPage].length}}</b> of <b>{{items.length}}</b></span>
					<div class="btn-group btn-group">
						<button type="button" class="btn btn-default btn-lg"
							ng-class="{disabled: currentPage == 0}" ng-click="prevPage()">
							<span class="glyphicon glyphicon-chevron-left"></span>
						</button>
						<button type="button" class="btn btn-default btn-lg"
							ng-class="{disabled: currentPage == pagedItems.length - 1}"
							ng-click="nextPage()">
							<span class="glyphicon glyphicon-chevron-right"></span>
						</button>
					</div>
				</div>
				<hr>
			</div>

			<!-- /.modal compose message -->
			<div class="modal fade" id="modalCompose">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-hidden="true">×</button>
							<h4 class="modal-title"><?php echo xlt('Compose Message'); ?></h4>
						</div>
						<div class="modal-body">
							<form name='pnotesend' id='pnotesend' role="form"
								action="./handle_note.php" method="POST" class="form-horizontal">
								<!--  -->
								<div class="form-group">
									<label class="col-sm-2" for="selSendto"><?php echo xlt('To'); ?></label>
									<div class="col-sm-10">
										<select class="form-control" id="selSendto" name="selSendto">
											<option selected="selected" value="<?php echo($_SESSION['providerUName'])?>"><?php echo $_SESSION['providerName'];?></option>
											<option>Admin</option>
											<option>Nursing</option>
											<option>Billing</option>
											<option></option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2" for="title"><?php echo xlt('Subject'); ?></label>
									<div class="col-sm-10">
										<select type='text' name='title' id='title'
											class="form-control"> <!--  replace below with user fillable subject -->
											<option><?php echo xlt('Unassigned'); ?></option>
											<option><?php echo xlt('Insurance'); ?></option>
											<option><?php echo xlt('Prior Auth'); ?></option>
											<option><?php echo xlt('Bill/Collect'); ?></option>
											<option><?php echo xlt('Referral'); ?></option>
											<option><?php echo xlt('Other'); ?></option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-12" for="inputBody"><?php echo xlt('Message'); ?></label>
									<div class="col-sm-12">
										<textarea class="form-control" name="inputBody" id="inputBody"
											rows="10"></textarea>
									</div>
								</div>
								<input type='hidden' name='noteid' id='noteid'
									value={{selected.id}}> <input type='hidden' name='sendto'
									id='sendto' value=''> <input type='hidden' name='task'
									id='task' value=''>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default pull-left"
								data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>

							<button type="button" id="submitForm" class="btn btn-primary ">
								<?php echo xlt('Send'); ?> <i class="fa fa-arrow-circle-right fa-lg"></i>
							</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal compose message -->
			<div>
				<!--/row ng-controller-->
			</div>
			<!--/container-->
		</div>
	</div>
	</ng>

	<script type='text/javascript'>var messages = <?php echo json_encode($arr);?>;</script>

	<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>
	<script type='text/javascript' src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-1-5-8/angular.min.js"></script>
	<script type='text/javascript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/angular-sanitize-1-5-8/angular-sanitize.min.js"></script>
	<script type='text/javascript' src="assets/js/msgapp.js"></script>

</body>
</html>