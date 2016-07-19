# ADOdb Changelog - v5.x

Older changelogs:
[v4.x](changelog_v4.x.md),
[v3.x](changelog_v3.x.md),
[v2.x](changelog_v2.x.md).


## 5.20.2 - 27-Dec-2015

- adodb: Remove a couple leftover PHP 4.x constructors (PHP7 compatibility). #139
- mysql: MoveNext() now respects ADODB_ASSOC_CASE. #167
- mssql, mysql, informix: Avoid PHP warning when closing recordset from destructor. #170

## 5.20.1 - 06-Dec-2015

- adodb: Fix regression introduced in 5.20.0, causing a PHP Warning when
  calling GetAssoc() on an empty recordset. See Github #162
- ADOConnection::Version() now handles SemVer. See Github #164

## 5.20.0 - 28-Nov-2015

- adodb: Fix regression introduced in v5.19, causing queries to return empty rows. See Github #20, #93, #95
- adodb: Fix regression introduced in v5.19 in GetAssoc() with ADODB_FETCH_ASSOC mode and '0' as data. See Github #102
- adodb: AutoExecute correctly handles empty result set in case of updates. See Github #13
- adodb: Fix regex in Version(). See Github #16
- adodb: Align method signatures to definition in parent class ADODB_DataDict. See Github #31
- adodb: Improve compatibility of ADORecordSet_empty, thanks to Sjan Evardsson. See Github #43
- adodb: fix ADODB_Session::open() failing after successful ADONewConnection() call, thanks to Sjan Evardsson. See Github #44
- adodb: Only include memcache library once for PHPUnit 4.x, thanks to Alan Farquharson. See Github #74
- adodb: Move() returns false when given row is < 0, thanks to Mike Benoit.
- adodb: Add support for pagination with complex queries, thanks to Mike Benoit. See Github #88
- adodb: Parse port out of hostname if specified in connection parameters, thanks to Andy Theuninck. See Github #63
- adodb: Fix inability to set values from 0 to null (and vice versa) with Active Record, thanks to Louis Johnson. See Github #71
- adodb: Fix PHP strict warning in ADODB_Active_Record::Reload(), thanks to Boštjan Žokš. See Github #75
- adodb: Add mssql's DATETIME2 type to ADOConnection::MetaType(), thanks to MarcelTO. See Github #80
- adodb: When flushing cache, initialize it if it is not set, thanks to Paul Haggart. See Github #57
- adodb: Define DB_AUTOQUERY_* constants in main include file. See Github #49
- adodb: Improve documentation of fetch mode and assoc case
- adodb: Improve logic to build the assoc case bind array
- adodb: Strict-standards compliance for function names. See Github #18, #142
- adodb: Remove old PHP 4.x constructors for compatibility with PHP 7. See Github #139
- adodb: Initialize charset in ADOConnection::SetCharSet. See Github #39
- adodb: Fix incorrect handling of input array in Execute(). See Github #146
- adodb: Release Recordset when raising exception. See Github #143
- adodb: Added new setConnectionParameter() method, currently implemented in mssqlnative driver only. See Github #158.
- adodb-lib: Optimize query pagination, thanks to Mike Benoit. See Github #110
- memcache: use include_once() to avoid issues with PHPUnit. See http://phplens.com/lens/lensforum/msgs.php?id=19489
- mssql_n: Allow use of prepared statements with driver. See Github #22
- mssqlnative: Use ADOConnection::outp instead of error_log. See Github #12
- mssqlnative: fix failure on Insert_ID() if the insert statement contains a semicolon in a value string, thanks to sketule. See Github #96
- mssqlnative: Fix "invalid parameter was passed to sqlsrv_configure" error, thanks to Ray Morris. See Github #103
- mssqlnative: Fix insert_ID() failing if server returns more than 1 row, thanks to gitjti. See Github #41
- mysql: prevent race conditions when creating/dropping sequences, thanks to MikeB. See Github #28
- mysql: Fix adodb_strip_order_by() bug causing SQL error for subqueries with order/limit clause, thanks to MikeB.
- mysql: workaround for HHVM behavior, thanks to Mike Benoit.
- mysqli: Fix qstr() when called without an active connection. See Github #11
- oci8: Fix broken quoting of table name in AddColumnSQL and AlterColumnSQL, thanks to Andreas Fernandez. see Github #67
- oci8: Allow oci8 driver to use lowercase field names in assoc mode. See Github #21
- oci8po: Prevent replacement of '?' within strings, thanks to Mark Newnham. See Github #132
- pdo: Added missing property (fixes PHP notices). see Github #56
- pdo: Align method signatures with parent class, thanks to Andy Theuninck. see Github #62
- pdo: new sqlsrv driver, thanks to MarcelTO. See Github #81
- pdo/mysql: New methods to make the driver behave more like mysql/mysqli, thanks to Andy Theuninck. see Github #40
- postgres: Stop using legacy function aliases
- postgres: Fix AlterColumnSQL when updating multiple columns, thanks to Jouni Ahto. See Github #72
- postgres: Fix support for HHVM 3.6, thanks to Mike Benoit. See Github #87
- postgres: Noblob optimization, thanks to Mike Benoit. See Github #112
- postgres7: fix system warning in MetaColumns() with schema. See http://phplens.com/lens/lensforum/msgs.php?id=19481
- sqlite3: ServerInfo() now returns driver's version
- sqlite3: Fix wrong connection parameter in _connect(), thanks to diogotoscano. See Github #51
- sqlite3: Fix FetchField, thanks to diogotoscano. See Github #53
- sqlite3: Fix result-less SQL statements executed twice. See Github #99
- sqlite3: use -1 for _numOfRows. See Github #151
- xmlschema: Fix ExtractSchema() when given $prefix and $stripprefix parameters, thanks to peterdd. See Github #92
- Convert languages files to UTF-8, thanks to Marc-Etienne Vargenau. See Github #32.

