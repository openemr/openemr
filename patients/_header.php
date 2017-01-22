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
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo xlt('OpenEMR Portal'); ?> | <?php echo xlt('Home'); ?></title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="description" content="Developed By sjpadgett@gmail.com">

<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/style.css" rel="stylesheet" type="text/css" />
<link href="sign/css/signer.css" rel="stylesheet" type="text/css" />
<link href="sign/assets/signpad.css" rel="stylesheet">

<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js" type="text/javascript"></script>
<script src="assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="sign/assets/signpad.js" type="text/javascript"></script>
<script src="sign/assets/signer.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/emodal-1-2-65/dist/eModal.js"></script>

</head>
<body class="skin-blue fixed">
	<header class="header">
		<a href="home.php" class="logo"><img src='./images/logo-full-con.png'/></a>
		<nav class="navbar navbar-static-top" role="navigation">
			<!-- Sidebar toggle button-->
			<a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas"
				role="button"> <span class="sr-only"><?php echo xlt('Toggle navigation'); ?></span> <span
				class="icon-bar"></span> <span class="icon-bar"></span> <span
				class="icon-bar"></span>
			</a>
			<div class="navbar-right">
				<ul class="nav navbar-nav">
					<li class="dropdown messages-menu"><a href="#"
						class="dropdown-toggle" data-toggle="dropdown"> <i
							class="fa fa-envelope"></i> <span class="label label-success"> <?php echo text($newcnt); ?></span>
					</a>
						<ul class="dropdown-menu">
							<li class="header"><?php echo xlt('You have'); ?> <?php echo text($newcnt); ?> <?php echo xlt('new messages'); ?></li>
							<li>
								<!-- inner menu: contains the actual data -->
								<ul class="menu">
								<?php
								 foreach ( $msgs as $i ) {
								  	if($i['message_status']=='New'){
										echo "<li><a href='#'>
											<h4>" . text($i['title']) . "</h4>
											<p>" . text($i['body']) . "</p></a></li>";
									}
								}
								?>
								</ul>
							</li>
							<li class="footer"><a href="./messages.php"><?php echo xlt('See All Messages'); ?></a></li>
						</ul></li>

					<li class="dropdown user user-menu"><a href="#"
						class="dropdown-toggle" data-toggle="dropdown"> <i
							class="fa fa-user"></i> <span><?php echo text($result['fname']." ".$result['lname']); ?>
								<i class="caret"></i></span>
					</a>
						<ul class="dropdown-menu dropdown-custom dropdown-menu-right">
							<li class="dropdown-header text-center"><?php echo xlt('Account'); ?></li>
							<li><a href="./messages.php"> <i class="fa fa-envelope-o fa-fw pull-right"></i>
									<span class="badge badge-danger pull-right"> <?php echo text($msgcnt); ?></span> <?php echo xlt('Messages'); ?></a></li>
							<li class="divider"></li>
							<li><a href="./messaging/secure_chat.php?fullscreen=true"> <i class="fa fa-user fa-fw pull-right"></i><?php echo xlt('Chat'); ?></a>
								<a href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal"> <i
									class="fa fa-cog fa-fw pull-right"></i> <?php echo xlt('Settings'); ?></a></li>

							<li class="divider"></li>

							<li><a href="logout.php"><i class="fa fa-ban fa-fw pull-right"></i>
									<?php echo xlt('Logout'); ?></a></li>
						</ul></li>
				</ul>
			</div>
		</nav>
	</header>
	<div class="wrapper row-offcanvas row-offcanvas-left">
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="left-side sidebar-offcanvas">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- Sidebar user panel -->
				<div class="user-panel">
					<div class="pull-left image">
						<i class="fa fa-user"></i>
					</div>
					<div class="pull-left info">
						<p><?php echo xlt('Welcome') . ' ' . text($result['fname']." ".$result['lname']); ?></p>
						<a href="#"><i class="fa fa-circle text-success"></i> <?php echo xlt('Online'); ?></a>
					</div>
				</div>
				<ul class="nav  nav-pills nav-stacked" style='font-color:#fff;'><!-- css class was sidebar-menu -->
					<li data-toggle="pill"><a href="#profilepanel" data-toggle="collapse"
						data-parent="#panelgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt('Profile'); ?></span>
					</a></li>
					<li data-toggle="pill"><a href="#lists" data-toggle="collapse"
						data-parent="#panelgroup"> <i class="fa fa-list"></i> <span><?php echo xlt('Lists'); ?></span>
					</a></li>
					<li><a href="./patient/onsitedocuments?pid=<?php echo attr($pid); ?>"> <i class="fa fa-gavel"></i> <span><?php echo xlt('Patient Documents'); ?></span>
					</a></li>
					<li data-toggle="pill"><a href="#appointmentpanel" data-toggle="collapse"
						data-parent="#panelgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt("Appointment"); ?></span>
					</a></li>
					<li class="dropdown accounting-menu"><a href="#"
						class="dropdown-toggle" data-toggle="dropdown"> <i
							class="fa fa-book"></i> <span><?php echo xlt('Accountings'); ?></span>
					</a>
						<ul class="dropdown-menu">
							<li data-toggle="pill"><a href="#ledgerpanel" data-toggle="collapse"
								data-parent="#panelgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Ledger'); ?></span>
							</a></li>
							<!-- <li data-toggle="pill"><a href="#paymentpanel" data-toggle="collapse"
								data-parent="#panelgroup"> <i class="fa fa-credit-card"></i> <span><?php //echo xlt('Make Payment'); ?></span>
							</a></li> -->
						</ul></li>
					<li class="dropdown reporting-menu"><a href="#"
						class="dropdown-toggle" data-toggle="dropdown"> <i
							class="fa fa-calendar"></i> <span><?php echo xlt('Reports'); ?></span>
					</a>
						<ul class="dropdown-menu">
							<li data-toggle="pill"><a href="#reportpanel" data-toggle="collapse"
								data-parent="#panelgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Report Content'); ?></span></a></li>
							<li data-toggle="pill"><a href="#downloadpanel" data-toggle="collapse"
								data-parent="#panelgroup"> <i class="fa fa-download"></i> <span><?php echo xlt('Download Documents'); ?></span>
							</a></li>
						</ul></li>

					<li><a href="./messages.php"><i class="fa fa-envelope" aria-hidden="true"></i>
							<span><?php echo xlt('Secure Messaging'); ?></span>
					</a></li>
					<li data-toggle="pill"><a href="#messagespanel" data-toggle="collapse"
						data-parent="#panelgroup"> <i class="fa fa-envelope"></i> <span><?php echo xlt("Secure Chat"); ?></span>
					</a></li>
					<li data-toggle="pill"><a href="#openSignModal" data-toggle="modal" > <i
							class="fa fa-sign-in"></i><span><?php echo xlt('Signature on File'); ?></span>
					</a></li>

					<li><a href="logout.php"><i class="fa fa-ban fa-fw"></i> <span><?php echo xlt('Logout'); ?></span></a></li>
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>