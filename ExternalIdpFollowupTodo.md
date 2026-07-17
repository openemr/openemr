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
