# OpenEMR Vendor Hooks Documentation

## Introduction

In an effort to help vendors and administrators of systems with customized code or modules cope with
more frequent releases with strong security implications, we now (as of 8.4.0) offer
access to container lifecycle events. Volume mount a run-parts-compatible set of scripts
in a hook directory, and the container will run your scripts at the appropriate lifecycle point.

## Lifecycle Hooks

### Locations

We define four hooks via `HOOKS_ROOT`, defaulting to `/root/hooks` within the container:
- `${HOOKS_ROOT}/postconfig` is run after first-time configuration is complete, and never again.
- `${HOOKS_ROOT}/postupgrade` is run after an upgrade process completes, on the authority container only (the sole container in a standard deployment, or the leader in swarm/Kubernetes mode).
- `${HOOKS_ROOT}/prelaunch` is run after all container setup is otherwise complete, every launch.
- `${HOOKS_ROOT}/tooearly` is run before any container setup even starts, aimed at debugging and rescue.

### Contents

A hook directory contains run-parts-compatible scripts (named only with letters, digits, underscores,
and hyphens, no dots) set executable, along with any number of other non-executable resource files you require.
These scripts will be run by run-parts in strict alphabetical order, and any that return non-zero will cause
container launch to fail.

### Notes

- `postconfig` and `postupgrade` will cause the container launch to fail if they don't return success, but since they can't roll back successful configuration events, when the container next launches it won't retry them because their lifecycle point has passed.
- Hook scripts inherit `openemr.sh`'s root permissions, which is relevant for security but also ownership of anything they unpack and install.
- `tooearly` probably shouldn't be used for normal deployments.

## Docker Compose Invocation

Mount the hook directory in to where the container expects to spot it:

```
    volumes:
    - logvolume01:/var/log
    - sitevolume:/var/www/localhost/htdocs/openemr/sites
    - vendor_prelaunch:/root/hooks/prelaunch:ro
```

```
volumes:
  vendor_prelaunch:
    driver_opts:
      type: none
      device: <absolute, not relative, path to hook directory on host>
      o: bind

```

Script output will be captured to the compose log, as expected. Don't forget that the scripts must be set executable.

### Worked Example

```
root@openemr:~/prelaunch-example# ls -al
total 16
drwxr-xr-x 2 root root 4096 Sep 12 02:47 .
drwx------ 6 root root 4096 Sep 12 02:46 ..
-rwxr-xr-x 1 root root   25 Sep 12 02:46 01hello
-rw-r--r-- 1 root root   30 Sep 12 02:47 02nope
-rw-r--r-- 1 root root    0 Sep 12 02:46 something.zip
root@openemr:~/prelaunch-example# cat 01hello
#!/bin/bash

echo hiiii
root@openemr:~/prelaunch-example# cat 02nope
#!/bin/bash

echo nope
exit 1
```

```
openemr-1  | Love OpenEMR? You can now support the project via the open collective:
openemr-1  |  > https://opencollective.com/openemr/donate
openemr-1  |
openemr-1  | WARNING: /root/hooks/prelaunch/02nope has a shebang but is not executable; run-parts will skip it
openemr-1  | hiiii
openemr-1  | prelaunch hook OK
openemr-1  | [TIMING] Step 15-PreApache: 2.0s elapsed
openemr-1  | [TIMING] Total script execution time: 2.0s before Apache start
openemr-1  | Starting Apache!
```

## Feedback

This is a work-in-progress, minimally tooled to offer a starting point, and we welcome feedback about
how we could build it out further. Let us know what you'd like to see in our [forums](https://community.open-emr.org/)!
