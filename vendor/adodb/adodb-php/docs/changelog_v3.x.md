# ADOdb old Changelog - v3.x

See the [Current Changelog](changelog.md).
Older changelogs:
[v2.x](changelog_v2.x.md).


## 3.94 - 11 Oct 2003

- Create trigger in datadict-oci8.inc.php did not work, because all cr/lf's must be removed.
- ErrorMsg()/ErrorNo() did not work for many databases when logging enabled. Fixed.
- Removed global variable $ADODB_LOGSQL as it does not work properly with multiple connections.
- Added SQLDate support for sybase. Thx to Chris Phillipson
- Postgresql checking of pgsql resultset resource was incorrect. Fix by Bharat Mediratta bharat#menalto.com. Same patch applied to _insertid and _affectedrows for adodb-postgres64.inc.php.
- Added support for NConnect for postgresql.
- Added Sybase data dict support. Thx to Chris Phillipson
- Extensive improvements in $perf->UI(), eg. Explain now opens in new window, we show scripts which call sql, etc.
- Perf Monitor UI works with magic quotes enabled.
- rsPrefix was declared twice. Removed.
- Oci8 stored procedure support, eg. "begin func(); end;" was incorrect in _query. Fixed.
- Tiraboschi Massimiliano contributed italian language file.
- Fernando Ortiz, fortiz#lacorona.com.mx, contributed informix performance monitor.
- Added _varchar (varchar arrays) support for postgresql. Reported by PREVOT Stéphane.


## 3.92 - 22 Sept 2003

