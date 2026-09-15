<?php

use Vendor\Beta;

/**
 * File docblock.
 *
 * @package Demo
 */

// prevent UI redressing
header('X-Frame-Options: DENY');

use Vendor\Alpha;

require_once __DIR__ . '/globals.php';

?>
-----
<?php

/**
 * File docblock.
 *
 * @package Demo
 */
use Vendor\Beta;
use Vendor\Alpha;
// prevent UI redressing
header('X-Frame-Options: DENY');

require_once __DIR__ . '/globals.php';

?>
