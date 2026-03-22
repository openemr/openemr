<?php

/**
 * DEPRECATED: MedEx Background Worker
 *
 * The MedEx module no longer relies on OpenEMR's `background_services` system
 * or the legacy `MedEx_background` worker. External synchronization and
 * message processing are managed outside the OpenEMR background process by
 * the module or external services. This file is retained only as a stub for
 * backward-compatibility and should not be executed.
 *
 * If you see references to `/library/MedEx/MedEx_background.php` or SQL that
 * updates the `background_services` table in the module docs, those are
 * historical and can be ignored — background services have been removed.
 */

// Intentionally empty - background worker removed. Do not execute.
