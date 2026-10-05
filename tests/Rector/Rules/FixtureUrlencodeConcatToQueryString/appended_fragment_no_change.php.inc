<?php

namespace OpenEMR\Tests\Rector\Fixture;

function appendMode(string $url, string $mode): string
{
    $url .= '&mode=' . urlencode($mode);
    return $url;
}

?>
