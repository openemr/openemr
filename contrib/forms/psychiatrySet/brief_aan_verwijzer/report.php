<?php

////////////////////////////////////////////////////////////////////
// Form:    BRIEF AAN VERWIJZER
// Package: letter to - Dutch specific form
// Created by:  Larry Lart
// Version: 1.0 - 30-03-2008
////////////////////////////////////////////////////////////////////

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

////////////////////////////////////////////////////////////////////
// Function:    brief_aan_verwijzer_report
// Purpose: callback func?
// Input:   pid? encounter, cols, id ?
////////////////////////////////////////////////////////////////////
function brief_aan_verwijzer_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_psychiatrisch_onderzoek", $id);
    if ($data) {
        print "<table>";

        foreach ($data as $key => $value) {
            // here we check for current ???? what ? session ?
            if (
                $key == "id" || $key == "pid" || $key == "user" ||
                $key == "groupname" || $key == "authorized" || $key == "activity" ||
                $key == "date" || $value == "" || $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            // larry :: ??? - is this for check box or select or what ?
            if ($value == "on") {
                $value = "yes";
            }

            // Introductie
            if ($key == "introductie") {
                print "<tr><td><span class=bold>" . xlt('Introduction') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // Reden van aanmelding
            if ($key == "reden_van_aanmelding") {
                print "<tr><td><span class=bold>" . xlt('Reason for Visit') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // Anamnese
            if ($key == "anamnese") {
                print "<tr><td><span class=bold>" . xlt('History') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // Psychiatrisch onderzoek
            if ($key == "psychiatrisch_onderzoek") {
                print "<tr><td><span class=bold>" . xlt('Psychiatric Examination') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // Beschrijvend conclusie
            if ($key == "beschrijvend_conclusie") {
                print "<tr><td><span class=bold>" . xlt('Conclusions') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // Advies/beleid
            if ($key == "advies_beleid") {
                print "<tr><td><span class=bold>" . xlt('Treatment Plan') . ": </span><span class=text>" .
                    nl2br(text($value)) . "</span></td></tr>";
            }

            // increment records counter
            $count++;
            // check if not at the end close/open new row
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
