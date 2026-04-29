"""Backend-for-Frontend (BFF).

ARCHITECTURE.md §3:

- OAuth2 authorization-code with Proof Key for Code Exchange (PKCE)
  against OpenEMR's OAuth2 server.
- 5-minute downscoped task tokens per ``Patient/{id}`` compartment with a
  ``purpose_of_use`` claim.
- Second-layer policy check on ``(user, patient, purpose)`` against a
  local Postgres store, independent of OpenEMR's ACLs.

The BFF is the only component that ever holds a refresh token. The
sidecar receives a 5-minute task token signed with the BFF's key, never
the refresh token.
"""
