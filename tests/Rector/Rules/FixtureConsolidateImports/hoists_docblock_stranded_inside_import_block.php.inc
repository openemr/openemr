<?php

use Vendor\Alpha;
use Vendor\Beta;

/**
 * File docblock.
 *
 * @package Demo
 */

use Vendor\Gamma;

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run() . Gamma::run();

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

use Vendor\Gamma;

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run() . Gamma::run();

?>