## 5.19 - 23-Apr-2014

**NOTE:**
This release suffers from a [known issue with Associative Fetch Mode](https://github.com/ADOdb/ADOdb/issues/20)
(i.e. when $ADODB_FETCH_MODE is set to ADODB_FETCH_ASSOC).
It causes recordsets to return empty strings (no data) when using some database drivers.
The problem has been reported on MSSQL, Interbase and Foxpro, but possibly affects
other database types as well; all drivers derived from the above are also impacted.

- adodb: GetRowAssoc will return null as required. See http://phplens.com/lens/lensforum/msgs.php?id=19289
- adodb: Fix GetRowAssoc bug introduced in 5.17, causing function to return data from previous fetch for NULL fields. See http://phplens.com/lens/lensforum/msgs.php?id=17539
- adodb: GetAssoc will return a zero-based array when 2nd column is null. See https://sourceforge.net/p/adodb/bugs/130/
- adodb: Execute no longer ignores single parameters evaluating to false. See https://sourceforge.net/p/adodb/patches/32/
- adodb: Fix LIMIT 1 clause in subquery gets stripped off. See http://phplens.com/lens/lensforum/msgs.php?id=17813
- adodb-lib: Fix columns quoting bug. See https://sourceforge.net/p/adodb/bugs/127/
- Added new ADODB_ASSOC_CASE_* constants. Thx to Damien Regad.
- sessions: changed lob handling to detect all variations of oci8 driver.
- ads: clear fields before fetching. See http://phplens.com/lens/lensforum/msgs.php?id=17539
- mssqlnative: fixed many FetchField compat issues. See http://phplens.com/lens/lensforum/msgs.php?id=18464. Also date format changed to remove timezone.
- mssqlnative: Numerous fixes and improvements by Mark Newnham
    - Driver supports SQL Server 2005, 2008 and 2012
    - Bigint data types mapped to I8 instead of I
    - Reintroduced MetaColumns function
    - On SQL Server 2012, makes use of new CREATE SEQUENCE statement
    - FetchField caches metadata at initialization to improve performance
    - etc.
- mssqlnative: Fix Insert ID on prepared statement, thanks to Mike Parks. See http://phplens.com/lens/lensforum/msgs.php?id=19079
- mssql: timestamp format changed to `Y-m-d\TH:i:s` (ISO 8601) to make them independent from DATEFORMAT setting, as recommended on
  [Microsoft TechNet](http://technet.microsoft.com/en-us/library/ms180878%28v=sql.105%29.aspx#StringLiteralDateandTimeFormats).
- mysql/mysqli: Fix ability for MetaTables to filter by table name, broken since 5.15. See http://phplens.com/lens/lensforum/msgs.php?id=19359
- odbc: Fixed MetaTables and MetaPrimaryKeys definitions in odbc driver to match adoconnection class.
- odbc: clear fields before fetching. See http://phplens.com/lens/lensforum/msgs.php?id=17539
- oci8: GetRowAssoc now works in ADODB_FETCH_ASSOC fetch mode
- oci8: MetaType and MetaForeignKeys argument count are now strict-standards compliant
- oci8: Added trailing `;` on trigger creation for sequence fields, prevents occurence of ORA-24344
- oci8quercus: new oci8 driver with support for quercus jdbc data types.
- pdo: Fixed concat recursion bug in 5.3. See http://phplens.com/lens/lensforum/msgs.php?id=19285
- pgsql: Default driver (postgres/pgsql) is now postgres8
- pgsql: Fix output of BLOB (bytea) columns with PostgreSQL >= 9.0
- pgsql: Fix handling of DEFAULT NULL columns in AlterColumnSQL
- pgsql: Fix mapping of error message to ADOdb error codes
- pgsql: Reset parameter number in Param() method when $name == false
- postgres8: New class/type with correct behavior for _insertid(). See Github #8
- postgres9: Fixed assoc problem. See http://phplens.com/lens/lensforum/msgs.php?id=19296
- sybase: Removed redundant sybase_connect() call in _connect(). See Github #3
- sybase: Allow connection on custom port. See Github #9
- sybase: Fix null values returned with ASSOC fetch mode. See Github #10
- Added Composer support. See Github #7

## 5.18 - 3 Sep 2012

- datadict-postgres: Fixes bug in ALTER COL. See http://phplens.com/lens/lensforum/msgs.php?id=19202.
- datadict-postgres: fixed bugs in MetaType() checking $fieldobj properties.
- GetRowAssoc did not work with null values. Bug in 5.17.
- postgres9: New driver to better support PostgreSQL 9. Thx Glenn Herteg and Cacti team.
- sqlite3: Modified to support php 5.4. Thx GÃ¼nter Weber [built.development#googlemail.com]
- adodb: When fetch mode is ADODB_FETCH_ASSOC, and we execute `$db->GetAssoc("select 'a','0'");` we get an error. Fixed. See http://phplens.com/lens/lensforum/msgs.php?id=19190
- adodb: Caching directory permissions now configurable using global variable $ADODB_CACHE_PERMS. Default value is 0771.
- mysqli: SetCharSet() did not return true (success) or false (fail) correctly. Fixed.
- mysqli: changed dataProvider to 'mysql' so that MetaError and other shared functions will work.
- mssqlnative: Prepare() did not work previously. Now calling Prepare() will work but the sql is not actually compiled. Unfortunately bind params are passed to sqlsrv_prepare and not to sqlsrv_execute. make Prepare() and empty function, and we still execute the unprepared stmt.
- mysql: FetchField(-1), turns it is is not possible to retrieve the max_length. Set to -1.
- mysql-perf: Fixed "SHOW INNODB STATUS". Change to "SHOW ENGINE INNODB STATUS"

## 5.17 - 18 May 2012

- Active Record: Removed trailing whitespace from adodb-active-record.inc.php.
- odbc: Added support for $database parameter in odbc Connect() function. E.g. $DB->Connect($dsn_without_db, $user, $pwd, $database).
  Previously $database had to be left blank and the $dsn was used to pass in this parameter.
- oci8: Added better empty($rs) error handling to metaindexes().
- oci8: Changed to use newer oci API to support PHP 5.4.
- adodb.inc.php: Changed GetRowAssoc to more generic code that will work in all scenarios.

## 5.16 - 26 March 2012

- mysqli: extra mysqli_next_result() in close() removed. See http://phplens.com/lens/lensforum/msgs.php?id=19100
- datadict-oci8: minor typo in create sequence trigger fixed. See http://phplens.com/lens/lensforum/msgs.php?id=18879.
- security: safe date parsing changes. Does not impact security, these are code optimisations. Thx Saithis.
- postgres, oci8, oci8po, db2oci: Param() function parameters inconsistent with base class. $type='C' missing. Fixed.
- active-record: locked bug fixed. http://phplens.com/lens/lensforum/msgs.php?phplens_forummsg=new&id=19073
- mysql, mysqli and informix: added MetaProcedures. Metaprocedures allows to retrieve an array list of all procedures in database. http://phplens.com/lens/lensforum/msgs.php?id=18414
- Postgres7: added support for serial data type in MetaColumns().

## 5.15 - 19 Jan 2012

- pdo: fix ErrorMsg() to detect errors correctly. Thx Jens.
- mssqlnative: added another check for $this->fields array exists.
- mssqlnative: bugs in FetchField() fixed. See http://phplens.com/lens/lensforum/msgs.php?id=19024
- DBDate and DBTimeStamp had sql injection bug. Fixed. Thx Saithis
- mysql and mysqli: MetaTables() now identifies views and tables correctly.
- Added function adodb_time() to adodb-time.inc.php. Generates current time in unsigned integer format.

## 5.14 - 8 Sep 2011

- mysqli: fix php compilation bug.
- postgres: bind variables did not work properly. Fixed.
- postgres: blob handling bug in _decode. Fixed.
- ActiveRecord: if a null field was never updated, activerecord would still update the record. Fixed.
- ActiveRecord: 1 char length string never quoted. Fixed.
- LDAP: Connection string ldap:// and ldaps:// did not work. Fixed.

## 5.13 - 15 Aug 2011

- Postgres: Fix in 5.12 was wrong. Uses pg_unescape_bytea() correctly now in _decode.
- GetInsertSQL/GetUpdateSQL: Now $ADODB_QUOTE_FIELDNAMES allows you to define 'NATIVE', 'UPPER', 'LOWER'. If set to true, will default to 'UPPER'.
- mysqli: added support for persistent connections 'p:'.
- mssqlnative: ADODB_FETCH_BOTH did not work properly. Fixed.
- mssqlnative: return values for stored procedures where not returned! Fixed. See http://phplens.com/lens/lensforum/msgs.php?id=18919
- mssqlnative: timestamp and fetchfield bugs fixed. http ://phplens.com/lens/lensforum/msgs.php?id=18453

## 5.12 - 30 June 2011

- Postgres: Added information_schema support for postgresql.
- Postgres: Use pg_unescape_bytea() in _decode.
- Fix bulk binding with oci8. http://phplens.com/lens/lensforum/msgs.php?id=18786
- oci8 perf: added wait evt monitoring. Also db cache advice now handles multiple buffer pools properly.
- sessions2: Fixed setFetchMode problem.
- sqlite: Some DSN connection settings were not parsed correctly.
- mysqli: now GetOne obeys $ADODB_GETONE_EOF;
- memcache: compress option did not work. Fixed. See http://phplens.com/lens/lensforum/msgs.php?id=18899

## 5.11 - 5 May 2010

- mysql: Fixed GetOne() to return null if no records returned.
- oci8 perf: added stats on sga, rman, memory usage, and flash in performance tab.
- odbtp: Now you can define password in $password field of Connect()/PConnect(), and it will add it to DSN.
- Datadict: altering columns did not consider the scale of the column. Now it does.
- mssql: Fixed problem with ADODB_CASE_ASSOC causing multiple versions of column name appearing in recordset fields.
- oci8: Added missing & to refLob.
- oci8: Added obj->scale to FetchField().
- oci8: Now you can get column info of a table in a different schema, e.g. MetaColumns("schema.table") is supported.
- odbc_mssql: Fixed missing $metaDatabasesSQL.
- xmlschema: Changed declaration of create() to create($xmls) to fix compat problems. Also changed constructor adoSchema() to pass in variable instead of variable reference.
- ado5: Fixed ado5 exceptions to only display errors when $this->debug=true;
- Added DSN support to sessions2.inc.php.
- adodb-lib.inc.php. Fixed issue with _adodb_getcount() not using $secs2cache parameter.
- adodb active record. Fixed caching bug. See http://phplens.com/lens/lensforum/msgs.php?id=18288.
- db2: fixed ServerInfo().
- adodb_date: Added support for format 'e' for TZ as in adodb_date('e')
- Active Record: If you have a field which is a string field (with numbers in) and you add preceding 0's to it the adodb library does not pick up the fact that the field has changed because of the way php's == works (dodgily). The end result is that it never gets updated into the database - fix by Matthew Forrester (MediaEquals). [matthew.forrester#mediaequals.com]
- Fixes RowLock() and MetaIndexes() inconsistencies. See http://phplens.com/lens/lensforum/msgs.php?id=18236
- Active record support for postgrseql boolean. See http://phplens.com/lens/lensforum/msgs.php?id=18246
- By default, Execute 2D array is disabled for security reasons. Set $conn->bulkBind = true to enable. See http://phplens.com/lens/lensforum/msgs.php?id=18270. Note this breaks backward compat.
- MSSQL: fixes for 5.2 compat. http://phplens.com/lens/lensforum/msgs.php?id=18325
- Changed Version() to return a string instead of a float so it correctly returns 5.10 instead of 5.1.

## 5.10 - 10 Nov 2009

- Fixed memcache to properly support $rs->timeCreated.
- adodb-ado.inc.php: Added BigInt support for PHP5. Will return float instead to support large numbers. Thx nasb#mail.goo.ne.jp.
- adodb-mysqli.inc.php: mysqli_multi_query is now turned off by default. To turn it on, use $conn->multiQuery = true; This is because of the risks of sql injection. See http://phplens.com/lens/lensforum/msgs.php?id=18144
- New db2oci driver for db2 9.7 when using PL/SQL mode. Allows oracle style :0, :1, :2 bind parameters which are remapped to ? ? ?.
- adodb-db2.inc.php: fixed bugs in MetaTables. SYS owner field not checked properly. Also in $conn->Connect($dsn, null, null, $schema) and PConnect($dsn, null, null, $schema), we do a SET SCHEMA=$schema if successful connection.
- adodb-mysqli.inc.php: Now $rs->Close() closes all pending next resultsets. Thx Clifton mesmackgod#gmail.com
- Moved _CreateCache() from PConnect()/Connect() to CacheExecute(). Suggested by Dumka.
- Many bug fixes to adodb-pdo_sqlite.inc.php and new datadict-sqlite.inc.php. Thx Andrei B. [andreutz#mymail.ro]
- Removed usage of split (deprecated in php 5.3). Thx david#horizon-nigh.org.
- Fixed RowLock() parameters to comply with PHP5 strict mode in multiple drivers.

## 5.09 - 25 June 2009

- Active Record: You can force column names to be quoted in INSERT and UPDATE statements, typically because you are using reserved words as column names by setting ADODB_Active_Record::$_quoteNames = true;
- Added memcache and cachesecs to DSN. e.g.

    ``` php
    # we have a memcache servers mem1,mem2 on port 8888, compression=off and cachesecs=120
    $dsn = 'mysql://user:pwd@localhost/mydb?memcache=mem1,mem2:8888:0&cachesecs=120';
    ```

- Fixed up MetaColumns and MetaPrimaryIndexes() for php 5.3 compat. Thx http://adodb.pastebin.com/m52082b16
- The postgresql driver's OffsetDate() apparently does not work with postgres 8.3. Fixed.
- Added support for magic_quotes_sybase in qstr() and addq(). Thanks Eloy and Sam Moffat.
- The oci8 driver did not handle LOBs properly when binding. Fixed. See http://phplens.com/lens/lensforum/msgs.php?id=17991.
- Datadict: In order to support TIMESTAMP with subsecond accuracy, added to datadict the new TS type. Supported by mssql, postgresql and oci8 (oracle). Also changed oci8 $conn->sysTimeStamp to use 'SYSTIMESTAMP' instead of 'SYSDATE'. Should be backwards compat.
- Added support for PHP 5.1+ DateTime objects in DBDate and DBTimeStamp. This means that dates and timestamps will be managed by DateTime objects if you are running PHP 5.1+.
- Added new property to postgres64 driver to support returning I if type is unique int called $db->uniqueIisR, defaulting to true. See http://phplens.com/lens/lensforum/msgs.php?id=17963
- Added support for bindarray in adodb_GetActiveRecordsClass with SelectLimit in adodb-active-record.inc.php.
- Transactions now allowed in ado_access driver. Thx to petar.petrov.georgiev#gmail.com.
- Sessions2 garbage collection is now much more robust. We perform ORDER BY to prevent deadlock in adodb-sessions2.inc.php.
- Fixed typo in pdo_sqlite driver.

## 5.08a - 17 Apr 2009

- Fixes wrong version number string.
- Incorrect + in adodb-datadict.inc.php removed.
- Fixes missing OffsetDate() function in pdo. Thx paul#mantisforge.org.

## 5.08 - 17 Apr 2009

- adodb-sybase.inc.php driver. Added $conn->charSet support. Thx Luis Henrique Mulinari (luis.mulinari#gmail.com)
- adodb-ado5.inc.php. Fixed some bind param issues. Thx Jirka Novak.
- adodb-ado5.inc.php. Now has improved error handling.
- Fixed typo in adodb-xmlschema03.inc.php. See XMLS_EXISTING_DATA, line 1501. Thx james johnson.
- Made $inputarr optional for _query() in all drivers.
- Fixed spelling mistake in flushall() in adodb.inc.ophp.
- Fixed handling of quotes in adodb_active_record::doquote. Thx Jonathan Hohle (jhohle#godaddy.com).
- Added new index parameter to adodb_active_record::setdatabaseadaptor. Thx Jonathan Hohle
- Fixed & readcache() reference compat problem with php 5.3 in adodb.Thx Jonathan Hohle.
- Some minor $ADODB_CACHE_CLASS definition issues in adodb.inc.php.
- Added Reset() function to adodb_active_record. Thx marcus.
- Minor dsn fix for pdo_sqlite in adodb.inc.php. Thx Sergey Chvalyuk.
- Fixed adodb-datadict _CreateSuffix() inconsistencies. Thx Chris Miller.
- Option to delete old fields $dropOldFlds in datadict ChangeTableSQL($table, $flds, $tableOptions, $dropOldFlds=false) added. Thx Philipp Niethammer.
- Memcache caching did not expire properly. Fixed.
- MetaForeignKeys for postgres7 driver changed from adodb_movenext to $rs->MoveNext (also in 4.99)
- Added support for ldap and ldaps url format in ldap driver. E.g. ldap://host:port/dn?attributes?scope?filter?extensions

## 5.07 - 26 Dec 2008

- BeginTrans/CommitTrans/RollbackTrans return true/false correctly on success/failure now for mssql, odbc, oci8, mysqlt, mysqli, postgres, pdo.
- Replace() now quotes all non-null values including numeric ones.
- Postgresql qstr() now returns booleans as *true* and *false* without quotes.
- MetaForeignKeys in mysql and mysqli drivers had this problem: A table can have two foreign keys pointing to the same column in the same table. The original code will incorrectly report only the last column. Fixed. https://sourceforge.net/tracker/index.php?func=detail&aid=2287278&group_id=42718&atid=433976
- Passing in full ado connection string in $argHostname with ado drivers was failing in adodb5 due to bug. Fixed.
- Fixed memcachelib flushcache and flushall bugs. Also fixed possible timeCreated = 0 problem in readcache. (Also in adodb 4.992). Thanks AlexB_UK (alexbarnes#hotmail.com).
- Fixed a notice in adodb-sessions2.inc.php, in _conn(). Thx bober m.derlukiewicz#rocktech.remove_me.pl;
- ADOdb Active Record: Fixed some issues with incompatible fetch modes (ADODB_FETCH_ASSOC) causing problems in UpdateActiveTable().
- ADOdb Active Record: Added support for functions that support predefining one-to-many relationships:
   _ClassHasMany ClassBelongsTo TableHasMany TableBelongsTo TableKeyHasMany TableKeyBelongsTo_.
- You can also define your child/parent class in these functions, instead of the default ADODB_Active_Record. Thx Arialdo Martini & Chris R for idea.
- ADOdb Active Record: HasMany hardcoded primary key to "id". Fixed.
- Many pdo and pdo-sqlite fixes from Sid Dunayer [sdunayer#interserv.com].
- CacheSelectLimit not working for mssql. Fixed. Thx AlexB.
- The rs2html function did not display hours in timestamps correctly. Now 24hr clock used.
- Changed ereg* functions to use preg* functions as ereg* is deprecated in PHP 5.3. Modified sybase and postgresql drivers.

## 5.06 - 16 Oct 2008

- Added driver adodb-pdo_sqlite.inc.php. Thanks Diogo Toscano (diogo#scriptcase.net) for the code.
- Added support for [one-to-many relationships](docs-active-record.htm#onetomany) with BelongsTo() and HasMany() in adodb_active_record.
- Added BINARY type to mysql.inc.php (also in 4.991).
- Added support for SelectLimit($sql,-1,100) in oci8. (also in 4.991).
- New $conn->GetMedian($table, $field, $where='') to get median account no. (also in 4.991)
- The rs2html() function in tohtml.inc.php did not handle dates with ':' in it properly. Fixed. (also in 4.991)
- Added support for connecting to oci8 using `$DB->Connect($ip, $user, $pwd, "SID=$sid");` (also in 4.991)
- Added mysql type 'VAR_STRING' to MetaType(). (also in 4.991)
- The session and session2 code supports setfetchmode assoc properly now (also in 4.991).
- Added concat support to pdo. Thx Andrea Baron.
- Changed db2 driver to use format `Y-m-d H-i-s` for datetime instead of `Y-m-d-H-i-s` which was legacy from odbc_db2 conversion.
- Removed vestigal break on adodb_tz_offset in adodb-time.inc.php.
- MetaForeignKeys did not work for views in MySQL 5. Fixed.
- Changed error handling in GetActiveRecordsClass.
- Added better support for using existing driver when $ADODB_NEWCONNECTION function returns false.
- In _CreateSuffix in adodb-datadict.inc.php, adding unsigned variable for mysql.
- In adodb-xmlschema03.inc.php, changed addTableOpt to include db name.
- If bytea blob in postgresql is null, empty string was formerly returned. Now null is returned.
- Changed db2 driver CreateSequence to support $start parameter.
- rs2html() now does not add nbsp to end if length of string > 0
- The oci8po FetchField() now only lowercases field names if ADODB_ASSOC_CASE is set to 0.
- New mssqlnative drivers for php. TQ Garrett Serack of M'soft. [Download](http://www.microsoft.com/downloads/details.aspx?FamilyId=61BF87E0-D031-466B-B09A-6597C21A2E2A&displaylang=en) mssqlnative extension. Note that this is still in beta.
- Fixed bugs in memcache support.
- You can now change the return value of GetOne if no records are found using the global variable $ADODB_GETONE_EOF. The default is null. To change it back to the pre-4.99/5.00 behaviour of false, set $ADODB_GETONE_EOF = false;
- In Postgresql 8.2/8.3 MetaForeignkeys did not work. Fixed William Kolodny William.Kolodny#gt-t.net

## 5.05 - 11 Jul 2008

Released together with [v4.990](changelog_v4.x.md#4990---11-jul-2008)

- Added support for multiple recordsets in mysqli , thanks to Geisel Sierote geisel#4up.com.br. See http://phplens.com/lens/lensforum/msgs.php?id=15917
- Malcolm Cook added new Reload() function to Active Record. See http://phplens.com/lens/lensforum/msgs.php?id=17474
- Thanks Zoltan Monori (monzol#fotoprizma.hu) for bug fixes in iterator, SelectLimit, GetRandRow, etc.
- Under heavy loads, the performance monitor for oci8 disables Ixora views.
- Fixed sybase driver SQLDate to use str_replace(). Also for adodb5, changed sybase driver UnixDate and UnixTimeStamp calls to static.
- Changed oci8 lob handler to use & reference `$this->_refLOBs[$numlob]['VAR'] = &$var`.
- We now strtolower the get_class() function in PEAR::isError() for php5 compat.
- CacheExecute did not retrieve cache recordsets properly for 5.04 (worked in 4.98). Fixed.
- New ADODB_Cache_File class for file caching defined in adodb.inc.php.
- Farsi language file contribution by Peyman Hooshmandi Raad (phooshmand#gmail.com)
- New API for creating your custom caching class which is stored in $ADODB_CACHE:

    ``` php
    include "/path/to/adodb.inc.php";
    $ADODB_CACHE_CLASS = 'MyCacheClass';
    class MyCacheClass extends ADODB_Cache_File
    {
        function writecache($filename, $contents,$debug=false) {...}
        function &readcache($filename, &$err, $secs2cache, $rsClass) { ...}
         :
    }
    $DB = NewADOConnection($driver);
    $DB->Connect(...); ## MyCacheClass created here and stored in $ADODB_CACHE global variable.
    $data = $rs->CacheGetOne($sql); ## MyCacheClass is used here for caching...
    ```

- Memcache supports multiple pooled hosts now. Only if none of the pooled servers
  can be contacted will a connect error be generated. Usage example below:

    ``` php
    $db = NewADOConnection($driver);
    $db->memCache = true; /// should we use memCache instead of caching in files
    $db->memCacheHost = array($ip1, $ip2, $ip3); /// $db->memCacheHost = $ip1; still works
    $db->memCachePort = 11211; /// this is default memCache port
    $db->memCacheCompress = false; /// Use 'true' to store the item compressed (uses zlib)
    $db->Connect(...);
    $db->CacheExecute($sql);
    ```

## 5.04 - 13 Feb 2008

Released together with [v4.98](changelog_v4.x.md#498---13-feb-2008)

- Fixed adodb_mktime problem which causes a performance bottleneck in $hrs.
- Added mysqli support to adodb_getcount().
- Removed MYSQLI_TYPE_CHAR from MetaType().

## 5.03 - 22 Jan 2008

Released together with [v4.97](changelog_v4.x.md#497---22-jan-2008)

- Active Record: $ADODB_ASSOC_CASE=1 did not work properly. Fixed.
- Modified Fields() in recordset class to support display null fields in FetchNextObject().
- In ADOdb5, active record implementation, we now support column names with spaces in them - we autoconvert the spaces to _ using __set(). Thx Daniel Cook. http://phplens.com/lens/lensforum/msgs.php?id=17200
- Removed $arg3 from mysqli SelectLimit. See http://phplens.com/lens/lensforum/msgs.php?id=16243. Thx Zsolt Szeberenyi.
- Changed oci8 FetchField, which returns the max_length of BLOB/CLOB/NCLOB as 4000 (incorrectly) to -1.
- CacheExecute would sometimes return an error on Windows if it was unable to lock the cache file. This is harmless and has been changed to a warning that can be ignored. Also adodb_write_file() code revised.
- ADOdb perf code changed to only log sql if execution time >= 0.05 seconds. New $ADODB_PERF_MIN variable holds min sql timing. Any SQL with timing value below this and is not causing an error is not logged.
- Also adodb_backtrace() now traces 1 level deeper as sometimes actual culprit function is not displayed.
- Fixed a group by problem with adodb_getcount() for db's which are not postgres/oci8 based.
- Changed mssql driver Parameter() from SQLCHAR to SQLVARCHAR: case 'string': $type = SQLVARCHAR; break.
- Problem with mssql driver in php5 (for adodb 5.03) because some functions are not static. Fixed.

## 5.02 - 24 Sept 2007

Released together with [v4.96](changelog_v4.x.md#496---24-sept-2007)

- ADOdb perf for oci8 now has non-table-locking code when clearing the sql. Slower but better transparency. Added in 4.96a and 5.02a.
- Fix adodb count optimisation. Preg_match did not work properly. Also rewrote the ORDER BY stripping code in _adodb_getcount(), adodb-lib.inc.php.
- SelectLimit for oci8 not optimal for large recordsets when offset=0. Changed $nrows check.
- Active record optimizations. Added support for assoc arrays in Set().
- Now GetOne returns null if EOF (no records found), and false if error occurs. Use ErrorMsg()/ErrorNo() to get the error.
- Also CacheGetRow and CacheGetCol will return false if error occurs, or empty array() if EOF, just like GetRow and GetCol.
- Datadict now allows changing of types which are not resizable, eg. VARCHAR to TEXT in ChangeTableSQL. -- Mateo TibaquirÃ¡
- Added BIT data type support to adodb-ado.inc.php and adodb-ado5.inc.php.
- Ldap driver did not return actual ldap error messages. Fixed.
- Implemented GetRandRow($sql, $inputarr). Optimized for Oci8.
- Changed adodb5 active record to use static SetDatabaseAdapter() and removed php4 constructor. Bas van Beek bas.vanbeek#gmail.com.
- Also in adodb5, changed adodb-session2 to use static function declarations in class. Thx Daniel Berlin.
- Added "Clear SQL Log" to bottom of Performance screen.
- Sessions2 code echo'ed directly to the screen in debug mode. Now uses ADOConnection::outp().
- In mysql/mysqli, qstr(null) will return the string `null` instead of empty quoted string `''`.
- postgresql optimizeTable in perf-postgres.inc.php added by Daniel Berlin (mail#daniel-berlin.de)
- Added 5.2.1 compat code for oci8.
- Changed @@identity to SCOPE_IDENTITY() for multiple mssql drivers. Thx Stefano Nari.
- Code sanitization introduced in 4.95 caused problems in European locales (as float 3.2 was typecast to 3,2). Now we only sanitize if is_numeric fails.
- Added support for customizing ADORecordset_empty using $this->rsPrefix.'empty'. By Josh Truwin.
- Added proper support for ALterColumnSQL for Postgresql in datadict code. Thx. Josh Truwin.
- Added better support for MetaType() in mysqli when using an array recordset.
- Changed parser for pgsql error messages in adodb-error.inc.php to case-insensitive regex.

## 5.01 - 17 May 2007

Released together with [v4.95](changelog_v4.x.md#495---17-may-2007)

- CacheFlush debug outp() passed in invalid parameters. Fixed.
- Added Thai language file for adodb. Thx Trirat Petchsingh rosskouk#gmail.com and Marcos Pont
- Added zerofill checking support to MetaColumns for mysql and mysqli.
- CacheFlush no longer deletes all files/directories. Only *.cache files deleted.
- DB2 timestamp format changed to `var $fmtTimeStamp = "'Y-m-d-H:i:s'";`
- Added some code sanitization to AutoExecute in adodb-lib.inc.php.
- Due to typo, all connections in adodb-oracle.inc.php would become persistent, even non-persistent ones. Fixed.
- Oci8 DBTimeStamp uses 24 hour time for input now, so you can perform string comparisons between 2 DBTimeStamp values.
- Some PHP4.4 compat issues fixed in adodb-session2.inc.php
- For ADOdb 5.01, fixed some adodb-datadict.inc.php MetaType compat issues with PHP5.
- The $argHostname was wiped out in adodb-ado5.inc.php. Fixed.
- Adodb5 version, added iterator support for adodb_recordset_empty.
- Adodb5 version,more error checking code now will use exceptions if available.
