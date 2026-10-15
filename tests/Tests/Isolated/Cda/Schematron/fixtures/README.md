# Schematron test fixtures

| fixture | `node-golden` | `php-golden` | used by |
|---|:-:|:-:|---|
| `ccda-example-response1` | yes | yes | `testGoldenParityAgainstNodeService` |
| `qrda1-catI-doc-28` | yes | yes | `testGoldenParityAgainstNodeService` |
| `qrda3-minimal` | yes | yes | `testGoldenParityAgainstNodeService` |
| `qrda3-cms-variables` | **no, on purpose** | yes | `testSchLetVariablesEvaluateAgainstQrdaCategoryThree` |

- `*.node-golden.json` holds what the old Node schematron service reported. The
  parity test checks that PHP reports every Node error at least as many times.
  These captures ran with warnings disabled, so they record errors only.
- `*.php-golden.json` is a change-detection snapshot of this validator's output
  with default settings. If it changes, find out why before regenerating it.

## Why `qrda3-cms-variables` has no Node golden

This isn't an oversight. The fixture exists to exercise `<sch:let>` variables in
the QRDA III schematron: the NPI checksum chain, `$timeZoneExists`,
`$intendedRecipient-Doc`, and the NPI and TIN counts. The Node engine never
evaluated `<sch:let>`. It marked every one of those assertions ignored. So a
Node capture would record only that the behavior under test didn't happen, and
the parity check, which compares errors, would pass without testing anything.
The Node service has also been removed from the repo, so the capture couldn't
be rerun.

Instead, `testSchLetVariablesEvaluateAgainstQrdaCategoryThree` asserts the
expected outcome per assertion. Each malformed NPI must fail exactly the checks
it violates, and the valid NPI `1234567893` must pass all three. That last case
is what proves the checksum is computed, not just run. Keep this fixture out of
`goldenFixtureProvider()`, and don't add a Node golden for it.

See [`TESTING.md`](../../../../../../src/Services/Cda/Schematron/TESTING.md) for
the full test layout.
