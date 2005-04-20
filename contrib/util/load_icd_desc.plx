#!/usr/bin/perl
use strict;

use DBI;

#######################################################################
# This program is released under the GNU General Public License.
# Copyright (c) 2005 Rod Roark
#
# This loads descriptions of ICD9 codes into the "codes" table of
# OpenEMR.
#
# Run it like this:
#
#   ./load_icd_desc.plx < V22ICD9_FILE1.TXT
#
# To get this input file, download and extract v22_icd9.zip from
# http://www.cms.hhs.gov/providers/pufdownload/.  After that, you might
# want to also load the "friendlier" descriptions from icd9_long.txt at
# http://www.aafp.org/.
#######################################################################

#######################################################################
#                 Parameters that you may customize                   #
#######################################################################

my $DBNAME = "openemr";  # database name

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

# temporary!
#
# $dbh->do("delete from codes where code_type = 2") or die "oops";

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
  $line =~ s/^> //;

  next unless ($line =~ /^([0-9A-Z]\d\d\S*)\s+(\S.*)$/);

  my $code = $1;
  my $desc = $2;
  $code =~ s/\.//;    # remove periods from the icd9 codes
  $desc =~ s/\s*$//g; # remove all trailing whitespace
  $desc =~ s/'/''/g;  # just in case there are any quotes

  my $usth = $dbh->prepare("SELECT id FROM codes " .
    "WHERE code_type = 2 AND code = '$code'")
    or die $dbh->errstr;
  $usth->execute() or die $usth->errstr;
  my @urow = $usth->fetchrow_array();

  my $query;
  if (! @urow) {
    $query = "INSERT INTO codes " .
      "( code_type, code, modifier, code_text  ) VALUES " .
      "( 2, '$code', '', '$desc' )";
    ++$countnew;
  }
  else {
    $query = "UPDATE codes SET code_text = '$desc' " .
      "WHERE code_type = 2 AND code = '$code'";
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
