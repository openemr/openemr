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

<link href="../assets/bootstrap/css/font-awesome.min.css" rel="stylesheet" />
<link href="../assets/bootstrap/css/bootstrap-datetimepicker.min.css"	rel="stylesheet" />
<link href="../assets/bootstrap/css/bootstrap-combobox.css" rel="stylesheet" />
<link href="styles/style.css" rel="stylesheet" />
<!--<link href="../assets/css/style.css" rel="stylesheet" />  -->
<script type="text/javascript" src="scripts/libs/LAB.min.js"></script>
<script type="text/javascript">
			$LAB.script("../assets/js/jquery-1.11.3.min.js").wait()
				.script("../assets/bootstrap/js/bootstrap.min12px.js")
				.script("../assets/js/eModal.js")
				.script("../assets/bootstrap/js/moment.min.js")
				.script("../assets/bootstrap/js/bootstrap-combobox.js")
				.script("../assets/bootstrap/js/bootstrap-datetimepicker.min.js")
				.script("scripts/libs/underscore-min.js").wait()
				.script("scripts/libs/underscore.date.min.js")
				.script("scripts/libs/backbone-min.js")
				.script("scripts/app.js")
				.script("scripts/model.js").wait()
				.script("scripts/view.js").wait()
		</script>

</head>
<body>
