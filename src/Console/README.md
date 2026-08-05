# Console commands

These commands intentionally differ from those in `OpenEMR\Common\Command`:

1) They support (and may require) dependency injection with autowiring
2) They leverage the `__invoke` Symfony Console tooling for improved/simplified type safety

However, they only work in `./cli` and not `./bin/console`.
The two will eventually be unified, but these are "clean break" commands without backwards compatibility concerns.
