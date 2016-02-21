## 4.990/5.05 - 11 Jul 2008

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

## 4.98/5.04 - 13 Feb 2008

- Fixed adodb_mktime problem which causes a performance bottleneck in $hrs.
- Added mysqli support to adodb_getcount().
- Removed MYSQLI_TYPE_CHAR from MetaType().

## 4.97/5.03 - 22 Jan 2008

- Active Record: $ADODB_ASSOC_CASE=1 did not work properly. Fixed.
- Modified Fields() in recordset class to support display null fields in FetchNextObject().
- In ADOdb5, active record implementation, we now support column names with spaces in them - we autoconvert the spaces to _ using __set(). Thx Daniel Cook. http://phplens.com/lens/lensforum/msgs.php?id=17200
- Removed $arg3 from mysqli SelectLimit. See http://phplens.com/lens/lensforum/msgs.php?id=16243. Thx Zsolt Szeberenyi.
- Changed oci8 FetchField, which returns the max_length of BLOB/CLOB/NCLOB as 4000 (incorrectly) to -1.
- CacheExecute would sometimes return an error on Windows if it was unable to lock the cache file. This is harmless and has been changed to a warning that can be ignored. Also adodb_write_file() code revised.
- ADOdb perf code changed to only log sql if execution time &gt;= 0.05 seconds. New $ADODB_PERF_MIN variable holds min sql timing. Any SQL with timing value below this and is not causing an error is not logged.
- Also adodb_backtrace() now traces 1 level deeper as sometimes actual culprit function is not displayed.
- Fixed a group by problem with adodb_getcount() for db's which are not postgres/oci8 based.
- Changed mssql driver Parameter() from SQLCHAR to SQLVARCHAR: case 'string': $type = SQLVARCHAR; break.
- Problem with mssql driver in php5 (for adodb 5.03) because some functions are not static. Fixed.

## 4.96/5.02 - 24 Sept 2007

ADOdb perf for oci8 now has non-table-locking code when clearing the sql. Slower but better transparency. Added in 4.96a and 5.02a.
Fix adodb count optimisation. Preg_match did not work properly. Also rewrote the ORDER BY stripping code in _adodb_getcount(), adodb-lib.inc.php.
SelectLimit for oci8 not optimal for large recordsets when offset=0. Changed $nrows check.
Active record optimizations. Added support for assoc arrays in Set().
Now GetOne returns null if EOF (no records found), and false if error occurs. Use ErrorMsg()/ErrorNo() to get the error.
Also CacheGetRow and CacheGetCol will return false if error occurs, or empty array() if EOF, just like GetRow and GetCol.
Datadict now allows changing of types which are not resizable, eg. VARCHAR to TEXT in ChangeTableSQL. -- Mateo Tibaquirá
Added BIT data type support to adodb-ado.inc.php and adodb-ado5.inc.php.
Ldap driver did not return actual ldap error messages. Fixed.
Implemented GetRandRow($sql, $inputarr). Optimized for Oci8.
Changed adodb5 active record to use static SetDatabaseAdapter() and removed php4 constructor. Bas van Beek bas.vanbeek#gmail.com.
Also in adodb5, changed adodb-session2 to use static function declarations in class. Thx Daniel Berlin.
Added "Clear SQL Log" to bottom of Performance screen.
Sessions2 code echo'ed directly to the screen in debug mode. Now uses ADOConnection::outp().
In mysql/mysqli, qstr(null) will return the string "null" instead of empty quoted string "''".
postgresql optimizeTable in perf-postgres.inc.php added by Daniel Berlin (mail#daniel-berlin.de)
Added 5.2.1 compat code for oci8.
Changed @@identity to SCOPE_IDENTITY() for multiple mssql drivers. Thx Stefano Nari.
Code sanitization introduced in 4.95 caused problems in European locales (as float 3.2 was typecast to 3,2). Now we only sanitize if is_numeric fails.
Added support for customizing ADORecordset_empty using $this-&gt;rsPrefix.'empty'. By Josh Truwin.
Added proper support for ALterColumnSQL for Postgresql in datadict code. Thx. Josh Truwin.
Added better support for MetaType() in mysqli when using an array recordset.
Changed parser for pgsql error messages in adodb-error.inc.php to case-insensitive regex.

## 4.95/5.01 - 17 May 2007

CacheFlush debug outp() passed in invalid parameters. Fixed.
Added Thai language file for adodb. Thx Trirat Petchsingh rosskouk#gmail.com
and Marcos Pont
Added zerofill checking support to MetaColumns for mysql and mysqli.
CacheFlush no longer deletes all files/directories. Only *.cache files
deleted.
DB2 timestamp format changed to var $fmtTimeStamp =
&quot;'Y-m-d-H:i:s'&quot;;
Added some code sanitization to AutoExecute in adodb-lib.inc.php.
Due to typo, all connections in adodb-oracle.inc.php would become
persistent, even non-persistent ones. Fixed.
Oci8 DBTimeStamp uses 24 hour time for input now, so you can perform string
comparisons between 2 DBTimeStamp values.
Some PHP4.4 compat issues fixed in adodb-session2.inc.php
For ADOdb 5.01, fixed some adodb-datadict.inc.php MetaType compat issues
with PHP5.
The $argHostname was wiped out in adodb-ado5.inc.php. Fixed.
Adodb5 version, added iterator support for adodb_recordset_empty.
Adodb5 version,more error checking code now will use exceptions if
available.
