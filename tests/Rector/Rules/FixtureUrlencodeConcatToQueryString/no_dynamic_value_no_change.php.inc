<?php

namespace OpenEMR\Tests\Rector\Fixture;

function staticOnly(string $webroot): string
{
    return $webroot . '/x.php?a=1&b=2';
}

?>
