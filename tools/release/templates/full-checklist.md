### Manual steps remaining

Most post-release steps that historically appeared here are now automated,
split across three repos: **`openemr/openemr`** owns docker image builds
(via `docker-release-orchestrator.yml` fanning out `docker-build-release.yml`
per rel branch) and fires the `release-targets-changed` dispatch that
triggers the downstream cascades; **`openemr/website-openemr`** owns the
download-page auto-update (`data/releases.json` update on `openemr-tag`
dispatch + Hugo rebuild on docs-PR merge to master) and announcement
drafting (`release-announcements.yml` renders per-channel drafts on
docs-PR merge); **`openemr/demo_farm_openemr`** owns demo-farm reconciliation
(the `derive-ip-map` bot opens a reconciliation PR on `release-targets-changed`
+ daily cron). Wiki pages ("Release History", "OpenEMR Downloads") remain
manual — they're separate from the auto-generated download page. Only
steps that require maintainer judgment, landing external content, or
touching the wiki stay on this list.

- [ ] Verify source archives + checksums on the GitHub Release page match the
      artifact bundle this build produced (spot-check one tarball / one zip / one
      checksum file against the run's Upload artifacts step output).
- [ ] Confirm the docker image auto-build for this release completed successfully
      on Docker Hub — the [`.github/workflows/docker-release-orchestrator.yml`](../../../.github/workflows/docker-release-orchestrator.yml)
      cron fires 06:00 UTC daily and after `.github/release-targets.yml`
      changes; check `openemr/openemr` Actions for the `docker-build-release`
      run that ships this version's tag.
- [ ] Confirm the [website-openemr](https://github.com/openemr/website-openemr)
      docs PR merged and the download page reflects the new version. Site is
      Hugo-rebuilt on merge to master; changes go live within minutes.
- [ ] Update the wiki [Release History](https://www.open-emr.org/wiki/index.php/OpenEMR_Downloads)
      page — separate from the auto-generated website download page.
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
