# Layout-field renderer snapshot fixtures

These fixtures snapshot the HTML emitted today by `generate_form_field`,
`generate_display_field`, and `generate_print_field` in
`library/options.inc.php` for every `data_type` the dispatcher handles.

They act as a behavior-preservation gate for a future refactor of the
~40-branch `if/elseif` cascade behind a `FieldDataType` enum + `match`.

Regenerate after an intentional renderer change:

```bash
composer update-layout-field-fixtures
```

Review the diff with `git diff -- tests/Tests/Services/Common/Layouts/fixtures`
before committing.
