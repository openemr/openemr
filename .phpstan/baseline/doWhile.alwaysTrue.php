<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Do\\-while loop condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Do\\-while loop condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_pat_sel.inc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
