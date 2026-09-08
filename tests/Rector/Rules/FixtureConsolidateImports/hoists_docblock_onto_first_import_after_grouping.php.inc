<?php

use function Vendor\helper;

/**
 * File docblock.
 *
 * @package Demo
 */

require_once __DIR__ . '/globals.php';

use Vendor\Alpha;

echo Alpha::run() . helper();

?>
-----
<?php

/**
 * File docblock.
 *
 * @package Demo
 */
use Vendor\Alpha;
use function Vendor\helper;
require_once __DIR__ . '/globals.php';

echo Alpha::run() . helper();

?>
