<?php

namespace OpenEMR\Tests\Rector\Fixture;

function link(string $id, int $pid): string
{
    return 'x.php?mode=edit&id=' . urlencode($id) . '&pid=' . $pid;
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function link(string $id, int $pid): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::build([
        'mode' => 'edit',
        'id' => $id,
        'pid' => $pid,
    ]);
}

?>
