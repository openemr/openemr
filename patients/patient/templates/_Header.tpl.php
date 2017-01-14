<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">

		<title><?php $this->eprint($this->title); ?></title>
		<meta content="width=device-width, initial-scale=1, user-scalable=yes" name="viewport">
		<meta http-equiv="X-Frame-Options" content="deny">
		<base href="<?php $this->eprint($this->ROOT_URL); ?>" />
		<meta name="description" content="Patient Portal" />
		<meta name="author" content="Form | sjpadgett@gmail.com" />

		<!-- Styles -->
		<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<!-- <link href="../assets/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" /> -->
		<link href="styles/style.css" rel="stylesheet" />
		<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" rel="stylesheet" />
		<link href="../assets/bootstrap/css/datepicker.css" rel="stylesheet" />
		<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/eonasdan-bootstrap-datetimepicker-3-1-3/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
		<link href="../assets/bootstrap/css/bootstrap-timepicker.min.css" rel="stylesheet" />
		<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-combobox-1-1-7/css/bootstrap-combobox.css" rel="stylesheet" />

		<script type="text/javascript" src="scripts/libs/LAB.min.js"></script>
		<script type="text/javascript">
			$LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js").wait()
				.script("../assets/bootstrap/js/bootstrap.min.js")
				.script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment-2-13-0/moment.js")
				.script("../assets/bootstrap/js/bootstrap-datepicker.js")
				.script("../assets/bootstrap/js/bootstrap-timepicker.min.js")
				.script("<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-combobox-1-1-7/js/bootstrap-combobox.js")
				.script("<?php echo $GLOBALS['assets_static_relative']; ?>/eonasdan-bootstrap-datetimepicker-3-1-3/build/js/bootstrap-datetimepicker.min.js")
				.script("scripts/libs/underscore-min.js").wait()
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
					<div class="navbar-header"><a class="navbar-brand" href="./">Home</a>
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
							<ul class="nav pull-right navbar-nav">
								<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-lock"></i> Login <i class="caret"></i></a>
								<ul class="dropdown-menu">
									<li><a href="./loginform">Login</a></li>
									<li class="divider"></li>
									<li><a href="./secureuser">Patient Dashboard<i class="icon-lock"></i></a></li>
									<li><a href="./secureadmin">Provider Dashboard<i class="icon-lock"></i></a></li>
								</ul>
								</li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</div>
			</div>