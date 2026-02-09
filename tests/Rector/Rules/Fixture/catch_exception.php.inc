<?php

namespace OpenEMR\Tests\Rector\Fixture;

use Exception;

try {
    doSomething();
} catch (Exception $e) {
    handleError($e);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

use Exception;

try {
    doSomething();
} catch (\Throwable $e) {
    handleError($e);
}

?>
