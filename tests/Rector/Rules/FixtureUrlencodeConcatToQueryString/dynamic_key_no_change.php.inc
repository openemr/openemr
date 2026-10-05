<?php

namespace OpenEMR\Tests\Rector\Fixture;

function dynamicKey(string $key, string $value): string
{
    return 'x.php?' . $key . '=' . urlencode($value);
}

?>
