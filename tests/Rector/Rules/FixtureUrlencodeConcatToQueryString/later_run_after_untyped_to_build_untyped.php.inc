<?php

namespace OpenEMR\Tests\Rector\Fixture;

function partial($site, string $redirect): string
{
    return 'index.php?site=' . urlencode($site) . '&redirect=' . urlencode($redirect);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function partial($site, string $redirect): string
{
    return 'index.php?' . \OpenEMR\Common\Http\QueryString::buildUntyped(['site' => $site, 'redirect' => $redirect]);
}

?>
