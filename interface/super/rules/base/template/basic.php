<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['css_header'] ?>" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">

</head>

<body class='body_top'>
<?php
if ( file_exists($viewBean->_view_body) ) {
    require_once($viewBean->_view_body);
}
?>

</body>

</html>