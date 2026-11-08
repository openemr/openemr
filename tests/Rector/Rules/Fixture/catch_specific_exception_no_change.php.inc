<?php

namespace OpenEMR\Tests\Rector\Fixture;

try {
    doSomething();
} catch (\InvalidArgumentException $e) {
    handleError($e);
}

?>
