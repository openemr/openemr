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
<html lang="en">
	<head>
		<meta charset="utf-8">

		<title><?php $this->eprint($this->title); ?></title>
		<meta content="width=device-width, initial-scale=1, user-scalable=yes" name="viewport">
		<meta http-equiv="X-Frame-Options" content="deny">
		<base href="<?php $this->eprint($this->ROOT_URL); ?>" />
		<meta name="description" content="Provider Portal" />
		<meta name="author" content="Dashboard | sjpadgett@gmail.com" />

		<!-- Styles -->
		<link href="../assets/bootstrap/css/bootstrap.min12px.css" rel="stylesheet" />
		<link href="styles/style.css" rel="stylesheet" />
		<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" rel="stylesheet" />
		<link href="../sign/css/signer.css" rel="stylesheet" type="text/css" />
		<link href="../sign/assets/signpad.css" rel="stylesheet">

		<script type="text/javascript" src="scripts/libs/LAB.min.js"></script>
		<script type="text/javascript">
			$LAB.setGlobalDefaults({BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"});
			$LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js")
				//.script("../sign/assets/signpad.js")
				.script("../sign/assets/signer.js")
				.script("../assets/bootstrap/js/bootstrap.min12px.js")
				.script("scripts/libs/underscore-min.js")
				.script("scripts/libs/underscore.date.min.js")
				.script("scripts/libs/backbone-min.js")

				.script("scripts/app.js")
				.script("scripts/model.js").wait()
				.script("scripts/view.js").wait()
		</script>
	</head>

	<body>
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
					<div class="navbar-header"><a class="navbar-brand" href="./provider"><?php echo xlt('Home'); ?></a>
						<a class="navbar-toggle btn-default" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="glyphicon glyphicon-bar"></span>
        					<span class="glyphicon glyphicon-bar"></span>
        					<span class="glyphicon glyphicon-bar"></span>
						</a>
						</div>
						<div class="container">
						<div class="navbar-collapse">
							<ul class="nav navbar-nav">
								<!-- <li <?php //if ($this->nav=='patientdata') { echo 'class="active"'; } ?>><a href="./patientdata?pid=30">Patient Demo's</a></li>
								<li <?php //if ($this->nav=='onsiteactivityviews') { echo 'class="active"'; } ?>><a href="./onsiteactivityviews">Patient's Activities</a></li> -->
								</ul>
							<!-- <ul class="nav pull-right navbar-nav">
								<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-lock"></i> Login <i class="caret"></i></a>
								<ul class="dropdown-menu">
									<li><a href="./loginform">Login</a></li>
									<li class="divider"></li>
									<li><a href="./secureuser">Patient Dashboard<i class="icon-lock"></i></a></li>
									<li><a href="./secureadmin">Provider Dashboard<i class="icon-lock"></i></a></li>
								</ul>
								</li>
							</ul> -->
						</div><!--/.nav-collapse -->
					</div>
				</div>
			</div>