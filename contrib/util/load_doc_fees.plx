#!/usr/bin/perl
use strict;

use DBI;

#######################################################################
# Copyright (C) 2005, 2008 Rod Roark <rod@sunsetsystems.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This loads CPT codes and their modifiers and fees into the "codes"
# table of OpenEMR.  If the row already exists then only the fee is
# modified, otherwise a new row is created with no description.
# See also load_cpt_desc.plx which loads the descriptions.
#
# Run it like this:
#
#   ./load_doc_fees.plx < PFALL08A.TXT
#
# Fee schedules as of this writing are at:
#   http://www.cms.hhs.gov/PhysicianFeeSched/PFSNPAF/list.asp
# Medicare also produces periodic updates which are in the same format
# and can also be processed by this program.
#######################################################################

#######################################################################
#               Parameters that you should customize                  #
#######################################################################

my $DBNAME         = "openemr";  # database name
my $CARRIER        = "05440";    # Tennessee
my $FEE_MULTIPLIER = 1.5;        # fee multiplier

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

my $countnew = 0;
my $countup  = 0;

#######################################################################
#                            Main Loop                                #
#######################################################################

while (my $line = <STDIN>) {
  next unless ($line =~ /^"20/);

  my @cols = split /,/, $line;

  my $carrier  = substr($cols[1], 1, -1);
  my $code     = substr($cols[3], 1, -1);
  my $modifier = substr($cols[4], 1, -1);
  my $fee      = substr($cols[5], 1, -1);

  next unless ($carrier eq $CARRIER);
  next unless ($code =~ /^\d/); # CPT codes only

  $modifier =~ s/ //g;
  $fee *= $FEE_MULTIPLIER;

  my $usth = $dbh->prepare("SELECT id FROM codes " .
    "WHERE code_type = 1 AND code = '$code' AND modifier = '$modifier'")
    or die $dbh->errstr;
  $usth->execute() or die $usth->errstr;
  my @urow = $usth->fetchrow_array();

  my $query;
  my $codes_id;

  if (! @urow) {
    $query = "INSERT INTO codes " .
      "( code_text, code, code_type, modifier, fee ) VALUES " .
      "( '', '$code', 1, '$modifier', 0 )";
    $dbh->do($query) or die $query;
    $codes_id = $dbh->{'mysql_insertid'};
    print $query . "\n";
    ++$countnew;
  }
  else {
    $codes_id = $urow[0];
    $query = "DELETE FROM prices WHERE pr_id = $codes_id AND pr_selector = ''";
    $dbh->do($query) or die $query;
    print $query . "\n";
    ++$countup;
  }

  $query = "INSERT INTO prices ( pr_id, pr_level, pr_price ) " .
    "VALUES ( $codes_id, 'standard', $fee )";
  $dbh->do($query) or die $query;
  print $query . "\n";
}

#######################################################################
#                             Shutdown                                #
#######################################################################

print "\nInserted $countnew rows, updated $countup rows.\n";

$dbh->disconnect;
