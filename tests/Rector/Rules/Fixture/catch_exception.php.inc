<?php

namespace OpenEMR\Tests\Rector\Fixture;

use Exception;

try {
    throw new Exception('boom');
} catch (Exception $e) {
    handleError($e);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

use Exception;

try {
    throw new Exception('boom');
} catch (\Throwable $e) {
    handleError($e);
}

?>
