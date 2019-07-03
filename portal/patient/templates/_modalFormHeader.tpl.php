<?php
/**
 * _modalFormHeader.tpl.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<title><?php $this->eprint($this->title); ?></title>
<meta content="width=device-width, initial-scale=1, user-scalable=no"   name="viewport">


<meta name="description" content="Patient Profile" />
<meta name="author" content="Form | sjpadgett@gmail.com" />

<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js" type="text/javascript"></script>
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css"  rel="stylesheet" />
<?php if ($this->register) {?>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
<?php } ?>

<link href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/styles/style.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/moment/moment.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
<base href="<?php $this->eprint($this->ROOT_URL); ?>" />
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
<script type="text/javascript">
$LAB.setGlobalDefaults({BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"});
</script>
</head>
<body>
