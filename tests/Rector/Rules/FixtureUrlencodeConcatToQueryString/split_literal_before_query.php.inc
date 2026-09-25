<?php

namespace OpenEMR\Tests\Rector\Fixture;

function media(string $base, string $siteId, string $token): string
{
    return $base . '/library/faxMedia.php'
        . '?site=' . urlencode($siteId)
        . '&t=' . urlencode($token);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function media(string $base, string $siteId, string $token): string
{
    return $base . '/library/faxMedia.php?' . \OpenEMR\Common\Http\QueryString::build(['site' => $siteId, 't' => $token]);
}

?>
