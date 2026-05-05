# OpenEMR on ECS + RDS + EFS (Practical Setup Guide)

This guide explains how to run OpenEMR reliably in AWS ECS/Fargate with RDS and EFS, based on the behavior we observed in this repo.

It is written for teams who already have Terraform provisioning ECS/RDS/EFS and want startup to be predictable.

## Why AWS behaves differently than local Docker Compose

Local `docker-compose` usually works quickly because Docker named volumes are initialized in a way that feels like "it just works" for first run.

In ECS with EFS, OpenEMR can fail on first boot if `sites/` is empty or partially initialized.

Common symptoms:

- `Upgrade detected: 0 -> 9`
- `Error: Cannot upgrade - OpenEMR is not configured yet`
- (when swarm mode is enabled) `An OpenEMR worker is trying to run on a missing configuration`

Root cause:

- OpenEMR startup logic expects a usable `sites/default` state.
- With EFS, that state may not exist on first boot unless you explicitly seed it.

## Recommended architecture in this repo

Use OpenEMR with:

- `SWARM_MODE=no`
- an idempotent `seed-sites` container in the same ECS task definition
- persistent EFS mounted at OpenEMR `sites/`

### Why this works

- The `seed-sites` container runs first and copies baseline files from `/swarm-pieces/sites/` into EFS when needed.
- The OpenEMR container starts only after `seed-sites` exits successfully.
- On later deploys, seeding is skipped if OpenEMR is already configured.

## Required infrastructure components

- ECS cluster and Fargate service
- RDS MySQL instance
- EFS file system + access point
- Security groups allowing:
  - ECS OpenEMR task -> RDS `3306`
  - ECS OpenEMR task -> EFS `2049`
- ALB target group + listener rules (if exposing OpenEMR)

## Required OpenEMR environment inputs

At minimum, OpenEMR container needs:

- `MYSQL_HOST`
- `MYSQL_ROOT_USER`
- `MYSQL_ROOT_PASS`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASS`
- `OE_USER`
- `OE_PASS`
- `SWARM_MODE=no`

This repo currently uses master DB credentials for OpenEMR startup in ECS.

## First-time deploy flow

1. Apply Terraform so RDS/EFS/ECS resources exist.
2. Deploy OpenEMR task definition containing both:
   - `seed-sites` container (non-essential)
   - `openemr` container (essential, depends on `seed-sites:SUCCESS`)
3. Watch CloudWatch logs:
   - `seed-sites` should log seeding (or skipping)
   - then OpenEMR should proceed into setup and start Apache
4. Confirm health endpoint and ALB target health.

## Idempotent seeding behavior

The seeding step should:

- skip if `/seed-sites/default/sqlconf.php` exists
- copy baseline files only when needed
- remove stale marker files from previous swarm experiments:
  - `docker-leader`
  - `docker-initiated`
  - `docker-completed`
- write a sentinel file (for example `.base_seed_complete`)

This keeps first boot reliable and repeat deploys fast.

## Recovery playbook for broken state

If OpenEMR still fails during startup, the most common issue is mismatched persisted state between RDS and EFS.

Perform a one-time reset:

1. Scale OpenEMR ECS service to `0`.
2. Snapshot/backup RDS and EFS if needed.
3. Ensure EFS `sites/` path is cleared (or recreate EFS/access point).
4. Recreate or reset the OpenEMR DB (if needed for clean install).
5. Scale service back up and watch first-boot logs.

## Troubleshooting quick map

- `Upgrade detected: 0 -> 9` followed by `not configured yet`
  - Usually means `sites/` not seeded correctly before OpenEMR startup.
- `worker ... missing configuration`
  - Usually from `SWARM_MODE=yes` leader/follower behavior with missing shared state.

## Operational recommendations

- Pin OpenEMR image version (avoid implicit image changes).
- Keep OpenEMR replicas at `1` unless you intentionally design full multi-replica shared-state behavior.
- Treat EFS as stateful data; do not wipe casually outside planned recovery.
- Keep CloudWatch log retention long enough for post-deploy debugging.

---

If you are using this repository, the source of truth for deployment configuration is:

- `terraform/openemr_variables.tf`
- `terraform/ecs_openemr.tf`
- `terraform/database.tf`
- `terraform/storage_efs.tf`
- `terraform/dns_acm.tf`
- `terraform/network.tf`
- `terraform/locals.tf`
