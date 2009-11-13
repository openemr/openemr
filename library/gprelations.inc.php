<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This module supports use of the gprelations table to maintain
// many-to-many relationships (linkings) among the following other
// tables.  For each, a corresponding type code is assigned:
//
//  1 documents
//  2 form_encounter (visits)
//  3 immunizations
//  4 lists (issues)
//  5 openemr_postcalendar_events (appointments)
//  6 pnotes
//  7 prescriptions
//  8 transactions (e.g. referrals)
//
// By convention we require that type1 must be less than or equal to type2.
//
// As of this writing (2009-11-11), only documents-to-pnotes relations are
// used. However expansion is anticipated, as well as the opportunity to
// obsolete the issue_encounter table.

function isGpRelation($type1, $id1, $type2, $id2) {
  $tmp = sqlQuery("SELECT count(*) AS count FROM gprelations WHERE " .
    "type1 = $type1 AND id1 = $id1 AND " .
    "type2 = $type2 AND id2 = $id2");
  return !empty($tmp['count']);
}

function setGpRelation($type1, $id1, $type2, $id2, $set=TRUE) {
  if (isGpRelation($type1, $id1, $type2, $id2)) {
    if (!$set) {
      sqlStatement("DELETE FROM gprelations WHERE " .
        "type1 = $type1 AND id1 = $id1 AND type2 = $type2 AND id2 = $id2");
    }
  }
  else {
    if ($set) {
      sqlStatement("INSERT INTO gprelations " .
        "( type1, id1, type2, id2 ) VALUES " .
        "( $type1, $id1, $type2, $id2 )");
    }
  }
}
?>
