<?php

declare(strict_types=1);

use Vendor\Alpha;

/**
 * File docblock.
 *
 * @package Demo
 */

use Vendor\Beta;

echo Alpha::run() . Beta::run();

?>
-----
<?php

/**
 * File docblock.
 *
 * @package Demo
 */
declare(strict_types=1);

use Vendor\Alpha;

use Vendor\Beta;

echo Alpha::run() . Beta::run();

?>
