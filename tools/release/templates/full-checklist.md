### Manual steps remaining

Most post-release steps that historically appeared here are now automated
by workflows in `openemr/openemr` (docker builds, demo farm reconciliation,
announcement drafting, wiki/download-page updates). Only steps that require
maintainer judgment or landing external content stay on this list.

- [ ] Verify source archives + checksums on the GitHub Release page match the
      artifact bundle this build produced (spot-check one tarball / one zip / one
      checksum file against the run's Upload artifacts step output).
- [ ] Confirm the docker image auto-build for this release completed successfully
      on Docker Hub — the [`.github/workflows/docker-release-orchestrator.yml`](../../.github/workflows/docker-release-orchestrator.yml)
      cron fires 06:00 UTC daily and after `.github/release-targets.yml`
      changes; check `openemr/openemr` Actions for the `docker-build-release`
      run that ships this version's tag.
- [ ] Confirm the [website-openemr](https://github.com/openemr/website-openemr)
      docs PR merged and the download page reflects the new version. Site is
      Hugo-rebuilt on merge to master; changes go live within minutes.
- [ ] Confirm the `demo_farm_openemr` auto-derive reconciliation PR merged
      (fired by the `release-targets-changed` dispatch when the finalize-on-master
      PR lands). Demo farm honors the new tag once the docker image is on
      Docker Hub.
- [ ] Post the per-channel announcement drafts (rendered as a `website-openemr`
      workflow artifact by [`release-announcements.yml`](https://github.com/openemr/website-openemr/blob/master/.github/workflows/release-announcements.yml)
      on the docs PR merge — drafting is automated; posting is still manual):
  - [ ] Forums (paste `forum.md`; if `mail.subject.txt` includes a link to
        the freshly-posted thread, backfill it into the mailing-list draft)
  - [ ] Chat
  - [ ] Twitter
  - [ ] Facebook
  - [ ] LinkedIn group + company page
  - [ ] Mailing list — run [`oe-sender.js`](https://github.com/openemr/openemr-registration/blob/master/oe-sender.js)
        against `mail.html` + `mail.subject.txt` from the drafts artifact