- Added GetAssoc and CacheGetAssoc to connection object.
- Removed TextMax and CharMax functions from adodb.inc.php.
- HasFailedTrans() returned false when trans failed. Fixed.
- Moved perf driver classes into adodb/perf/*.php.
- Misc improvements to performance monitoring, including UI().
- RETVAL in mssql Parameter(), we do not append @ now.
- Added Param($name) to connection class, returns '?' or ":$name", for defining bind parameters portably.
- LogSQL traps affected_rows() and saves its value properly now. Also fixed oci8 _stmt and _affectedrows() bugs.
- Session code timestamp check for oci8 works now. Formerly default NLS_DATE_FORMAT stripped off time portion. Thx to Tony Blair (tonanbarbarian#hotmail.com). Also added new $conn->datetime field to oci8, controls whether MetaType() returns 'D' ($this->datetime==false) or 'T' ($this->datetime == true) for DATE type.
- Fixed bugs in adodb-cryptsession.inc.php and adodb-session-clob.inc.php.
- Fixed misc bugs in adodb_key_exists, GetInsertSQL() and GetUpdateSQL().
- Tuned include_once handling to reduce file-system checking overhead.

## 3.91 - 9 Sept 2003

- Only released to InterAkt
- Added LogSQL() for sql logging and $ADODB_NEWCONNECTION to override factory for driver instantiation.
- Added IfNull($field,$ifNull) function, thx to johnwilk#juno.com
- Added portable substr support.
- Now rs2html() has new parameter, $echo. Set to false to return $html instead of echoing it.

## 3.90 - 5 Sept 2003

- First beta of performance monitoring released.
- MySQL supports MetaTable() masking.
- Fixed key_exists() bug in adodb-lib.inc.php
- Added sp_executesql Prepare() support to mssql.
- Added bind support to db2.
- Added swedish language file - Christian Tiberg" christian#commsoft.nu
- Bug in drop index for mssql data dict fixed. Thx to Gert-Rainer Bitterlich.
- Left join setting for oci8 was wrong. Thx to johnwilk#juno.com

## 3.80 - 27 Aug 2003

- Patch for PHP 4.3.3 cached recordset csv2rs() fread loop incompatibility.
- Added matching mask for MetaTables. Only for oci8, mssql and postgres currently.
- Rewrite of "oracle" driver connection code, merging with "oci8", by Gaetano.
- Added better debugging for Smart Transactions.
- Postgres DBTimeStamp() was wrongly using TO_DATE. Changed to TO_TIMESTAMP.
- ADODB_FETCH_CASE check pushed to ADONewConnection to allow people to define it after including adodb.inc.php.
- Added portugese (brazilian) to languages. Thx to "Levi Fukumori".
- Removed arg3 parameter from Execute/SelectLimit/Cache* functions.
- Execute() now accepts 2-d array as $inputarray. Also changed docs of fnExecute() to note change in sql query counting with 2-d arrays.
- Added MONEY to MetaType in PostgreSQL.
- Added more debugging output to CacheFlush().

## 3.72 - 9 Aug 2003

- Added qmagic($str), which is a qstr($str) that auto-checks for magic quotes and does the right thing...
- Fixed CacheFlush() bug - Thx to martin#gmx.de
- Walt Boring contributed MetaForeignKeys for postgres7.
- _fetch() called _BlobDecode() wrongly in interbase. Fixed.
- adodb_time bug fixed with dates after 2038 fixed by Jason Pell. http://phplens.com/lens/lensforum/msgs.php?id=6980

## 3.71 - 4 Aug 2003

- The oci8 driver, MetaPrimaryKeys() did not check the owner correctly when $owner == false.
- Russian language file contributed by "Cyrill Malevanov" cyrill#malevanov.spb.ru.
- Spanish language file contributed by "Horacio Degiorgi" horaciod#codigophp.com.
- Error handling in oci8 bugfix - if there was an error in Execute(), then when calling ErrorNo() and/or ErrorMsg(), the 1st call would return the error, but the 2nd call would return no error.
- Error handling in odbc bugfix. ODBC would always return the last error, even if it happened 5 queries ago. Now we reset the errormsg to '' and errorno to 0 everytime before CacheExecute() and Execute().

## 3.70 - 29 July 2003

- Added new SQLite driver. Tested on PHP 4.3 and PHP 5.
- Added limited "sapdb" driver support - mainly date support.
- The oci8 driver did not identify NUMBER with no defined precision correctly.
- Added ADODB_FORCE_NULLS, if set, then PHP nulls are converted to SQL nulls in GetInsertSQL/GetUpdateSQL.
- DBDate() and DBTimeStamp() format for postgresql had problems. Fixed.
- Added tableoptions to ChangeTableSQL(). Thx to Mike Benoit.
- Added charset support to postgresql. Thx to Julian Tarkhanov.
- Changed OS check for MS-Windows to prevent confusion with darWIN (MacOS)
- Timestamp format for db2 was wrong. Changed to yyyy-mm-dd-hh.mm.ss.nnnnnn.
- adodb-cryptsession.php includes wrong. Fixed.
- Added MetaForeignKeys(). Supported by mssql, odbc_mssql and oci8.
- Fixed some oci8 MetaColumns/MetaPrimaryKeys bugs. Thx to Walt Boring.
- adodb_getcount() did not init qryRecs to 0\. Missing "WHERE" clause checking in GetUpdateSQL fixed. Thx to Sebastiaan van Stijn.
- Added support for only 'VIEWS' and "TABLES" in MetaTables. From Walt Boring.
- Upgraded to adodb-xmlschema.inc.php 0.0.2.
- NConnect for mysql now returns value. Thx to Dennis Verspuij.
- ADODB_FETCH_BOTH support added to interbase/firebird.
- Czech language file contributed by Kamil Jakubovic jake#host.sk.
- PostgreSQL BlobDecode did not use _connectionID properly. Thx to Juraj Chlebec.
- Added some new initialization stuff for Informix. Thx to "Andrea Pinnisi" pinnisi#sysnet.it
- ADODB_ASSOC_CASE constant wrong in sybase _fetch(). Fixed.

## 3.60 - 16 June 2003

- We now SET CONCAT_NULL_YIELDS_NULL OFF for odbc_mssql driver to be compat with mssql driver.
- The property $emptyDate missing from connection class. Also changed 1903 to constant (TIMESTAMP_FIRST_YEAR=100). Thx to Sebastiaan van Stijn.
- ADOdb speedup optimization - we now return all arrays by reference.
- Now DBDate() and DBTimeStamp() now accepts the string 'null' as a parameter. Suggested by vincent.
- Added GetArray() to connection class.
- Added not_null check in informix metacolumns().
- Connection parameters for postgresql did not work correctly when port was defined.
- DB2 is now a tested driver, making adodb 100% compatible. Extensive changes to odbc driver for DB2, including implementing serverinfo() and SQLDate(), switching to SQL_CUR_USE_ODBC as the cursor mode, and lastAffectedRows and SelectLimit() fixes.
- The odbc driver's FetchField() field names did not obey ADODB_ASSOC_CASE. Fixed.
- Some bugs in adodb_backtrace() fixed.
- Added "INT IDENTITY" type to adorecordset::MetaType() to support odbc_mssql properly.
- MetaColumns() for oci8, mssql, odbc revised to support scale. Also minor revisions to odbc MetaColumns() for vfp and db2 compat.
- Added unsigned support to mysql datadict class. Thx to iamsure.
- Infinite loop in mssql MoveNext() fixed when ADODB_FETCH_ASSOC used. Thx to Josh R, Night_Wulfe#hotmail.com.
- ChangeTableSQL contributed by Florian Buzin.
- The odbc_mssql driver now sets CONCAT_NULL_YIELDS_NULL OFF for compat with mssql driver.

## 3.50 - 19 May 2003

- Fixed mssql compat with FreeTDS. FreeTDS does not implement mssql_fetch_assoc().
- Merged back connection and recordset code into adodb.inc.php.
- ADOdb sessions using oracle clobs contributed by achim.gosse#ddd.de. See adodb-session-clob.php.
- Added /s modifier to preg_match everywhere, which ensures that regex does not stop at /n. Thx Pao-Hsi Huang.
- Fixed error in metacolumns() for mssql.
- Added time format support for SQLDate.
- Image => B added to metatype.
- MetaType now checks empty($this->blobSize) instead of empty($this).
- Datadict has beta support for informix, sybase (mapped to mssql), db2 and generic (which is a fudge).
- BlobEncode for postgresql uses pg_escape_bytea, if available. Needed for compat with 7.3.
- Added $ADODB_LANG, to support multiple languages in MetaErrorMsg().
- Datadict can now parse table definition as declarative text.
- For DataDict, oci8 autoincrement trigger missing semi-colon. Fixed.
- For DataDict, when REPLACE flag enabled, drop sequence in datadict for autoincrement field in postgres and oci8.s
- Postgresql defaults to template1 database if no database defined in connect/pconnect.
- We now clear _resultid in postgresql if query fails.

## 3.40 - 19 May 2003

- Added insert_id for odbc_mssql.
- Modified postgresql UpdateBlobFile() because it did not work in safe mode.
- Now connection object is passed to raiseErrorFn as last parameter. Needed by StartTrans().
- Added StartTrans() and CompleteTrans(). It is recommended that you do not modify transOff, but use the above functions.
- oci8po now obeys ADODB_ASSOC_CASE settings.
- Added virtualized error codes, using PEAR DB equivalents. Requires you to manually include adodb-error.inc.php yourself, with MetaError() and MetaErrorMsg($errno).
- GetRowAssoc for mysql and pgsql were flawed. Fix by Ross Smith.
- Added to datadict types I1, I2, I4 and I8\. Changed datadict type 'T' to map to timestamp instead of datetime for postgresql.
- Error handling in ExecuteSQLArray(), adodb-datadict.inc.php did not work.
- We now auto-quote postgresql connection parameters when building connection string.
- Added session expiry notification.
- We now test with odbc mysql - made some changes to odbc recordset constructor.
- MetaColumns now special cases access and other databases for odbc.

## 3.31 - 17 March 2003

- Added row checking for _fetch in postgres.
- Added Interval type to MetaType for postgres.
- Remapped postgres driver to call postgres7 driver internally.
- Adorecordset_array::getarray() did not return array when nRows >= 0.
- Postgresql: at times, no error message returned by pg_result_error() but error message returned in pg_last_error(). Recoded again.
- Interbase blob's now use chunking for updateblob.
- Move() did not set EOF correctly. Reported by Jorma T.
- We properly support mysql timestamp fields when we are creating mysql tables using the data-dict interface.
- Table regex includes backticks character now.

## 3.30 - 3 March 2003

- Added $ADODB_EXTENSION and $ADODB_COMPAT_FETCH constant.
- Made blank1stItem configurable using syntax "value:text" in GetMenu/GetMenu2. Thx to Gabriel Birke.
- Previously ADOdb differed from the Microsoft standard because it did not define what to set $this->fields when EOF was reached. Now at EOF, ADOdb sets $this->fields to false for all databases, which is consist with Microsoft's implementation. Postgresql and mysql have always worked this way (in 3.11 and earlier). If you are experiencing compatibility problems (and you are not using postgresql nor mysql) on upgrading to 3.30, try setting the global variables $ADODB_COUNTRECS = true (which is the default) and $ADODB_FETCH_COMPAT = true (this is a new global variable).
- We now check both pg_result_error and pg_last_error as sometimes pg_result_error does not display anything. Iman Mayes
- We no longer check for magic quotes gpc in Quote().
- Misc fixes for table creation in adodb-datadict.inc.php. Thx to iamsure.
- Time calculations use adodb_time library for all negative timestamps due to problems in Red Hat 7.3 or later. Formerly, only did this for Windows.
- In mssqlpo, we now check if $sql in _query is a string before we change || to +. This is to support prepared stmts.
- Move() and MoveLast() internals changed to support to support EOF and $this->fields change.
- Added ADODB_FETCH_BOTH support to mssql. Thx to Angel Fradejas afradejas#mediafusion.es
- We now check if link resource exists before we run mysql_escape_string in qstr().
- Before we flock in csv code, we check that it is not a http url.

## 3.20 - 17 Feb 2003

- Added new Data Dictionary classes for creating tables and indexes. Warning - this is very much alpha quality code. The API can still change. See adodb/tests/test-datadict.php for more info.
- We now ignore $ADODB_COUNTRECS for mysql, because PHP truncates incomplete recordsets when mysql_unbuffered_query() is called a second time.
- Now postgresql works correctly when $ADODB_COUNTRECS = false.
- Changed _adodb_getcount to properly support SELECT DISTINCT.
- Discovered that $ADODB_COUNTRECS=true has some problems with prepared queries - suspect PHP bug.
- Now GetOne and GetRow run in $ADODB_COUNTRECS=false mode for better performance.
- Added support for mysql_real_escape_string() and pg_escape_string() in qstr().
- Added an intermediate variable for mysql _fetch() and MoveNext() to store fields, to prevent overwriting field array with boolean when mysql_fetch_array() returns false.
- Made arrays for getinsertsql and getupdatesql case-insensitive. Suggested by Tim Uckun" tim#diligence.com

## 3.11 - 11 Feb 2003

- Added check for ADODB_NEVER_PERSIST constant in PConnect(). If defined, then PConnect() will actually call non-persistent Connect().
- Modified interbase to properly work with Prepare().
- Added $this->ibase_timefmt to allow you to change the date and time format.
- Added support for $input_array parameter in CacheFlush().
- Added experimental support for dbx, which was then removed when i found that it was slower than using native calls.
- Added MetaPrimaryKeys for mssql and ibase/firebird.
- Added new $trim parameter to GetCol and CacheGetCol
- Uses updated adodb-time.inc.php 0.06.

## 3.10 - 27 Jan 2003

- Added adodb_date(), adodb_getdate(), adodb_mktime() and adodb-time.inc.php.
- For interbase, added code to handle unlimited number of bind parameters. From Daniel Hasan daniel#hasan.cl.
- Added BlobDecode and UpdateBlob for informix. Thx to Fernando Ortiz.
- Added constant ADODB_WINDOWS. If defined, means that running on Windows.
- Added constant ADODB_PHPVER which stores php version as a hex num. Removed $ADODB_PHPVER variable.
- Felho Bacsi reported a minor white-space regular expression problem in GetInsertSQL.
- Modified ADO to use variant to store _affectedRows
- Changed ibase to use base class Replace(). Modified base class Replace() to support ibase.
- Changed odbc to auto-detect when 0 records returned is wrong due to bad odbc drivers.
- Changed mssql to use datetimeconvert ini setting only when 4.30 or later (does not work in 4.23).
- ExecuteCursor($stmt, $cursorname, $params) now accepts a new $params array of additional bind parameters -- William Lovaton walovaton#yahoo.com.mx.
- Added support for sybase_unbuffered_query if ADODB_COUNTRECS == false. Thx to chuck may.
- Fixed FetchNextObj() bug. Thx to Jorma Tuomainen.
- We now use SCOPE_IDENTITY() instead of @@IDENTITY for mssql - thx to marchesini#eside.it
- Changed postgresql movenext logic to prevent illegal row number from being passed to pg_fetch_array().
- Postgresql initrs bug found by "Bogdan RIPA" bripa#interakt.ro $f1 accidentally named $f

## 3.00 - 6 Jan 2003

- Fixed adodb-pear.inc.php syntax error.
- Improved _adodb_getcount() to use SELECT COUNT(*) FROM ($sql) for languages that accept it.
- Fixed _adodb_getcount() caching error.
- Added sql to retrive table and column info for odbc_mssql.
