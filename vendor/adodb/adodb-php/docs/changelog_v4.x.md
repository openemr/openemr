# ADOdb old Changelog - v4.x

See the [Current Changelog](changelog.md).
Older changelogs:
[v3.x](changelog_v3.x.md),
[v2.x](changelog_v2.x.md).


## 4.990 - 11 Jul 2008

- Added support for multiple recordsets in mysqli Geisel Sierote <geisel#4up.com.br>. See http://phplens.com/lens/lensforum/msgs.php?id=15917
- Malcolm Cook added new Reload() function to Active Record. See http://phplens.com/lens/lensforum/msgs.php?id=17474
- Thanks Zoltan Monori [monzol#fotoprizma.hu] for bug fixes in iterator, SelectLimit, GetRandRow, etc.
- Under heavy loads, the performance monitor for oci8 disables Ixora views.
- Fixed sybase driver SQLDate to use str_replace(). Also for adodb5, changed sybase driver UnixDate and UnixTimeStamp calls to static.
- Changed oci8 lob handler to use &amp; reference $this-&gt;_refLOBs[$numlob]['VAR'] = &amp;$var.
- We now strtolower the get_class() function in PEAR::isError() for php5 compat.
- CacheExecute did not retrieve cache recordsets properly for 5.04 (worked in 4.98). Fixed.
- New ADODB_Cache_File class for file caching defined in adodb.inc.php.
- Farsi language file contribution by Peyman Hooshmandi Raad (phooshmand#gmail.com)
- New API for creating your custom caching class which is stored in $ADODB_CACHE:
  ```
include "/path/to/adodb.inc.php";
$ADODB_CACHE_CLASS = 'MyCacheClass';

class MyCacheClass extends ADODB_Cache_File
{
	function writecache($filename, $contents,$debug=false){...}
	function &readcache($filename, &$err, $secs2cache, $rsClass){ ...}
		:
}

$DB = NewADOConnection($driver);
$DB->Connect(...);  ## MyCacheClass created here and stored in $ADODB_CACHE global variable.

$data = $rs->CacheGetOne($sql); ## MyCacheClass is used here for caching...
```
- Memcache supports multiple pooled hosts now. Only if none of the pooled servers can be contacted will a connect error be generated. Usage example below:
  ```
$db = NewADOConnection($driver);
$db->memCache = true; /// should we use memCache instead of caching in files
$db->memCacheHost = array($ip1, $ip2, $ip3); /// $db->memCacheHost = $ip1; still works
$db->memCachePort = 11211; /// this is default memCache port
$db->memCacheCompress = false; /// Use 'true' to store the item compressed (uses zlib)

$db->Connect(...);
$db->CacheExecute($sql);
```

## 4.98 - 13 Feb 2008

- Fixed adodb_mktime problem which causes a performance bottleneck in $hrs.
- Added mysqli support to adodb_getcount().
- Removed MYSQLI_TYPE_CHAR from MetaType().

## 4.97 - 22 Jan 2008

- Active Record: $ADODB_ASSOC_CASE=1 did not work properly. Fixed.
- Modified Fields() in recordset class to support display null fields in FetchNextObject().
- In ADOdb5, active record implementation, we now support column names with spaces in them - we autoconvert the spaces to `_` using `__set()`. Thx Daniel Cook. http://phplens.com/lens/lensforum/msgs.php?id=17200
- Removed $arg3 from mysqli SelectLimit. See http://phplens.com/lens/lensforum/msgs.php?id=16243. Thx Zsolt Szeberenyi.
- Changed oci8 FetchField, which returns the max_length of BLOB/CLOB/NCLOB as 4000 (incorrectly) to -1.
- CacheExecute would sometimes return an error on Windows if it was unable to lock the cache file. This is harmless and has been changed to a warning that can be ignored. Also adodb_write_file() code revised.
- ADOdb perf code changed to only log sql if execution time &gt;= 0.05 seconds. New $ADODB_PERF_MIN variable holds min sql timing. Any SQL with timing value below this and is not causing an error is not logged.
- Also adodb_backtrace() now traces 1 level deeper as sometimes actual culprit function is not displayed.
- Fixed a group by problem with adodb_getcount() for db's which are not postgres/oci8 based.
- Changed mssql driver Parameter() from SQLCHAR to SQLVARCHAR: case 'string': $type = SQLVARCHAR; break.
- Problem with mssql driver in php5 (for adodb 5.03) because some functions are not static. Fixed.

## 4.96 - 24 Sept 2007

- ADOdb perf for oci8 now has non-table-locking code when clearing the sql. Slower but better transparency. Added in 4.96a and 5.02a.
- Fix adodb count optimisation. Preg_match did not work properly. Also rewrote the ORDER BY stripping code in _adodb_getcount(), adodb-lib.inc.php.
- SelectLimit for oci8 not optimal for large recordsets when offset=0. Changed $nrows check.
- Active record optimizations. Added support for assoc arrays in Set().
- Now GetOne returns null if EOF (no records found), and false if error occurs. Use ErrorMsg()/ErrorNo() to get the error.
- Also CacheGetRow and CacheGetCol will return false if error occurs, or empty array() if EOF, just like GetRow and GetCol.
- Datadict now allows changing of types which are not resizable, eg. VARCHAR to TEXT in ChangeTableSQL. -- Mateo Tibaquirá
- Added BIT data type support to adodb-ado.inc.php and adodb-ado5.inc.php.
- Ldap driver did not return actual ldap error messages. Fixed.
- Implemented GetRandRow($sql, $inputarr). Optimized for Oci8.
- Changed adodb5 active record to use static SetDatabaseAdapter() and removed php4 constructor. Bas van Beek bas.vanbeek#gmail.com.
- Also in adodb5, changed adodb-session2 to use static function declarations in class. Thx Daniel Berlin.
- Added "Clear SQL Log" to bottom of Performance screen.
- Sessions2 code echo'ed directly to the screen in debug mode. Now uses ADOConnection::outp().
- In mysql/mysqli, qstr(null) will return the string "null" instead of empty quoted string "''".
- postgresql optimizeTable in perf-postgres.inc.php added by Daniel Berlin (mail#daniel-berlin.de)
- Added 5.2.1 compat code for oci8.
- Changed @@identity to SCOPE_IDENTITY() for multiple mssql drivers. Thx Stefano Nari.
- Code sanitization introduced in 4.95 caused problems in European locales (as float 3.2 was typecast to 3,2). Now we only sanitize if is_numeric fails.
- Added support for customizing ADORecordset_empty using $this-&gt;rsPrefix.'empty'. By Josh Truwin.
- Added proper support for ALterColumnSQL for Postgresql in datadict code. Thx. Josh Truwin.
- Added better support for MetaType() in mysqli when using an array recordset.
- Changed parser for pgsql error messages in adodb-error.inc.php to case-insensitive regex.

## 4.95 - 17 May 2007

- CacheFlush debug outp() passed in invalid parameters. Fixed.
- Added Thai language file for adodb. Thx Trirat Petchsingh rosskouk#gmail.com and Marcos Pont
- Added zerofill checking support to MetaColumns for mysql and mysqli.
- CacheFlush no longer deletes all files directories. Only `*.cache` files deleted.
- DB2 timestamp format changed to var $fmtTimeStamp = 'Y-m-d-H:i:s';
- Added some code sanitization to AutoExecute in adodb-lib.inc.php.
- Due to typo, all connections in adodb-oracle.inc.php would become persistent, even non-persistent ones. Fixed.
- Oci8 DBTimeStamp uses 24 hour time for input now, so you can perform string comparisons between 2 DBTimeStamp values.
- Some PHP4.4 compat issues fixed in adodb-session2.inc.php
- For ADOdb 5.01, fixed some adodb-datadict.inc.php MetaType compat issues with PHP5.
- The $argHostname was wiped out in adodb-ado5.inc.php. Fixed.
- Adodb5 version, added iterator support for adodb_recordset_empty.
- Adodb5 version,more error checking code now will use exceptions if available.

## 4.94 - 23 Jan 2007

- Active Record: $ADODB_ASSOC_CASE=2 did not work properly. Fixed. Thx gmane#auxbuss.com.
- mysqli had bugs in BeginTrans() and EndTrans(). Fixed.
- Improved error handling when no database is connected for oci8. Thx Andy Hassall.
- Names longer than 30 chars in oci8 datadict will be changed to random name. Thx Eugenio. http://phplens.com/lens/lensforum/msgs.php?id=16182
- Added var $upperCase = 'ucase' to access and ado_access drivers. Thx Renato De Giovanni renato#cria.org.br
- Postgres64 driver, if preparing plan failed in _query, did not handle error properly. Fixed. See http://phplens.com/lens/lensforum/msgs.php?id=16131.
- Fixed GetActiveRecordsClass() reference bug. See http://phplens.com/lens/lensforum/msgs.php?id=16120
- Added handling of nulls in adodb-ado_mssql.inc.php for qstr(). Thx to Felix Rabinovich.
- Adodb-dict contributions by Gaetano
    - Support for INDEX in data-dict. Example: idx_ev1. The ability to define indexes using the INDEX keyword was added in ADOdb 4.94. The following example features mutiple indexes, including a compound index idx_ev1.

        ```
        event_id I(11) NOTNULL AUTOINCREMENT PRIMARY,
        event_type I(4) NOTNULL
        event_start_date T DEFAULT NULL **INDEX id_esd**,
        event_end_date T DEFAULT '0000-00-00 00:00:00' **INDEX id_eted**,
        event_parent I(11) UNSIGNED NOTNULL DEFAULT 0 **INDEX id_evp**,
        event_owner I(11) DEFAULT 0 **INDEX idx_ev1**,
        event_project I(11) DEFAULT 0 **INDEX idx_ev1**,
        event_times_recuring I(11) UNSIGNED NOTNULL DEFAULT 0,
        event_icon C(20) DEFAULT 'obj/event',
        event_description X
        ```

  - Prevents the generated SQL from including double drop-sequence statements for REPLACE case of tables with autoincrement columns (on those dbs that emulate it via sequences)
  - makes any date defined as DEFAULT value for D and T columns work cross-database, not just the "sysdate" value (as long as it is specified using adodb standard format). See above example.
- Fixed pdo's GetInsertID() support. Thx Ricky Su.
- oci8 Prepare() now sets error messages if an error occurs.
- Added 'PT_BR' to SetDateLocale() -- brazilian portugese.
- charset in oci8 was not set correctly on `*Connect()`
- ADOConnection::Transpose() now appends as first column the field names.
- Added $ADODB_QUOTE_FIELDNAMES. If set to true, will autoquote field names in AutoExecute(),GetInsertSQL(), GetUpdateSQL().
- Transpose now adds the field names as the first column after transposition.
- Added === check in ADODB_SetDatabaseAdapter for $db, adodb-active-record.inc.php. Thx Christian Affolter.
- Added ErrorNo() to adodb-active-record.inc.php. Thx ante#novisplet.com.

## 4.93 - 10 Oct 2006

- Added support for multiple database connections in performance monitoring code (adodb-perf.inc.php). Now all sql in multiple database connections can be saved into one database ($ADODB_LOG_CONN).
- Added MetaIndexes() to odbc_mssql.
- Added connection property $db->null2null = 'null'. In autoexecute/getinsertsql/getupdatesql, this value will be converted to a null. Set this to a funny invalid value if you do not want null conversion. See http://phplens.com/lens/lensforum/msgs.php?id=15902.
- Path disclosure problem in mysqli fixed. Thx Andy.
- Fixed typo in session_schema2.xml.
- Changed INT in oci8 to return correct precision in $fld->max_length, MetaColumns(). Thx Eloy Lafuente Plaza.
- Patched postgres64 _connect to handle serverinfo(). see http://phplens.com/lens/lensforum/msgs.php?id=15887.
- Added pdo fix for null columns. See http://phplens.com/lens/lensforum/msgs.php?id=15889
- For stored procedures, missing connection id now passed into mssql_query(). Thx Ecsy (ecsy#freemail.hu).

## 4.92a - 30 Aug 2006

- Syntax error in postgres7 driver. Thx Eloy Lafuente Plaza.
- Minor bug fixes - adodb informix 10 types added to adodb.inc.php. Thx Fernando Ortiz.

## 4.92 - 29 Aug 2006

- Better odbtp date support.
- Added IgnoreErrors() to bypass default error handling.
- The _adodb_getcount() function in adodb-lib.inc.php, some ORDER BY bug fixes.
- For ibase and firebird, set $sysTimeStamp = "CURRENT_TIMESTAMP".
- Fixed postgres connection bug: http://phplens.com/lens/lensforum/msgs.php?id=11057.
- Changed CacheSelectLimit() to flush cache when $secs2cache==-1 due to complaints from other users.
- Added support for using memcached with CacheExecute/CacheSelectLimit. Requires memcache module PECL extension. Usage:
    ```
$db = NewADOConnection($driver);
$db->memCache = true; /// should we use memCache instead of caching in files
$db->memCacheHost = "126.0.1.1"; /// memCache host
$db->memCachePort = 11211; /// this is default memCache port
$db->memCacheCompress = false; /// Use 'true' to store the item compressed (uses zlib)
$db->Connect(...);
$db->CacheExecute($sql);
```

- Implemented Transpose() for recordsets. Recordset must be retrieved using ADODB_FETCH_NUM. First column becomes the column name.
    ```
$db = NewADOConnection('mysql');
$db->Connect(...);
$db->SetFetchMode(ADODB_FETCH_NUM);
$rs = $db->Execute('select productname,productid,unitprice from products limit 10');
$rs2 = $db->Transpose($rs);
rs2html($rs2);
```

## 4.91 - 2 Aug 2006

- Major session code rewrite... See session docs.
- PDO bindinputarray() was not set properly for MySQL (changed from true to false).
- Changed CacheSelectLimit() to re-cache when $secs2cache==0. This is one way to flush the cache when SelectLimit is called.
- Added to quotes to mysql and mysqli: "SHOW COLUMNS FROM \`%s\`";
- Removed accidental optgroup handling in GetMenu(). Fixed ibase _BlobDecode for php5 compat, and also mem alloc issues for small blobs, thx salvatori#interia.pl
- Mysql driver OffsetDate() speedup, useful for adodb-sessions.
- Fix for GetAssoc() PHP5 compat. See http://phplens.com/lens/lensforum/msgs.php?id=15425
- Active Record - If inserting a record and the value of a primary key field is null, then we do not insert that field in as we assume it is an auto-increment field. Needed by mssql.
- Changed postgres7 MetaForeignKeys() see http://phplens.com/lens/lensforum/msgs.php?id=15531
- DB2 will now return db2_conn_errormsg() when it is a connection error.

## 4.90 - 8 June 2006

- Changed adodb_countrec() in adodb-lib.inc.php to allow LIMIT to be used as a speedup to reduce no of records counted.
- Added support for transaction modes for postgres and oci8 with SetTransactionMode(). These transaction modes affect all subsequent transactions of that connection.
- Thanks to Halmai Csongor for suggestion.
- Removed `$off = $fieldOffset - 1` line in db2 driver, FetchField(). Tx Larry Menard.
- Added support for PHP5 objects as Execute() bind parameters using `__toString` (eg. Simple-XML). Thx Carl-Christian Salvesen.
- Rounding in tohtml.inc.php did not work properly. Fixed.
- MetaIndexes in postgres fails when fields are deleted then added in again because the attnum has gaps in it. See http://sourceforge.net/tracker/index.php?func=detail&aid=1451245&group_id=42718&atid=433976. Fixed.
- MetaForeignkeys in mysql and mysqli did not work when fetchMode==ADODB_FETCH_ASSOC used. Fixed.
- Reference error in AutoExecute() fixed.
- Added macaddr postgres type to MetaType. Maps to 'C'.
- Added to `_connect()` in adodb-ado5.inc.php support for $database and $dataProvider parameters. Thx Larry Menard.
- Added support for sequences in adodb-ado_mssql.inc.php. Thx Larry Menard.
- Added ADODB_SESSION_READONLY.
- Added session expiryref support to crc32 mode, and in LOB code.
- Clear `_errorMsg` in postgres7 driver, so that ErrorMsg() displays properly when no error occurs.
- Added BindDate and BindTimeStamp

## 4.81 - 3 May 2006

- Fixed variable ref errors in adodb-ado5.inc.php in _query().
- Mysqli setcharset fix using method_exists().
- The adodb-perf.inc.php CreateLogTable() code now works for user-defined table names.
- Error in ibase_blob_open() fixed. See http://phplens.com/lens/lensforum/msgs.php?id=14997

## 4.80 - 8 Mar 2006

- Added activerecord support.
- Added mysql `$conn->compat323 = true` if you want MySQL 3.23 compat enabled. Fixes GetOne() Select-Limit problems.
- Added adodb-xmlschema03.inc.php to support XML Schema version 3 and updated adodb-datadict.htm docs.
- Better memory management in Execute. Thx Mike Fedyk.

## 4.72 - 21 Feb 2006

- Added 'new' DSN parameter for NConnect().
- Pager now sanitizes $PHP_SELF to protect against XSS. Thx to James Bercegay and others.
- ADOConnection::MetaType changed to setup $rs->connection correctly.
- New native DB2 driver contributed by Larry Menard, Dan Scott, Andy Staudacher, Bharat Mediratta.
- The mssql CreateSequence() did not BEGIN TRANSACTION correctly. Fixed. Thx Sean Lee.
- The _adodb_countrecs() function in adodb-lib.inc.php has been revised to handle more ORDER BY variations.

## 4.71 - 24 Jan 2006

- Fixes postgresql security issue related to binary strings. Thx to Andy Staudacher.
- Several DSN bugs found:
  1. Fix bugs in DSN connections introduced in 4.70 when underscores are found in the DSN.
  2. DSN with _ did not work properly in PHP5 (fine in PHP4). Fixed.
  3. Added support for PDO DSN connections in NewADOConnection(), and database parameter in PDO::Connect().
- The oci8 datetime flag not correctly implemented in ADORecordSet_array. Fixed.
- Added BlobDelete() to postgres, as a counterpoint to UpdateBlobFile().
- Fixed GetInsertSQL() to support oci8po.
- Fixed qstr() issue with postgresql with \0 in strings.
- Fixed some datadict driver loading issues in _adodb_getdriver().
- Added register shutdown function session_write_close in adodb-session.inc.php for PHP 5 compat. See http://phplens.com/lens/lensforum/msgs.php?id=14200.

## 4.70 - 6 Jan 2006

- Many fixes from Danila Ulyanov to ibase, oci8, postgres, mssql, odbc_oracle, odbtp, etc drivers.
- Changed usage of binary hint in adodb-session.inc.php for mysql. See http://phplens.com/lens/lensforum/msgs.php?id=14160
- Fixed invalid variable reference problem in undomq(), adodb-perf.inc.php.
- Fixed http://phplens.com/lens/lensforum/msgs.php?id=14254 in adodb-perf.inc.php, `_DBParameter()` settings of fetchmode was wrong.
- Fixed security issues in server.php and tmssql.php discussed by Andreas Sandblad in a Secunia security advisory. Added `$ACCEPTIP = 127.0.0.1` and changed suggested root password to something more secure.
- Changed pager to close recordset after RenderLayout().

## 4.68 - 25 Nov 2005

- PHP 5 compat for mysqli. MetaForeignKeys repeated twice and MYSQLI_BINARY_FLAG missing.
- PHP 5.1 support for postgresql bind parameters using ? did not work if >= 10 parameters. Fixed. Thx to Stanislav Shramko.
- Lots of PDO improvements.
- Spelling error fixed in mysql MetaForeignKeys, $associative parameter.

## 4.67 - 16 Nov 2005

- Postgresql not_null flag not set to false correctly. Thx Cristian MARIN.
- We now check in Replace() if key is in fieldArray. Thx Sébastien Vanvelthem.
- `_file_get_contents()` function was missing in xmlschema. fixed.
- Added week in year support to SQLDate(), using 'W' flag. Thx Spider.
- In sqlite metacolumns was repeated twice, causing PHP 5 problems. Fixed.
- Made debug output XHTML compliant.

## 4.66 - 28 Sept 2005

- ExecuteCursor() in oci8 did not clean up properly on failure. Fixed.
- Updated xmlschema.dtd, by "Alec Smecher" asmecher#smecher.bc.ca
- Hardened SelectLimit, typecasting nrows and offset to integer.
- Fixed misc bugs in AutoExecute() and GetInsertSQL().
- Added $conn->database as the property holding the database name. The older $conn->databaseName is retained for backward compat.
- Changed _adodb_backtrace() compat check to use function_exists().
- Bug in postgresql MetaIndexes fixed. Thx Kevin Jamieson.
- Improved OffsetDate for MySQL, reducing rounding error.
- Metacolumns added to sqlite. Thx Mark Newnham.
- PHP 4.4 compat fixes for GetAssoc().
- Added postgresql bind support for php 5.1. Thx Cristiano da Cunha Duarte
- OffsetDate() fixes for postgresql, typecasting strings to date or timestamp.
- DBTimeStamp formats for mssql, odbc_mssql and postgresql made to conform with other db's.
- Changed PDO constants from PDO_ to PDO:: to support latest spec.

## 4.65 - 22 July 2005

- Reverted 'X' in mssql datadict to 'TEXT' to be compat with mssql driver. However now you can set $datadict->typeX = 'varchar(4000)' or 'TEXT' or 'CLOB' for mssql and oci8 drivers.
- Added charset support when using DSN for Oracle.
- _adodb_getmenu did not use fieldcount() to get number of fields. Fixed.
- MetaForeignKeys() for mysql/mysqli contributed by Juan Carlos Gonzalez.
- MetaDatabases() now correctly returns an array for mysqli driver. Thx Cristian MARIN.
- CompleteTrans(false) did not return false. Fixed. Thx to JMF.
- AutoExecute() did not work with Oracle. Fixed. Thx José Moreira.
- MetaType() added to connection object.
- More PHP 4.4 reference return fixes. Thx Ryan C Bonham and others.

## 4.64 - 20 June 2005

- In datadict, if the default field value is set to '', then it is not applied when the field is created. Fixed by Eugenio.
- MetaPrimaryKeys for postgres did not work because of true/false change in 4.63. Fixed.
- Tested ocifetchstatement in oci8. Rejected at the end.
- Added port to dsn handling. Supported in postgres, mysql, mysqli,ldap.
- Added 'w' and 'l' to mysqli SQLDate().
- Fixed error handling in ldap _connect() to be more consistent. Also added ErrorMsg() handling to ldap.
- Added support for union in _adodb_getcount, adodb-lib.inc.php for postgres and oci8.
- rs2html() did not work with null dates properly.
- PHP 4.4 reference return fixes.

## 4.63 - 18 May 2005

- Added $nrows<0 check to mysqli's SelectLimit().
- Added OptimizeTable() and OptimizeTables() in adodb-perf.inc.php. By Markus Staab.
- PostgreSQL inconsistencies fixed. true and false set to TRUE and FALSE, and boolean type in datadict-postgres.inc.php set to 'L' => 'BOOLEAN'. Thx Kevin Jamieson.
- New adodb_session_create_table() function in adodb-session.inc.php. By Markus Staab.
- Added null check to UserTimeStamp().
- Fixed typo in mysqlt driver in adorecordset. Thx to Andy Staudacher.
- GenID() had a bug in the raiseErrorFn handling. Fixed. Thx Marcos Pont.
- Datadict name quoting now handles ( ) in index fields correctly - they aren't part of the index field.
- Performance monitoring:
    1. oci8 Ixora checks moved down;
    2. expensive sql changed so that only those sql with count(*)>1 are shown;
    3. changed sql1 field to a length+crc32 checksum - this breaks backward compat.
- We remap firebird15 to firebird in data dictionary.

## 4.62 - 2 Apr 2005

- Added 'w' (dow as 0-6 or 1-7) and 'l' (dow as string) for SQLDate for oci8, postgres and mysql.
- Rolled back MetaType() changes for mysqli done in prev version.
- Datadict change by chris, cblin#tennaxia.com data mappings from:

    ```
oci8:  X->varchar(4000) XL->CLOB
mssql: X->XL->TEXT
mysql: X->XL->LONGTEXT
fbird: X->XL->varchar(4000)
```
  to:
    ```
oci8:  X->varchar(4000) XL->CLOB
mssql: X->VARCHAR(4000) XL->TEXT
mysql: X->TEXT          XL->LONGTEXT
fbird: X->VARCHAR(4000) XL->VARCHAR(32000)
```
- Added $connection->disableBlobs to postgresql to improve performance when no bytea is used (2-5% improvement).
- Removed all HTTP_* vars.
- Added $rs->tableName to be set before calling AutoExecute().
- Alex Rootoff rootoff#pisem.net contributed ukrainian language file.
- Added new mysql_option() support using $conn->optionFlags array.
- Added support for ldap_set_option() using the $LDAP_CONNECT_OPTIONS global variable. Contributed by Josh Eldridge.
- Added LDAP_* constant definitions to ldap.
- Added support for boolean bind variables. We use $conn->false and $conn->true to hold values to set false/true to.
- We now do not close the session connection in adodb-session.inc.php as other objects could be using this connection.
- We now strip off `\0` at end of Ixora SQL strings in $perf->tohtml() for oci8.

## 4.61 - 23 Feb 2005

- MySQLi added support for mysqli_connect_errno() and mysqli_connect_error().
- Massive improvements to alpha PDO driver.
- Quote string bind parameters logged by performance monitor for easy type checking. Thx Jason Judge.
- Added support for $role when connecting with Interbase/firebird.
- Added support for enum recognition in MetaColumns() mysql and mysqli. Thx Amedeo Petrella.
- The sybase_ase driver contributed by Interakt Online. Thx Cristian Marin cristic#interaktonline.com.
- Removed not_null, has_default, and default_value from ADOFieldObject.
- Sessions code, fixed quoting of keys when handling LOBs in session write() function.
- Sessions code, added adodb_session_regenerate_id(), to reduce risk of session hijacking by changing session cookie dynamically. Thx Joe Li.
- Perf monitor, polling for CPU did not work for PHP 4.3.10 and 5.0.0-5.0.3 due to PHP bugs, so we special case these versions.
- Postgresql, UpdateBlob() added code to handle type==CLOB.

## 4.60 - 24 Jan 2005

- Implemented PEAR DB's autoExecute(). Simplified design because I don't like using constants when strings work fine.
- _rs2serialize will now update $rs->sql and $rs->oldProvider.
- Added autoExecute().
- Added support for postgres8 driver. Currently just remapped to postgres7 driver.
- Changed oci8 _query(), so that OCIBindByName() sets the length to -1 if element size is > 4000. This provides better support for LONGs.
- Added SetDateLocale() support for netherlands (Nl).
- Spelling error in pivot code ($iff should be $iif).
- mysql insert_id() did not work with mysql 3.x. Fixed.
- `\r\n` not converted to spaces correctly in exporting data. Fixed.
- _nconnect() in mysqli did not return value correctly. Fixed.
- Arne Eckmann contributed danish language file.
- Added clone() support to FetchObject() for PHP5.
- Removed SQL_CUR_USE_ODBC from odbc_mssql.

## 4.55 - 5 Jan 2005

- Found bug in Execute() with bind params for db's that do not support binding natively.
- DropSequence() now correctly uses default parameter.
- Now Execute() ignores locale for floats, so 1.23 is NEVER converted to 1,23.
- SetFetchMode() not properly saved in adodb-perf, suspicious sql and expensive sql. Fixed.
- Added INET to postgresql metatypes. Thx motzel.
- Allow oracle hints to work when counting with _adodb_getcount in adodb-lib.inc.php. Thx Chris Wrye.
- Changed mysql insert_id() to use SELECT LAST_INSERT_ID().
- If alter col in datadict does not modify col type/size of actual col, then it is removed from alter col code. By Mark Newham. Not perfect as MetaType() !== ActualType().
- Added handling of view fields in metacolumns() for postgresql. Thx Renato De Giovanni.
- Added to informix MetaPrimaryKeys and MetaColumns fixes for null bit. Thx to Cecilio Albero.
- Removed obsolete connection_timeout() from perf code.
- Added support for arrayClass in adodb-csv.inc.php.
- RSFilter now accepts methods of the form $array($obj, 'methodname'). Thx to blake#near-time.com.
- Changed CacheFlush to `$cmd = 'rm -rf '.$ADODB_CACHE_DIR.'/[0-9a-f][0-9a-f]/';`
- For better cursor concurrency, added code to free ref cursors in oci8 when $rs->Close() is called. Note that CLose() is called internally by the Get* functions too.
- Added IIF support for access when pivoting. Thx Volodia Krupach.
- Added mssql datadict support for timestamp. Thx Alexios.
- Informix pager fix. By Mario Ramirez.
- ADODB_TABLE_REGEX now includes ':'. By Mario Ramirez.
- Mark Newnham contributed MetaIndexes for oci8 and db2.

## 4.54 - 5 Nov 2004

- Now you can set $db->charSet = ?? before doing a Connect() in oci8.
- Added adodbFetchMode to sqlite.
- Perf code, added a string typecast to substr in adodb_log_sql().
- Postgres: Changed BlobDecode() to use po_loread, added new $maxblobsize parameter, and now it returns the blob instead of sending it to stdout - make sure to mention that as a compat warning. Also added $db->IsOID($oid) function; uses a heuristic, not guaranteed to work 100%.
- Contributed arabic language file by "El-Shamaa, Khaled" k.el-shamaa#cgiar.org
- PHP5 exceptions did not handle @ protocol properly. Fixed.
- Added ifnull handling for postgresql (using coalesce).
- Added metatables() support for Postgresql 8.0 (no longer uses pg_% dictionary tables).
- Improved Sybase ErrorMsg() function. By Gaetano Giunta.
- Improved oci8 SelectLimit() to use Prepare(). By Cristiano Duarte.
- Type-cast $row parameter in ifx_fetch_row() to int. Thx stefan bodgan.
- Ralf becker contributed improvements in postgresql, sapdb, mysql data dictionary handling:
  - MySql and Postgres MetaType was reporting every int column which was part of a primary key and unique as serial
  - Postgres was not reporting the scale of decimal types
  - MaxDB was padding the defaults of none-string types with spaces
  - MySql now correctly converts enum columns to varchar
- Ralf also changed Postgresql datadict:
  - you cant add NOT NULL columns in postgres in one go, they need to be added as NULL and then altered to NOT NULL
  - AlterColumnSQL could not change a varchar column with numbers into an integer column, postgres need an explicit conversation
  - a re-created sequence was not set to the correct value, if the name was the old name (no implicit sequence), now always the new name of the implicit sequence is used
- Sergio Strampelli added extra $intoken check to Lens_ParseArgs() in datadict code.

## 4.53 - 28 Sept 2004

- FetchMode cached in recordset is sometimes mapped to native db fetchMode. Normally this does not matter, but when using cached recordsets, we need to switch back to using adodb fetchmode. So we cache this in $rs->adodbFetchMode if it differs from the db's fetchMode.
- For informix we now set canSeek = false driver because stefan bodgan tells me that seeking doesn't work.
- SetDateLocale() never worked till now ;-) Thx david#tomato.it
- Set $_bindInputArray = true in sapdb driver. Required for clob support.
- Fixed some PEAR::DB emulation issues with isError() and isWarning. Thx to Gert-Rainer Bitterlich.
- Empty() used in getupdatesql without strlen() check. Fixed.
- Added unsigned detection to mysql and mysqli drivers. Thx to dan cech.
- Added hungarian language file. Thx to Halászvári Gábor.
- Improved fieldname-type formatting of datadict SQL generated (adding $widespacing parameter to _GenField).
- Datadict oci8 DROP CONSTRAINTS misspelt. Fixed. Thx Mark Newnham.
- Changed odbtp to dynamically change databaseType based on connection, eg. from 'odbtp' to 'odbtp_mssql' when connecting to mssql database.
- In datadict, MySQL I4 was wrongly mapped to MEDIUMINT, which is actually I3. Fixed.
- Fixed mysqli MetaType() recognition. Mysqli returns numeric types unlike mysql extension. Thx Francesco Riosa.
- VFP odbc driver curmode set wrongly, causing problems with memo fields. Fixed.
- Odbc driver did not recognize odbc version 2 driver date types properly. Fixed. Thx Bostjan.
- ChangeTableSQL() fixes to datadict-db2.inc.php by Mark Newnham.
- Perf monitoring with odbc improved. Now we try in perf code to manually set the sysTimeStamp using date() if sysTimeStamp is empty.
- All ADO errors are thrown as exceptions in PHP5. So we added exception handling to ado in PHP5 by creating new adodb-ado5.inc.php driver.
- Added IsConnected(). Returns true if connection object connected. By Luca.Gioppo.
- "Ralf Becker" RalfBecker#digitalROCK.de contributed new sapdb data-dictionary driver and a large patch that implements field and table renaming for oracle, mssql, postgresql, mysql and sapdb. See the new RenameTableSQL() and RenameColumnSQL() functions.
- We now check ExecuteCursor to see if PrepareSP was initially called.
- Changed oci8 datadict to use MODIFY for $dd->alterCol. Thx Mark Newnham.

## 4.52 - 10 Aug 2004

- Bug found in Replace() when performance logging enabled, introduced in ADOdb 4.50. Fixed.
- Replace() checks update stmt. If update stmt fails, we now return immediately. Thx to alex.
- Added support for $ADODB_FORCE_TYPE in GetUpdateSQL/GetInsertSQL. Thx to niko.
- Added ADODB_ASSOC_CASE support to postgres/postgres7 driver.
- Support for DECLARE stmt in oci8. Thx Lochbrunner.

## 4.51 - 29 July 2004

- Added adodb-xmlschema 1.0.2. Thx dan and richard.
- Added new adorecordset_ext_* classes. If ADOdb extension installed for mysql, mysqlt and oci8 (but not oci8po), we use the superfast ADOdb extension code for movenext.
- Added schema support to mssql and odbc_mssql MetaPrimaryKeys().
- Patched MSSQL driver to support PHP NULL and Boolean values while binding the input array parameters in the _query() function. By Stephen Farmer.
- Added support for clob's for mssql, UpdateBlob(). Thx to gfran#directa.com.br
- Added normalize support for postgresql (true=lowercase table name, or false=case-sensitive table names) to MetaColumns($table, $normalize=true).
- PHP5 variant dates in ADO not working. Fixed in adodb-ado.inc.php.
- Constant ADODB_FORCE_NULLS was not working properly for many releases (for GetUpdateSQL). Fixed. Also GetUpdateSQL strips off ORDER BY now - thx Elieser Leão.
- Perf Monitor for oci8 now dynamically highlights optimizer_* params if too high/low.
- Added dsn support to NewADOConnection/ADONewConnection.
- Fixed out of page bounds bug in _adodb_pageexecute_all_rows() Thx to "Sergio Strampelli" sergio#rir.it
- Speedup of movenext for mysql and oci8 drivers.
- Moved debugging code _adodb_debug_execute() to adodb-lib.inc.php.
- Fixed postgresql bytea detection bug. See http://phplens.com/lens/lensforum/msgs.php?id=9849.
- Fixed ibase datetimestamp typo in PHP5. Thx stefan.
- Removed whitespace at end of odbtp drivers.
- Added db2 metaprimarykeys fix.
- Optimizations to MoveNext() for mysql and oci8. Misc speedups to Get* functions.

## 4.50 - 6 July 2004

- Bumped it to 4.50 to avoid confusion with PHP 4.3.x series.
- Added db2 metatables and metacolumns extensions.
- Added alpha PDO driver. Very buggy, only works with odbc.
- Tested mysqli. Set poorAffectedRows = true. Cleaned up movenext() and _fetch().
- PageExecute does not work properly with php5 (return val not a variable). Reported Dmytro Sychevsky sych#php.com.ua. Fixed.
- MetaTables() for mysql, $showschema parameter was not backward compatible with older versions of adodb. Fixed.
- Changed mysql GetOne() to work with mysql 3.23 when using with non-select stmts (e.g. SHOW TABLES).
- Changed TRIG_ prefix to a variable in datadict-oci8.inc.php. Thx to Luca.Gioppo#csi.it.
- New to adodb-time code. We allow you to define your own daylights savings function, adodb_daylight_sv for pre-1970 dates. If the function is defined (somewhere in an include), then you can correct for daylights savings. See http://phplens.com/phpeverywhere/node/view/16#daylightsavings for more info.
- New sqlitepo driver. This is because assoc mode does not work like other drivers in sqlite. Namely, when selecting (joining) multiple tables, in assoc mode the table names are included in the assoc keys in the "sqlite" driver. In "sqlitepo" driver, the table names are stripped from the returned column names. When this results in a conflict, the first field get preference. Contributed by Herman Kuiper herman#ozuzo.net
- Added $forcenull parameter to GetInsertSQL/GetUpdateSQL. Idea by Marco Aurelio Silva.
- More XHTML changes for GetMenu. By Jeremy Evans.
- Fixes some ibase date issues. Thx to stefan bogdan.
- Improvements to mysqli driver to support $ADODB_COUNTRECS.
- Fixed adodb-csvlib.inc.php problem when reading stream from socket. We need to poll stream continiously.

## 4.23 - 16 June 2004

- New interbase/firebird fixes thx to Lester Caine. Driver fixes a problem with getting field names in the result array, and corrects a couple of data conversions. Also we default to dialect3 for firebird. Also ibase sysDate property was wrong. Changed to cast as timestamp.
- The datadict driver is set up to give quoted tables and fields as this was the only way round reserved words being used as field names in TikiWiki. TikiPro is tidying that up, and I hope to be able to produce a build of THAT which uses what I consider proper UPPERCASE field and table names. The conversion of TikiWiki to ADOdb helped in that, but until the database is completely tidied up in TikiPro ...
- Modified _gencachename() to include fetchmode in name hash. This means you should clear your cache directory after installing this release as the cache name algorithm has changed.
- Now Cache* functions work in safe mode, because we do not create sub-directories in the $ADODB_CACHE_DIR in safe mode. In non-safe mode we still create sub-directories. Done by modifying _gencachename().
- Added $gmt parameter (true/false) to UserDate and UserTimeStamp in connection class, to force conversion of input (in local time) to be converted to UTC/GMT.
- Mssql datadict did not support INT types properly (no size param allowed). Added _GetSize() to datadict-mssql.inc.php.
- For borland_ibase, BeginTrans(), changed:

    ```
$this->_transactionID = $this->_connectionID;
```

  to

    ```
$this->_transactionID = ibase_trans($this->ibasetrans, $this->_connectionID);
```

- Fixed typo in mysqi_field_seek(). Thx to Sh4dow (sh4dow#php.pl).
- LogSQL did not work with Firebird/Interbase. Fixed.
- Postgres: made errorno() handling more consistent. Thx to Michael Jahn, Michael.Jahn#mailbox.tu-dresden.de.
- Added informix patch to better support metatables, metacolumns by "Cecilio Albero" c-albero#eos-i.com
- Cyril Malevanov contributed patch to oci8 to support passing of LOB parameters:

    ```
$text = 'test test test';
$sql = "declare rs clob; begin :rs := lobinout(:sa0); end;";
$stmt = $conn -> PrepareSP($sql);
$conn -> InParameter($stmt,$text,'sa0', -1, OCI_B_CLOB);
$rs = '';
$conn -> OutParameter($stmt,$rs,'rs', -1, OCI_B_CLOB);
$conn -> Execute($stmt);
echo "return = ".$rs."<br>";</pre>
```

  As he says, the LOBs limitations are:
  - use OCINewDescriptor before binding
  - if Param is IN, uses save() before each execute. This is done automatically for you.
  - if Param is OUT, uses load() after each execute. This is done automatically for you.
  - when we bind $var as LOB, we create new descriptor and return it as a Bind Result, so if we want to use OUT parameters, we have to store somewhere &$var to load() data from LOB to it.
  - IN OUT params are not working now (should not be a big problem to fix it)
  - now mass binding not working too (I've wrote about it before)</pre>
- Simplified Connect() and PConnect() error handling.
- When extension not loaded, Connect() and PConnect() will return null. On connect error, the fns will return false.
- CacheGetArray() added to code.
- Added Init() to adorecordset_empty().
- Changed postgres64 driver, MetaColumns() to not strip off quotes in default value if :: detected (type-casting of default).
- Added test: if (!defined('ADODB_DIR')) die(). Useful to prevent hackers from detecting file paths.
- Changed metaTablesSQL to ignore Postgres 7.4 information schemas (sql_*).
- New polish language file by Grzegorz Pacan
- Added support for UNION in _adodb_getcount().
- Added security check for ADODB_DIR to limit path disclosure issues. Requested by postnuke team.
- Added better error message support to oracle driver. Thx to Gaetano Giunta.
- Added showSchema support to mysql.
- Bind in oci8 did not handle $name=false properly. Fixed.
- If extension not loaded, Connect(), PConnect(), NConnect() will return null.

## 4.22 - 15 Apr 2004

- Moved docs to own adodb/docs folder.
- Fixed session bug when quoting compressed/encrypted data in Replace().
- Netezza Driver and LDAP drivers contributed by Josh Eldridge.
- GetMenu now uses rtrim() on values instead of trim().
- Changed MetaColumnNames to return an associative array, keys being the field names in uppercase.
- Suggested fix to adodb-ado.inc.php affected_rows to support PHP5 variants. Thx to Alexios Fakos.
- Contributed bulgarian language file by Valentin Sheiretsky valio#valio.eu.org.
- Contributed romanian language file by stefan bogdan.
- GetInsertSQL now checks for table name (string) in $rs, and will create a recordset for that table automatically. Contributed by Walt Boring. Also added OCI_B_BLOB in bind on Walt's request - hope it doesn't break anything :-)
- Some minor postgres speedups in `_initrs()`.
- ChangeTableSQL checks now if MetaColumns returns empty. Thx Jason Judge.
- Added ADOConnection::Time(), returns current database time in unix timestamp format, or false.

## 4.21 - 20 Mar 2004

- We no longer in SelectLimit for VFP driver add SELECT TOP X unless an ORDER BY exists.
- Pim Koeman contributed dutch language file adodb-nl.inc.php.
- Rick Hickerson added CLOB support to db2 datadict.
- Added odbtp driver. Thx to "stefan bogdan" sbogdan#rsb.ro.
- Changed PrepareSP() 2nd parameter, $cursor, to default to true (formerly false). Fixes oci8 backward compat problems with OUT params.
- Fixed month calculation error in adodb-time.inc.php. 2102-June-01 appeared as 2102-May-32.
- Updated PHP5 RC1 iterator support. API changed, hasMore() renamed to valid().
- Changed internal format of serialized cache recordsets. As we store a version number, this should be backward compatible.
- Error handling when driver file not found was flawed in ADOLoadCode(). Fixed.

## 4.20 - 27 Feb 2004

- Updated to AXMLS 1.01.
- MetaForeignKeys for postgres7 modified by Edward Jaramilla, works on pg 7.4.
- Now numbers accepts function calls or sequences for GetInsertSQL/GetUpdateSQL numeric fields.
- Changed quotes of 'delete from $perf_table' to "". Thx Kehui (webmaster#kehui.net)
- Added ServerInfo() for ifx, and putenv trim fix. Thx Fernando Ortiz.
- Added addq(), which is analogous to addslashes().
- Tested with php5b4. Fix some php5 compat problems with exceptions and sybase.
- Carl-Christian Salvesen added patch to mssql _query to support binds greater than 4000 chars.
- Mike suggested patch to PHP5 exception handler. $errno must be numeric.
- Added double quotes (") to ADODB_TABLE_REGEX.
- For oci8, Prepare(...,$cursor), $cursor's meaning was accidentally inverted in 4.11. This causes problems with ExecuteCursor() too, which calls Prepare() internally. Thx to William Lovaton.
- Now dateHasTime property in connection object renamed to datetime for consistency. This could break bc.
- Csongor Halmai reports that db2 SelectLimit with input array is not working. Fixed..

## 4.11 - 27 Jan 2004

- Csongor Halmai reports db2 binding not working. Reverted back to emulated binding.
- Dan Cech modifies datadict code. Adds support for DropIndex. Minor cleanups.
- Table misspelt in perf-oci8.inc.php. Changed v$conn_cache_advice to v$db_cache_advice. Reported by Steve W.
- UserTimeStamp and DBTimeStamp did not handle YYYYMMDDHHMMSS format properly. Reported by Mike Muir. Fixed.
- Changed oci8 Prepare(). Does not auto-allocate OCINewCursor automatically, unless 2nd param is set to true. This will break backward compat, if Prepare/Execute is used instead of ExecuteCursor. Reported by Chris Jones.
- Added InParameter() and OutParameter(). Wrapper functions to Parameter(), but nicer because they are self-documenting.
- Added 'R' handling in ActualType() to datadict-mysql.inc.php
- Added ADOConnection::SerializableRS($rs). Returns a recordset that can be serialized in a session.
- Added "Run SQL" to performance UI().
- Misc spelling corrections in adodb-mysqli.inc.php, adodb-oci8.inc.php and datadict-oci8.inc.php, from Heinz Hombergs.
- MetaIndexes() for ibase contributed by Heinz Hombergs.

## 4.10 - 12 Jan 2004

- Dan Cech contributed extensive changes to data dictionary to support name quoting (with `\``), and drop table/index.
- Informix added cursorType property. Default remains IFX_SCROLL, but you can change to 0 (non-scrollable cursor) for performance.
- Added ADODB_View_PrimaryKeys() for returning view primary keys to MetaPrimaryKeys().
- Simplified chinese file, adodb-cn.inc.php from cysoft.
- Added check for ctype_alnum in adodb-datadict.inc.php. Thx to Jason Judge.
- Added connection parameter to ibase Prepare(). Fix by Daniel Hassan.
- Added nameQuote for quoting identifiers and names to connection obj. Requested by Jason Judge. Also the data dictionary parser now detects `field name` and generates column names with spaces correctly.
- BOOL type not recognised correctly as L. Fixed.
- Fixed paths in ADODB_DIR for session files, and back-ported it to 4.05 (15 Dec 2003)
- Added Schema to postgresql MetaTables. Thx to col#gear.hu
- Empty postgresql recordsets that had blob fields did not set EOF properly. Fixed.
- CacheSelectLimit internal parameters to SelectLimit were wrong. Thx to Nio.
- Modified adodb_pr() and adodb_backtrace() to support command-line usage (eg. no html).
- Fixed some fr and it lang errors. Thx to Gaetano G.
- Added contrib directory, with adodb rs to xmlrpc convertor by Gaetano G.
- Fixed array recordset bugs when `_skiprow1` is true. Thx to Gaetano G.
- Fixed pivot table code when count is false.

## 4.05 - 13 Dec 2003

- Added MetaIndexes to data-dict code - thx to Dan Cech.
- Rewritten session code by Ross Smith. Moved code to adodb/session directory.
- Added function exists check on connecting to most drivers, so we don't crash with the unknown function error.
- Smart Transactions failed with GenID() when it no seq table has been created because the sql statement fails. Fix by Mark Newnham.
- Added $db->length, which holds name of function that returns strlen.
- Fixed error handling for bad driver in ADONewConnection - passed too few params to error-handler.
- Datadict did not handle types like 16.0 properly in _GetSize. Fixed.
- Oci8 driver SelectLimit() bug &= instead of =& used. Thx to Swen Thümmler.
- Jesse Mullan suggested not flushing outp when output buffering enabled. Due to Apache 2.0 bug. Added.
- MetaTables/MetaColumns return ref bug with PHP5 fixed in adodb-datadict.inc.php.
- New mysqli driver contributed by Arjen de Rijke. Based on adodb 3.40 driver. Then jlim added BeginTrans, CommitTrans, RollbackTrans, IfNull, SQLDate. Also fixed return ref bug.
- $ADODB_FLUSH added, if true then force flush in debugging outp. Default is false. In earlier versions, outp defaulted to flush, which is not compat with apache 2.0.
- Mysql driver's GenID() function did not work when when sql logging is on. Fixed.
- $ADODB_SESSION_TBL not declared as global var. Not available if adodb-session.inc.php included in function. Fixed.
- The input array not passed to Execute() in _adodb_getcount(). Fixed.

## 4.04 - 13 Nov 2003

- Switched back to foreach - faster than list-each.
- Fixed bug in ado driver - wiping out $this->fields with date fields.
- Performance Monitor, View SQL, Explain Plan did not work if strlen($SQL)>max($_GET length). Fixed.
- Performance monitor, oci8 driver added memory sort ratio.
- Added random property, returns SQL to generate a floating point number between 0 and 1;

## 4.03 - 6 Nov 2003

- The path to adodb-php4.inc.php and adodb-iterators.inc.php was not setup properly.
- Patched SQLDate in interbase to support hours/mins/secs. Thx to ari kuorikoski.
- Force autorollback for pgsql persistent connections - apparently pgsql did not autorollback properly before 4.3.4. See http://bugs.php.net/bug.php?id=25404

## 4.02 - 5 Nov 2003

- Some errors in adodb_error_pg() fixed. Thx to Styve.
- Spurious Insert_ID() error was generated by LogSQL(). Fixed.
- Insert_ID was interfering with Affected_Rows() and Replace() when LogSQL() enabled. Fixed.
- More foreach loops optimized with list/each.
- Null dates not handled properly in ADO driver (it becomes 31 Dec 1969!).
- Heinz Hombergs contributed patches for mysql MetaColumns - adding scale, made interbase MetaColumns work with firebird/interbase, and added lang/adodb-de.inc.php.
- Added INFORMIXSERVER environment variable.
- Added $ADODB_ANSI_PADDING_OFF for interbase/firebird.
- PHP 5 beta 2 compat check. Foreach (Iterator) support. Exceptions support.

## 4.01 - 23 Oct 2003

- Fixed bug in rs2html(), tohtml.inc.php, that generated blank table cells.
- Fixed insert_id() incorrectly generated when logsql() enabled.
- Modified PostgreSQL _fixblobs to use list/each instead of foreach.
- Informix ErrorNo() implemented correctly.
- Modified several places to use list/each, including GetRowAssoc().
- Added UserTimeStamp() to connection class.
- Added $ADODB_ANSI_PADDING_OFF for oci8po.

## 4.00 - 20 Oct 2003

- Upgraded adodb-xmlschema to 1 Oct 2003 snapshot.
- Fix to rs2html warning message. Thx to Filo.
- Fix for odbc_mssql/mssql SQLDate(), hours was wrong.
- Added MetaColumns and MetaPrimaryKeys for sybase. Thx to Chris Phillipson.
- Added autoquoting to datadict for MySQL and PostgreSQL. Suggestion by Karsten Dambekalns
