<?php

namespace OpenEMR\Tests\Rector\Fixture;

function continued(string $url, string $email, string $data): string
{
    return $url . urlencode($email) . '&data=' . urlencode($data);
}

?>
