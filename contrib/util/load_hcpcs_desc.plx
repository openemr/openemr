#!/usr/bin/perl
use strict;

use DBI;

#######################################################################
# Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This loads descriptions of HCPCS codes into the "codes" table of
# OpenEMR.  Both the long and short descriptions are loaded from the
# same input file.
#
# For 2005, run it like this:
#
#   ./load_hcpcs_desc.plx < 05anweb.txt
#
# To get this input file, download and extract anhcpc05.zip from
# http://www.cms.hhs.gov/providers/pufdownload/.
#######################################################################

#######################################################################
#                 Parameters that you may customize                   #
#######################################################################

my $DBNAME     = "openemr";  # database name

# To load the short descriptions (SHORTU.txt, not currently used by
# OpenEMR but probably should), change this to "code_text_short":
#
my $TEXT_COL = "code_text";

# You can hard-code the database user name and password (see below),
# or else put them into the environment with bash commands like these
# before running this script:
#
#   export DBI_USER=username
#   export DBI_PASS=password
#
my $dbh = DBI->connect("dbi:mysql:dbname=$DBNAME") or die $DBI::errstr;

# my $dbh = DBI->connect("dbi:mysql:dbname=$DBNAME", "username", "password")
#   or die $DBI::errstr;

#######################################################################
#                             Startup                                 #
#######################################################################

my $currcode  = "";
my $currshort = "";
my $currlong  = "";
my $countup   = 0;
my $countnew  = 0;

$| = 1; # Turn on autoflushing of stdout.

#######################################################################
#                           Subroutines                               #
#######################################################################

sub writeCurrent() {
  return unless $currcode;

  $currlong  =~ s/  / /g;
  $currlong  =~ s/'/''/g;
  $currshort =~ s/'/''/g;

  my $usth = $dbh->prepare("SELECT id FROM codes " .
    "WHERE code_type = 3 AND code = '$currcode'")
    or die $dbh->errstr;
  $usth->execute() or die $usth->errstr;
  my @urow = $usth->fetchrow_array();

  my $query;
  if (! @urow) {
    $query = "INSERT INTO codes " .
      "( code_type, code, modifier, code_text_short, code_text ) VALUES " .
      "( 3, '$currcode', '', '$currshort', '$currshort' )";
    ++$countnew;
  }
  else {
    $query = "UPDATE codes SET code_text_short = '$currshort', code_text = '$currshort'  " .
      "WHERE code_type = 3 AND code = '$currcode'";
    ++$countup;
  }

  # Comment this out if you do not want to update the database here.
  # You can save stdout to a file if you want to inspect it and then
  # run it through the mysql utility.
  #
  $dbh->do($query) or die $query;

  print $query . ";\n";
}

#######################################################################
#                            Main Loop                                #
#######################################################################

while (my $line = <STDIN>) {
  my $rectype = substr($line, 10, 1);
  next unless ($rectype eq '3' or $rectype eq '4');

  if ($rectype eq '3') {
    &writeCurrent();
    $currcode = substr($line, 0, 5);
    $currlong = "";
    $currshort = substr($line, 91, 28);
    $currshort =~ s/\s*$//g; # remove all trailing whitespace
  }

  $currlong .= substr($line, 11, 80);
  $currlong =~ s/\s*$//g;
}

&writeCurrent();

#######################################################################
#                             Shutdown                                #
#######################################################################

print "\nInserted $countnew rows, updated $countup codes.\n";

$dbh->disconnect;
