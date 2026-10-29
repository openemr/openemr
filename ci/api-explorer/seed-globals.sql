-- Globals the oe-module-api-explorer needs before Register Clients will succeed.
-- Loaded by ci/api-explorer/run.sh after dev stack is up and before the driver runs.
--
-- Keys and values are backed by src/Services/Globals/GlobalConnectorsEnum.php;
-- update there if the enum changes and the keys here fall out of sync.

-- oauth_password_grant: 0=off, 1=users, 2=patient, 3=both. The dev-easy
-- compose already sets OPENEMR_SETTING_oauth_password_grant=3, so this
-- row is defence in depth after drid for the driver-password.php lane.
-- rest_portal_api gates the /apis/{site}/portal/* routes at the
-- dispatcher — needed for driver-portal.php.
REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES
  ('site_addr_oath', 0, 'https://openemr'),
  ('rest_api', 0, '1'),
  ('rest_fhir_api', 0, '1'),
  ('rest_system_scopes_api', 0, '1'),
  ('rest_portal_api', 0, '1'),
  ('oauth_password_grant', 0, '3');
