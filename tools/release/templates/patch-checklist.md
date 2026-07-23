### Manual steps remaining

Most post-patch steps that historically appeared here are now automated
by workflows in `openemr/openemr` (docker builds, demo farm reconciliation,
announcement drafting). Only steps that require maintainer judgment or
landing external content stay on this list.

- [ ] Verify the patch zip on the GitHub Release page matches the artifact
      bundle this build produced (spot-check the patch zip's checksum).
- [ ] Confirm the docker image auto-build for the patched rel branch
      completed successfully on Docker Hub —
      [`docker-release-orchestrator.yml`](../../.github/workflows/docker-release-orchestrator.yml)
      re-fans the branch's docker-build-release run after `release-targets.yml`
      pins the new patch tag.
- [ ] Confirm the [website-openemr](https://github.com/openemr/website-openemr)
      docs PR merged and the download page reflects the new patch version.
- [ ] Confirm `demo_farm_openemr` auto-derive PR merged and the demo pool
      picked up the new tag.
- [ ] Post the per-channel announcement drafts rendered by
      [`release-announcements.yml`](https://github.com/openemr/website-openemr/blob/master/.github/workflows/release-announcements.yml)
      on the docs PR merge:
  - [ ] Forums (backfill thread URL into mailing list draft if needed)
  - [ ] Chat
  - [ ] Twitter
  - [ ] Facebook
  - [ ] LinkedIn group + company page
  - [ ] Mailing list ([`oe-sender.js`](https://github.com/openemr/openemr-registration/blob/master/oe-sender.js))
