### Manual steps remaining

**Context: this checklist targets the legacy 4th-digit patch mechanism
(`$v_realpatch` bump in `version.php`), invoked as an emergent-hotfix
escape hatch via [`build-patch.yml`](../../.github/workflows/build-patch.yml).
Kept for zero-day-style situations where a full-release cycle isn't
warranted. This path runs INTENTIONALLY PARALLEL to the automated
release flow — most cascades that fire for full releases (docker
rebuilds via `release-targets.yml`, website download-page auto-update,
demo-farm auto-derive, announcement drafting on docs-PR merge) do NOT
fire here. The steps below reflect that.**

- [ ] Verify the patch zip on the GitHub Release page — the build workflow
      creates the tag + GitHub Release + uploads the patch zip + `changelog.md`
      as assets. Spot-check the patch zip's checksum against the workflow-run
      output.
- [ ] Update the [OpenEMR Patches](https://www.open-emr.org/wiki/index.php/OpenEMR_Patches)
      wiki page to reference the new patch. Separate wiki page from the main
      downloads page — patches aren't listed on the auto-generated release
      manifest.
- [ ] Docker: 4th-digit patches don't automatically re-fan `docker-build-release.yml`
      because `release-targets.yml` isn't touched. If a new docker image is
      warranted for this patch:
  - [ ] Manually dispatch [`docker-build-release.yml`](../../.github/workflows/docker-build-release.yml)
        for the rel branch with `openemr_version_ref` set to the new tag.
  - [ ] Update DockerHub readme if user-facing content changed.

      Otherwise (patch-as-overlay, no docker rebuild) skip this step — users
      apply the patch zip on top of their existing install.
- [ ] Point demo farm to the new tag if the patch warrants demo-side exercise.
      The `derive-ip-map` bot doesn't auto-run for 4th-digit patches (no
      `release-targets.yml` change fires `release-targets-changed`); operator
      manually updates `demo_farm_openemr/ip_map_branch.txt` if needed.
- [ ] Draft + post per-channel announcements manually. `release-announcements.yml`
      in `website-openemr` fires on docs-PR merge for full releases — 4th-digit
      patches have no docs PR, so no drafts render automatically. Consider whether
      the patch warrants announcements at all (many 4th-digit patches are
      quiet security-only pushes); if yes, hand-author the message using
      `changelog.md` from the build-run's artifacts as source material.
  - [ ] Forums
  - [ ] Chat
  - [ ] Twitter
  - [ ] Facebook
  - [ ] LinkedIn group + company page
  - [ ] Mailing list ([`oe-sender.js`](https://github.com/openemr/openemr-registration/blob/master/oe-sender.js))
