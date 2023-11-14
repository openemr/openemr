### Easy Development Docker Environment for macOS with M1, M2, M* SoCs
The instructions for The Easy Development Docker environment can be found at [CONTRIBUTING.md](../../CONTRIBUTING.md#code-contributions-local-development).

#### File Permissions

UIDs and GIDs are handled differently between macOS and Linux. This discrepancy will cause permission errors on .git/objects/pack directory
 files similar to:

```
development-easy-macos-openemr-1     | chown: /var/www/localhost/htdocs/openemr/.git/objects/pack/pack-ab1e32999500a2e52117c503b8126b3f77e3ff26.pack: Permission denied
```

To remedy this, change the file permissions on those files to be more permissive. For example, you can execute `chmod -R 777 ./git/objects/pack`; however, we must note that this level of permissiveness is discouraged in production environments as it goes against the principle of least privilege best practice. 

