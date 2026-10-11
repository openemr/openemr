# Exporting and Importing Layouts, Lists and Other Configuration

Administration → System → Backup used to offer **Export Configuration** and **Import Configuration**. They moved chosen layouts (layout-based forms, transactions, history and demographics), lists, and a few configuration tables from one site to another, and many sites used them to keep their forms under version control. The page was removed in [#13471](https://github.com/openemr/openemr/pull/13471) (see [#11905](https://github.com/openemr/openemr/issues/11905)). This guide does the same with the standard database tools.

It's for administrators with command-line access to the OpenEMR database. For a backup of the whole site, see [Backing Up Using the Built In Tools](https://www.open-emr.org/wiki/index.php/Backing_Up_Using_the_Built_In_Tools).

## Before you start

- **Back up the database first.** An import replaces what's in the target site.
- **Import only files you made or have read.** An export is plain SQL, and importing it runs that SQL with the database user's rights.
- **Use your MySQL or MariaDB tool names.** On MariaDB 11 and later, including OpenEMR's Docker images, the tools are `mariadb-dump` and `mariadb`. On MySQL and older MariaDB they're `mysqldump` and `mysql`. The examples use `mysqldump` and `mysql`.
- **Keep passwords off the command line** by putting the user and password in an option file such as `~/.my.cnf`:

  ```ini
  [client]
  user=openemr
  password=your-password
  ```

- **With Docker,** run the commands inside the database container, for example `docker compose exec -T mysql mariadb-dump …`.
- **On Windows,** run them in Git Bash or WSL. They assume a POSIX shell.

Set these once per shell. `DUMP` writes one `INSERT` per row with column names and no table definitions, the same options the old page used:

```sh
# Settings
DB=openemr
DUMP="mysqldump --default-character-set=utf8mb4 --hex-blob --skip-opt --quote-names --no-tablespaces --complete-insert --no-create-info --skip-comments"
```

## Export a layout

A layout's ID is shown in Administration → Forms → Layouts: `LBF…` for encounter forms, `LBT…` for transactions, `HIS` for history, `DEM` for demographics. IDs are letters, digits and underscores; check yours before putting it in a command.

The file first deletes the layout in the target site, then inserts its groups and fields:

```sh
# Export a layout
F=LBFexample
{
  echo "SET NAMES utf8mb4;"
  echo "DELETE FROM layout_options WHERE form_id = '$F';"
  echo "DELETE FROM layout_group_properties WHERE grp_form_id = '$F';"
  $DUMP "$DB" layout_group_properties --where="grp_form_id = '$F'"
  $DUMP "$DB" layout_options --where="form_id = '$F' ORDER BY group_id, seq, title"
} > "layout-$F.sql"
```

### History and demographics layouts

History and demographics fields are stored as columns of `history_data` and `patient_data`. A field added in one site has no column in another, so after importing the layout, the target site must add any missing columns. Append this to the export of a `DEM` layout. For a history layout (`HIS…`), use `history_data`, `'HIS%'` and source `'H'` instead:

```sh
# Add missing patient_data columns on import
cat >> "layout-$F.sql" <<'SQL'
SET sql_mode = '';
SET group_concat_max_len = 1000000;
SELECT CONCAT('ALTER TABLE patient_data ',
    COALESCE(GROUP_CONCAT(DISTINCT ' ADD `', lo.field_id, '` TEXT NOT NULL' ORDER BY lo.field_id), ''))
  FROM layout_options AS lo
  WHERE (lo.form_id LIKE 'DEM%' OR lo.source = 'D')
    AND lo.field_id NOT IN (SELECT COLUMN_NAME FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'patient_data')
  INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
SQL
```

This is the SQL the old page appended, with one change: `TABLE_SCHEMA = DATABASE()`. Without it, on a server holding more than one OpenEMR database, another site's columns would count as present, and the new fields would have nowhere to save.

## Export a list

A list's ID is shown in Administration → Lists. The file replaces the list's entries and its entry in the list of lists:

```sh
# Export a list
L=example_list
{
  echo "SET NAMES utf8mb4;"
  echo "DELETE FROM list_options WHERE list_id = '$L';"
  echo "DELETE FROM list_options WHERE list_id = 'lists' AND option_id = '$L';"
  $DUMP "$DB" list_options --where="list_id = 'lists' AND option_id = '$L' OR list_id = '$L' ORDER BY list_id != 'lists', seq, title"
} > "list-$L.sql"
```

To find the lists a layout uses, so you can export them with it:

```sh
# Lists used by a layout
mysql -N "$DB" -e "SELECT DISTINCT a.list_id FROM layout_options AS a
  JOIN list_options AS i ON i.list_id = 'lists' AND i.option_id = a.list_id AND i.activity = 1 AND i.option_value = 0
  WHERE a.form_id = '$F' AND a.list_id != '' AND a.uor > 0 ORDER BY a.list_id"
```

The old page left out lists every site already has, so an export didn't overwrite them: `allergy_issue_list`, `boolean`, `education_level`, `ethrace`, `Gender`, `genhivhist`, `occupations`, `Relation_to_Client`, `sex`, `Sexual_Orientation` and `yesno`.

## Import

Run the file inside a transaction. `mysql` stops at the first error, and the unfinished transaction is then rolled back:

```sh
# Import
{ echo "START TRANSACTION;"; cat "layout-$F.sql"; echo "COMMIT;"; } | mysql "$DB"
```

The column step for history and demographics layouts can't be rolled back: `ALTER TABLE` commits what came before it.

## Keeping forms under version control

Each exported row is one `INSERT` line, so a file kept in git shows a changed field as a changed line:

```sh
# Version control
git add layout-$F.sql && git commit -m "LBFexample: add follow-up date"
```

To undo a change, import the earlier version of the file.

## Other configuration tables

The old page could also export whole tables. These replace the table in the target site, references included, so use them only on a new site:

| What | Tables |
|---|---|
| Services | `codes` |
| Products | `drugs`, `drug_templates` |
| Prices | `prices` |
| Document categories | `categories`, `categories_seq` |
| Fee sheet options | `fee_sheet_options` |
| Lab configuration | `procedure_type`, `procedure_providers`, `procedure_questions` |
| Translations | `lang_languages`, `lang_constants`, `lang_definitions` |

```sh
# Export whole tables
mysqldump --hex-blob --opt --quote-names --skip-comments --no-tablespaces "$DB" codes prices > config-tables.sql
```

If the two servers use different character sets or collations, remove them from the file before importing, as the old page did: `sed -E -i 's/ DEFAULT CHARSET=[A-Za-z0-9]*//I; s/ COLLATE[ =][^ ;,]*//I' config-tables.sql`.
