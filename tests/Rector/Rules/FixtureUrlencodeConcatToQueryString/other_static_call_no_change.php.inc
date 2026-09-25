<?php

namespace OpenEMR\Tests\Rector\Fixture;

final class Tokens
{
    public static function collectCsrfToken(): string
    {
        return 'a b';
    }
}

function lookalike(string $file): string
{
    return "x.php?key=" . urlencode($file) . "&csrf_token_form=" . Tokens::collectCsrfToken();
}

?>
