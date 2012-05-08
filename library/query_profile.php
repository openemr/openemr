<?php
include_once(dirname(__FILE__) . "/sqlconf.php");

// Sigh, globals.
$__debug_query_log = [];

function shouldLogQueries() {
  // Count on this getting set in the site's sqlconf.php
  // But really, you could set it anywhere.
  return $GLOBALS["query_debug"];
}

function debugLogQuery($statement) {
  if (!shouldLogQueries()) {
    return;
  }
  $stack_trace = array_reverse(debug_backtrace());

  // Trim this function call from the stack trace, it's not useful.
  $stack_trace = array_slice($stack_trace, 0, count($stack_trace) - 1);
  array_push($GLOBALS["__debug_query_log"],
             array("query" => $statement,
                   "stack_trace" => $stack_trace));
}

function formatQuery($logged_query) {
  $traceback_message = "";
  foreach ($logged_query["stack_trace"] as $stack_frame) {
    $traceback_message .= "  " . $stack_frame["file"] . ":" .
      $stack_frame['line'] . " " .
      $stack_frame['function'] . "\n";
    }
  $message = "\n" . "  " . $logged_query["query"] . "\n" . $traceback_message;
  return $message;
}

function formatProfile() {
  $profile = "";
  $query_log = $GLOBALS["__debug_query_log"];
  foreach($query_log as $logged_query) {
    $message = formatQuery($logged_query);
    $profile .= $message;
  }
  $profile .= "\n" . count($query_log) . " total database queries.";
  return $profile;
}

function loggingDisabledMessage() {
  return "Query logging is disabled, set the global variable \$query_debug = true";
}

function printQueryProfileToConsole() {
  if (!shouldLogQueries()) {
    error_log(loggingDisabledMessage());
    return;
  }
  error_log(formatProfile());
}

function queryProfileAsHtmlComment() {
  $profile_comment = "<!-- SQL Query Profile: \n";
  if (!shouldLogQueries()) {
    $profile_comment .= loggingDisabledMessage();
  } else {
    $profile_comment .= formatProfile();
  }
  $profile_comment .= "\n-->\n";
  print $profile_comment;
}

?>