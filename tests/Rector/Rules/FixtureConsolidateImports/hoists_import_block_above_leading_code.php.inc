<?php

/**
 * File docblock.
 *
 * @package Demo
 */

require_once Vendor\Alpha::bootstrapPath();

use Vendor\Alpha;

echo Alpha::run();

?>
-----
<?php

/**
 * File docblock.
 *
 * @package Demo
 */
use Vendor\Alpha;
require_once Vendor\Alpha::bootstrapPath();

echo Alpha::run();

?>
