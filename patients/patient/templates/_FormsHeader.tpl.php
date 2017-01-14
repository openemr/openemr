<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">

<title><?php $this->eprint($this->title); ?></title>
<meta content="width=device-width, initial-scale=1, user-scalable=no"
	name="viewport">

<base href="<?php $this->eprint($this->ROOT_URL); ?>" />
<meta name="description" content="OpenEMR Portal" />
<meta name="author" content="Form | sjpadgett@gmail.com" />

<!-- Styles -->
<link href="../assets/bootstrap/css/bootstrap.min12px.css" rel="stylesheet" />
<!-- <link href="../assets/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" /> -->

<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" rel="stylesheet" />
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/eonasdan-bootstrap-datetimepicker-3-1-3/build/css/bootstrap-datetimepicker.min.css"	rel="stylesheet" />
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-combobox-1-1-7/css/bootstrap-combobox.css" rel="stylesheet" />
<link href="styles/style.css" rel="stylesheet" />
<!--<link href="../assets/css/style.css" rel="stylesheet" />  -->
<script type="text/javascript" src="scripts/libs/LAB.min.js"></script>
<script type="text/javascript">
			$LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js").wait()
				.script("../assets/bootstrap/js/bootstrap.min12px.js")
				.script("<?php echo $GLOBALS['assets_static_relative']; ?>/emodal-1-2-65/dist/eModal.js")
				.script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment-2-13-0/moment.js")
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
