<?php

$sl_conn = 0; // connection object
$sl_err = ""; // global error message

function SLConnect() {
  global $sl_conn, $sl_dbname, $sl_dbuser, $sl_dbpass;
  $sl_host = $GLOBALS['oer_config']['ws_accounting']['server'];
  $sl_conn = pg_pconnect("host=$sl_host dbname=$sl_dbname user=$sl_dbuser password=$sl_dbpass");
  if (!$sl_conn) die("Failed to connect to SQL-Ledger database.");
}

function SLClose() {
  global $sl_conn;
  if ($sl_conn) pg_close($sl_conn);
}

function SLQuery($query) {
  global $sl_conn, $sl_err;
  $sl_err = "";
  $res = pg_exec($sl_conn, $query);
  if (!$res || pg_numrows($res) < 0) {
    $sl_err = pg_errormessage($sl_conn) . ": $query";
    if (! $sl_err) $sl_err = "Query failed:" + $query;
  }
  return $res;
}

function SLRowCount($res) {
  return pg_numrows($res);
}

function SLAffectedCount($res) {
  return pg_affected_rows($res);
}

function SLGetRow($res, $rownum) {
  return pg_fetch_array($res, $rownum, PGSQL_ASSOC);
}

function SLQueryValue($query) {
  $res = SLQuery($query);
  if (! $sl_err && SLRowCount($res) > 0) {
    $tmp = pg_fetch_array($res, 0);
    return $tmp[0];
  }
  return "";
}

function SLFreeResult($res) {
  pg_freeresult($res);
}
?>
