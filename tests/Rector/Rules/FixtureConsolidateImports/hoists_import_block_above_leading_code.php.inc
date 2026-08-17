<?php

/**
 * File docblock.
 *
 * @package Demo
 */

require_once Alpha::bootstrapPath();

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
require_once Alpha::bootstrapPath();

echo Alpha::run();

?>
