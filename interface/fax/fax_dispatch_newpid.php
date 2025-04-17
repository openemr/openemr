<?php

// Copyright (C) 2008, 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is invoked via a jQuery getScript() from fax_dispatch.php.
// Its purpose is to return JavaScript that will set up patient-
// specific data when a patient is selected.  Currently this data
// is just the caller's selection list of recent encounters.


require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$res = sqlStatement("SELECT date, encounter, reason FROM form_encounter " .
  "WHERE pid = ? " .
  "ORDER BY date DESC, encounter DESC LIMIT 10", array($_GET['p']));

echo "var s = document.forms[0].form_copy_sn_visit;\n";
echo "s.options.length = 0;\n";

while ($row = sqlFetchArray($res)) {
    echo "s.options[s.options.length] = new Option(" .
    "'" . attr(substr($row['date'], 0, 10)) . " " .
    attr(strtr(substr($row['reason'], 0, 40), "\r\n", "  ")) . "', " .
    "'" . attr($row['encounter']) . "'" .
    ");\n";
}
