<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  bradymiller <bradymiller>
 * @author  cornfeed <jdough823@gmail.com>
 * @author  fndtn357 <fndtn357@gmail.com>
 * @copyright Copyright (c) 2010 bradymiller <bradymiller>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2012 fndtn357 <fndtn357@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

?>
<html>
<head>
    <title>Export Patient Demographics</title>

    <?php Header::setupHeader(); ?>
</head>
<body>

<p>This is a placeholder for a program to export the currently selected patient's
demographics to some other system.  A typical application would be a laboratory
requisiton system.</p>

<p>See export_labworks.php for a working implementation.  To install an export
program, replace this file with a symbolic link to that program.</p>

<center>
<form>
<p><input type='button' value='OK' onclick='window.close()' /></p>
</form>
</center>

</body>
</html>
