<?php
////////////////////////////////////////////////////////////////////
// Form:	Intakeverslag
// Package:	Report of First visit - Dutch specific form
// Created by:	Larry Lart
// Version:	1.0 - 27-03-2008
////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

////////////////////////////////////////////////////////////////////
// Function:	intakeverslag_report
// Purpose:	callback func?
// Input:	pid? encounter, cols, id ?
////////////////////////////////////////////////////////////////////
function intakeverslag_report( $pid, $encounter, $cols, $id )
{
  $count = 0;
  $data = formFetch( "form_intakeverslag", $id );
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

      // Intakedatum
      if( $key == "intakedatum" )
      {
        print "<tr><td><span class=bold>Intakedatum: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }

      // Reden van aanmelding 
      if( $key == "reden_van_aanmelding" )
      {
        print "<tr><td><span class=bold>Reden van aanmelding: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Klachten/Probleemgebieden
      if( $key == "klachten_probleemgebieden" )
      {
        print "<tr><td><span class=bold>Klachten/Probleemgebieden: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Hulpverlening en/of onderzoek tot nu toe 
      if( $key == "hulpverlening_onderzoek" )
      {
        print "<tr><td><span class=bold>Hulpverlening en/of onderzoek tot nu toe: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Hulpvraag en doelen
      if( $key == "hulpvraag_en_doelen" )
      {
        print "<tr><td><span class=bold>Hulpvraag en doelen: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Bijzonderheden systeem
      if( $key == "bijzonderheden_systeem" )
      {
        print "<tr><td><span class=bold>Bijzonderheden systeem: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Werk/ opleiding/ vrije tijdsbesteding
      if( $key == "werk_opleiding_vrije_tijdsbesteding" )
      {
        print "<tr><td><span class=bold>Werk/ opleiding/ vrije tijdsbesteding: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Relatie(s)/ kinderen
      if( $key == "relatie_kinderen" )
      {
        print "<tr><td><span class=bold>Relatie(s)/ kinderen: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Somatische context
      if( $key == "somatische_context" )
      {
        print "<tr><td><span class=bold>Somatische context: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      
      /////////////////////////////////////////////////////
      // - one line entry forms
      // alcohol
      if( $key == "alcohol" )
      {
        print "<tr><td><span class=bold>Alcohol: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // drugs
      if( $key == "drugs" )
      {
        print "<tr><td><span class=bold>Drugs: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // roken
      if( $key == "roken" )
      {
          print "<tr><td><span class=bold>Roken: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      ////////////////////////////////////////////////////////

      // Medicatie
      if( $key == "medicatie" )
      {
        print "<tr><td><span class=bold>Medicatie: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Familieanamnese
      if( $key == "familieanamnese" )
      {
        print "<tr><td><span class=bold>Familieanamnese: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Indruk/observaties 
      if( $key == "indruk_observaties" )
      {
        print "<tr><td><span class=bold>Indruk/observaties: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Beschrijvende conclusie
      if( $key == "beschrijvende_conclusie" )
      {
        print "<tr><td><span class=bold>Beschrijvende conclusie: </span><span class=text>" .
              nl2br(stripslashes($value)) . "</span></td></tr>";
      }
      // Behandelvoorstel
      if( $key == "behandelvoorstel" )
      {
        print "<tr><td><span class=bold>Behandelvoorstel: </span><span class=text>" .
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
