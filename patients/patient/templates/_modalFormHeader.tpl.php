
<head>
<meta charset="utf-8">

<title><?php $this->eprint($this->title); ?></title>
<meta content="width=device-width, initial-scale=1, user-scalable=no"	name="viewport">

<base href="<?php $this->eprint($this->ROOT_URL); ?>" />
<meta name="description" content="Patient Profile" />
<meta name="author" content="Form | sjpadgett@gmail.com" />

<!-- <link href="../assets/bootstrap/css/bootstrap.min12px.css" rel="stylesheet" />
<link href="../assets/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" />-->
<link href="../assets/bootstrap/css/font-awesome.min.css" rel="stylesheet" />
<link href="../assets/bootstrap/css/bootstrap-datetimepicker.min.css"	rel="stylesheet" />
<link href="../assets/bootstrap/css/bootstrap-combobox.css" rel="stylesheet" />

<!--<script type="text/javascript" src="../assets/js/jquery-1.11.3.min.js"></script>
 <script type="text/javascript" src="../assets/bootstrap/js/bootstrap.min12px.js"></script>-->
<link href="styles/style.css" rel="stylesheet" />
<script type="text/javascript" src="../assets/bootstrap/js/moment.min.js"></script>
<script type="text/javascript" src="../assets/bootstrap/js/bootstrap-combobox.js"></script>
<script type="text/javascript" src="../assets/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="scripts/libs/underscore-min.js"></script>
<script type="text/javascript" src="scripts/libs/underscore.date.min.js"></script>
<script type="text/javascript" src="scripts/libs/backbone-min.js"></script>
 <!--<script type="text/javascript" src="../js/eModal.js?version=&&TIME&&"></script>-->
<script type="text/javascript" src="scripts/app.js"></script>
<script type="text/javascript" src="scripts/model.js"></script>
<script type="text/javascript" src="scripts/view.js"></script>

<script type="text/javascript" src="scripts/libs/LAB.min.js"></script>
<script type="text/javascript">
$LAB.setGlobalDefaults({BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"});
</script>
<!-- * Don't need script load order or waits here ???
<script type="text/javascript">
			$LAB.script("assets/js/jquery-1.11.3.min.js").wait()
				.script("assets/bootstrap/js/bootstrap.min.js")
				.script("assets/bootstrap/js/moment.min.js")
				.script("assets/bootstrap/js/bootstrap-datepicker.js")
				.script("assets/bootstrap/js/bootstrap-timepicker.min.js")
				.script("assets/bootstrap/js/bootstrap-combobox.js")
				.script("assets/bootstrap/js/bootstrap-datetimepicker.min.js")
				.script("patient/scripts/libs/underscore-min.js").wait()
				.script("patient/scripts/libs/underscore.date.min.js")
				.script("patient/scripts/libs/backbone-min.js")
				.script("patient/scripts/app.js")
				.script("patient/scripts/model.js").wait()
				.script("patient/scripts/view.js").wait()
		</script> -->

</head>
<body>
