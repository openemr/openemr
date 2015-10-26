<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Prescriptions','e'); ?></title>
  <style>
   .menu {
      float:left;
      width:20%;
      height:80%;
    }
    .mainContent {
      float:left;
      width:75%;
      height:80%;
    }
  </style>
</head>
<body>

 <iframe class="menu" src="rx_left.php" name="RxLeft" scrolling="auto"></iframe>
 <iframe class="mainContent" src="<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&list&id=<?php echo $pid ?>"
  name="RxRight" scrolling="auto"></iframe>

</body>
</html>
