# PHPStan Custom Rules

Custom PHPStan rules for the OpenEMR project. These enforce project-specific coding standards during static analysis.

Rules are registered in `.phpstan/extension.neon` and live here (under `tests/`) so that PHPStan's result cache correctly tracks changes to them.
