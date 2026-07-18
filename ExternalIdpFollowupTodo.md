# External IdP Follow-up TODO

This file tracks cleanup and follow-up work that should happen after manual
testing is complete.

## Post-testing items

- Make the `Admin -> External Identity Provider` menu item conditional on the
  module being enabled, so the menu does not appear when OpenEMR will block the
  module path.
- Review whether the static Admin menu entry should move back to module-managed
  registration once the module-enable path is stable.
- Revisit `moduleConfigShell.php` and decide whether the shell should remain as
  a static title wrapper or be replaced with a more native OpenEMR module
  config entry point.
- Remove any now-unnecessary defensive fallbacks in
  `moduleConfig.php` after the module is validated in a clean environment.
- Confirm the module install flow always creates the required database tables in
  fresh deployments without relying on config-page bootstrap.
- Add a troubleshooting section to the end-user documentation covering:
  - blank config page
  - module disabled but menu visible
  - using Docker logs to diagnose module access issues
- Do one final code cleanup pass for warnings, redundant imports, and minimal
  structure once behavior is confirmed.
- Review the shadow-user provisioning implementation detail where the OIDC
  callback creates a local `users_secure` password hash directly. The current
  reason is that the core password helper expects an admin password prompt,
  which is not available inside the external-login callback. Decide whether to
  keep the generated hidden password approach or refactor the core external
  login/session flow to avoid requiring a stored local password for OIDC-only
  users.

## Testing-only changes to revisit

- `moduleConfigShell.php` is a static HTML shell that wraps the real config page
  in an iframe for stable tab-title behavior. Revisit whether this should
  remain in the final implementation.
- `moduleConfig.php` defaults `site` to `default` when no site parameter is
  present. Confirm whether this should remain or be replaced with a cleaner
  OpenEMR-native route path.
- `moduleConfig.php` attempts to create the module tables from `table.sql` if
  they are missing. Revisit whether schema creation should happen only through
  module install/enable instead of page bootstrap.
- `DiscoveryService.php` allows `http://` issuer and endpoint URLs for local
  testing. Revert this to HTTPS-only validation for production if required by
  deployment policy.
- `DiscoveryService.php` currently bypasses strict issuer equality checking and
  logs a warning instead. This was added only for local testing where the
  reachable container hostname differs from the issuer advertised by Keycloak.
- `DiscoveryService.php` now logs issuer URL, discovery URL, response status,
  and transport exceptions for debugging. Decide whether this level of logging
  should remain, be reduced, or be gated behind a debug setting.
