<?php

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


/**
 * Set the site ID if required.
 *
 * This must be done before any database access is attempted.
 * Use an IIFE to ensure no variables leak.
 */

(function (): void {
    // Any directory name in sites that contains a sqlconf.php can be a site id.
    $sites_dirs = glob('sites/*', GLOB_ONLYDIR) ?: [];
    $valid_site_ids = array_map(
        basename(...),
        array_filter($sites_dirs, fn($d) => is_file("{$d}/sqlconf.php"))
    );

    switch(count($valid_site_ids)) {
        case 0:
            throw new RuntimeException('No valid sites found');
        // Often there's only one valid request id, so we can ignore input.
        case 1:
            $site_id = $valid_site_ids[0];
            break;
        default:
            $site_id = filter_input(INPUT_GET, 'site') ?: (filter_input(INPUT_SERVER, 'HTTP_HOST') ?: 'default');
            if (!in_array($site_id, $valid_site_ids, true)) {
                throw new RuntimeException("Invalid site id: {$site_id}");
            };
    }
    require_once "sites/{$site_id}/sqlconf.php";
    /** @var int $config Defined in sqlconf.php */
    header('Location: ' . ($config === 1 ? 'interface/login/login.php' : 'setup.php') . "?site=$site_id");
})();
