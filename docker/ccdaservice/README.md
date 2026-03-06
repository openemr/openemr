# Containerized C-CDA Service

Run the C-CDA (ccdaservice) as a standalone container instead of the default
local Node.js service co-located in the OpenEMR container.

## How It Works

By default, all Docker development environments run the ccdaservice **inside
the OpenEMR container** as a separate Node.js process (managed by the
`/etc/init.d/ccdaservice` init script, listening on 127.0.0.1:6661).

The `ccdaservice` container is included in each compose file but does nothing
until you point OpenEMR at it via globals.

## Enabling the Containerized Service

Set these globals in **Admin > Config > Connectors**:

| Global                  | Value          |
|-------------------------|----------------|
| `ccda_service_host`     | `ccdaservice`  |
| `ccda_service_port`     | `6661`         |

Or set them as environment variables on the OpenEMR service:

```yaml
environment:
  OPENEMR_SETTING_ccda_service_host: ccdaservice
  OPENEMR_SETTING_ccda_service_port: 6661
```

The `ccda_alt_service_enable` global (already set to `3` in all dev
environments) controls which features use the service. No change is needed
there.

## Reverting to the Local Service

Reset `ccda_service_host` back to `127.0.0.1` (the default) in the admin
globals, or remove the `OPENEMR_SETTING_ccda_service_host` environment
variable.
