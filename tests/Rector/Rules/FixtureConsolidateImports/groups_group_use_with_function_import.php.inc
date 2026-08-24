<?php

declare(strict_types=1);

use Vendor\Alpha;

use function Vendor\helper;

require_once __DIR__ . '/globals.php';

use Vendor\Nested\{
    Beta,
    Gamma,
};

return [Alpha::class, Beta::class, Gamma::class, helper(...)];

?>
-----
<?php

declare(strict_types=1);
use Vendor\Alpha;

use Vendor\Nested\{
    Beta,
    Gamma,
};
use function Vendor\helper;
require_once __DIR__ . '/globals.php';

return [Alpha::class, Beta::class, Gamma::class, helper(...)];

?>
