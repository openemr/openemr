<?php

namespace OpenEMR\Tests\Rector\Fixture;

try {
    doSomething();
} catch (\Exception $e) {
    handleError($e);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

try {
    doSomething();
} catch (\Throwable $e) {
    handleError($e);
}

?>
