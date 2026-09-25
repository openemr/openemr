<?php

namespace OpenEMR\Tests\Rector\Fixture;

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

function claimFile(string $file, SessionInterface $session): string
{
    return "get_claim_file.php?key=" . attr_url($file) . "&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

function claimFile(string $file, SessionInterface $session): string
{
    return "get_claim_file.php?" . \OpenEMR\Common\Http\QueryString::build(['key' => $file, 'csrf_token_form' => CsrfUtils::collectCsrfToken(session: $session)]);
}

?>
