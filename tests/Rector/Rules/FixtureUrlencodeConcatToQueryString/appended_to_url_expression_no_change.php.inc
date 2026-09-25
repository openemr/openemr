<?php

namespace OpenEMR\Tests\Rector\Fixture;

function appendOnly(string $url, string $a): string
{
    return $url . '&a=' . urlencode($a);
}

?>
