<?php

// Conditional baseline loading based on environment
$config = [];

// Check if running in GitHub Actions
if (getenv('GITHUB_ACTIONS') === 'true') {
    // Running in GitHub Actions - include GitHub-specific baseline
    if (file_exists(__DIR__ . '/phpstan.github.neon')) {
        $config['includes'] = ['phpstan.github.neon'];
    }
} else {
    // Running locally - include local baseline if it exists
    if (file_exists(__DIR__ . '/phpstan.local.neon')) {
        $config['includes'] = ['phpstan.local.neon'];
    }
}

return $config;
