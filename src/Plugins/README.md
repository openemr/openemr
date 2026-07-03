# Plugins Infrastructure

This contains the tooling for new plugins. These are only available on the front-controller and cli paths.

New plugins are:

- Managed exclusively as external Composer packages
- Only given access to a well-defined, relatively narrow set of interfaces, which *are* subject to SemVer
- Required to rely on dependency injection and service autowiring

They can:

- Add command-line tools
- Add API endpoints
- Add DB migrations
- Be configured

Eventually, they will be able to:

- Interact with core through FHIR APIs

For now, they cannot:

- Use "legacy" tooling (queryutils, globalsbag, etc)
- Directly render UIs (this may be lifted in the future, keeping scope small for now)
