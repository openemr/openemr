<?php

namespace OpenEMR\Tests\Rector\Fixture;

function redirect(string $pid): void
{
    header('Location: ../x.php?set_pid=' . urlencode($pid));
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function redirect(string $pid): void
{
    header('Location: ../x.php?' . \OpenEMR\Common\Http\QueryString::build(['set_pid' => $pid]));
}

?>
