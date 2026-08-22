<?php

declare(strict_types=1);

use Vendor\Beta;

require_once __DIR__ . '/globals.php';

use Vendor\Alpha;

echo Alpha::run() . Beta::run();

?>
-----
<?php

declare(strict_types=1);
use Vendor\Beta;
use Vendor\Alpha;

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run();

?>
