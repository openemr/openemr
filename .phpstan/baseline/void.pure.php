<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function send_patient_data_to_remote_system\\(\\) returns void but does not have any side effects\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/oe-patient-create-update-hooks-example/openemr.bootstrap.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
