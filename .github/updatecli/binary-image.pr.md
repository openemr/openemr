Bumps `docker/binary` to the newest openemr-static-binary-forge release that the image can actually build against, plus the defaults documented in its README.

Opened by `.github/workflows/updatecli-docker-pins.yml` from `.github/updatecli/binary-image.yaml`. Nothing here merges itself — review the diff and merge when the bump looks right.

#### What was checked before this PR was opened

`tools/release/bin/resolve-binary-forge.php` only reports a pin once all of the following hold, and falls back to the next-newest release (or leaves the pin alone) otherwise:

- Both `linux_amd64` and `linux_arm64` forge releases exist for the same OpenEMR version and build date. A bump on one arch alone would break every build on the other.
- Each of those releases carries all three assets the Dockerfile downloads: `php-fpm-v<version>-linux-<arch>`, `php-cli-v<version>-linux-<arch>`, and `openemr.phar`.
- The matching `openemr/openemr` tag exists **and** still has a `tests/` tree. `tests/` is `export-ignore` in `.gitattributes`, which is what broke the v8_3_0 bump when the Dockerfile still fetched tag tarballs.
- The candidate is newer than the current pin. The forge re-cuts older version lines, and those rebuilds must not drag the image backwards.

The forge's PHP selector (`php85`) is derived from `ARG PHP_VERSION` in the Dockerfile, so a PHP bump retargets the search without any change here.

#### What still needs a human

Whether this OpenEMR version is one the binary image should ship. The checks above establish that it *can* be built, not that it *should* be released.
