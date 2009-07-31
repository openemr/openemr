<?php
////////////////////////////////////////////////////////////////////
// Form:	PSYCHIATRISCH ONDERZOEK
// Package:	Research psihiatric - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 29-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

////////////////////////////////////////////////////////////////////
// Function:	psychiatrisch_onderzoek_report
// Purpose:	callback func?
// Input:	pid? encounter, cols, id ?
////////////////////////////////////////////////////////////////////
function psychiatrisch_onderzoek_report( $pid, $encounter, $cols, $id )
{
  $count = 0;
  $data = formFetch( "form_psychiatrisch_onderzoek", $id );
  if( $data )
  {
    print "<table>";
    
    foreach( $data as $key => $value )
    {
      // here we check for current ???? what ? session ?
      if ($key == "id" || $key == "pid" || $key == "user" ||
        $key == "groupname" || $key == "authorized" || $key == "activity" ||
        $key == "date" || $value == "" || $value == "0000-00-00 00:00:00")
      {
        continue;
      }
      
      // larry :: ??? - is this for check box or select or what ?
      if( $value == "on" )
      {
        $value = "yes";
      }

      // Datum onderzoek
      if( $key == "datum_onderzoek" )
      {
        print "<tr><td><span class=bold>" . xl('Examination Date') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }

      // Reden van aanmelding 
      if( $key == "reden_van_aanmelding" )
      {
        print "<tr><td><span class=bold>" . xl('Reason for Visit') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Conclusie van intake
      if( $key == "conclusie_van_intake" )
      {
        print "<tr><td><span class=bold>" . xl('Intake Conclusion') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Medicatie 
      if( $key == "medicatie" )
      {
        print "<tr><td><span class=bold>" . xl('Medications') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Anamnese
      if( $key == "anamnese" )
      {
        print "<tr><td><span class=bold>" . xl('History') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Psychiatrisch onderzoek i.e.z.
      if( $key == "psychiatrisch_onderzoek" )
      {
        print "<tr><td><span class=bold>" . xl('Psychiatric Examination') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Beschrijvende conclusie
      if( $key == "beschrijvende_conclusie" )
      {
        print "<tr><td><span class=bold>" . xl('Conclusions') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Behandelvoorstel
      if( $key == "behandelvoorstel" )
      {
        print "<tr><td><span class=bold>" . xl('Treatment Plan') . ": </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      

      // increment records counter
      $count++;
      // check if not at the end close/open new row
      if ($count == $cols)
      {
        $count = 0;
        print "</tr><tr>\n";
      }
      
    }
  }
  print "</tr></table>";
}
?>
