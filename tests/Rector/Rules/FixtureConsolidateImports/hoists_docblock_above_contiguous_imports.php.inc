<?php

use Vendor\Alpha;
use Vendor\Beta;

/**
 * File docblock.
 *
 * @package Demo
 */

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run();

?>
-----
<?php

/**
 * File docblock.
 *
 * @package Demo
 */
use Vendor\Alpha;
use Vendor\Beta;

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run();

?>
