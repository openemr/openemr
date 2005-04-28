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
# This loads descriptions of CPT codes into the "codes" table of
# OpenEMR.  The codes must already be in the table
# (see load_doc_fees.plx).
#
# Run it like this:
#
#   ./load_cpt_desc.plx < MEDU.txt
#
# See https://catalog.ama-assn.org/Catalog/ for purchasing and
# downloading CPT codes and descriptions.  What you want is the
# "CPT ASCII Data Files Complete Set".
#######################################################################

#######################################################################
#                 Parameters that you may customize                   #
#######################################################################

my $DBNAME = "openemr";  # database name

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

$| = 1; # Turn on autoflushing of stdout.

my $countup  = 0;
my $countnew = 0;

#######################################################################
#                            Main Loop                                #
#######################################################################

while (my $line = <STDIN>) {
  next unless ($line =~ /^\d/);

  my $code = substr($line, 0, 5);
  my $desc = substr($line, 6);
  $desc =~ s/\s*$//g; # remove all trailing whitespace
  $desc =~ s/'/''/g;  # just in case there are any quotes

  my $usth = $dbh->prepare("SELECT id FROM codes " .
    "WHERE code_type = 1 AND code = '$code'")
    or die $dbh->errstr;
  $usth->execute() or die $usth->errstr;
  my @urow = $usth->fetchrow_array();

  my $query;
  if (! @urow) {
    $query = "INSERT INTO codes " .
      "( $TEXT_COL, code, code_type, modifier ) VALUES " .
      "( '$desc', '$code', 1, '' )";
    ++$countnew;
  }
  else {
    $query = "UPDATE codes SET $TEXT_COL = '$desc' " .
      "WHERE code_type = 1 AND code = '$code'";
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
#                             Shutdown                                #
#######################################################################

print "\nInserted $countnew rows, updated $countup codes.\n";

$dbh->disconnect;
