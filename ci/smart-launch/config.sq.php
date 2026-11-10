<?php

// SMART-launch smoke config for openemr/oe-smart-questionnaire.
//
// Writes outside the Apache document root (public/ serves only via alias).
// All URLs use the compose-network service alias `openemr`, so both the
// app's server-side PHP and the browser (via selenium) resolve the same
// hostname. verify_tls=false because openemr's dev cert is self-signed.
//
// Scope profile pinned to v1 — v2 (.rs + .cu) fails by design against
// current OpenEMR per the upstream README: OpenEMR's consent screen
// re-merges split scopes and the merged scope gets dropped. Don't flip
// to v2 as a "fix" when something regresses.

declare(strict_types=1);

return [
    'app_url' => 'https://openemr/smart-questionnaire',
    'app_name' => 'OpenEMR SMART Questionnaire smoke',
    'sites' => [
        'openemr' => 'https://openemr/apis/default/fhir',
    ],
    'contact' => 'acceptance-harness@openemr.example',
    'verify_tls' => false,
    'clients_file' => '/opt/oe-smart-questionnaire/app/var/clients.json',
    'default_scope_profile' => 'v1',
];
