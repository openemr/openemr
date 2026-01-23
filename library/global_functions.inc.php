<?php

declare(strict_types=1);

// This file is intentionally empty for now; see PHPStan rule
// ForbiddenGlobalNamespaceRule

/**
 * Reads $_POST and trims the value. New code should NOT use this function.
 */
function trimPost(string $key): string
{
    return \trim($_POST[$key] ?? '');
}
