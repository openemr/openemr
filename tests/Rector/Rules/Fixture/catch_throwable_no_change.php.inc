<?php

namespace OpenEMR\Tests\Rector\Fixture;

try {
    doSomething();
} catch (\Throwable $e) {
    handleError($e);
}

?>
