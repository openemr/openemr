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
# This loads the FDA Orange Book data for drugs into the "drugs"
# table of OpenEMR. If a row already exists with the same drug name
# it is ignored, otherwise a new row is created with the drug
# trade name provided from the Orange Book data.
#
# Run it like this:
#
#   ./load_fda_drugs.plx < Products.txt
#
# The Orange Book Data files as of this writing are at:
#   http://www.fda.gov/cder/orange/obreadme.htm
# Get the EOBZIP.ZIP file and extract the Products.txt file from there
#######################################################################

#######################################################################
#               Parameters that you should customize                  #
#######################################################################

my $DBNAME         = "openemr";  # database name

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
    
    my ($ingredients, $dosage_route, $tradename, $applicant,
        $strength, $ndaNum, $prodNum, $teCode, $approvalDate, 
        $refDrug, $type, $fullName) = split /~/, $line;

    # check for existing record
    my $usth = $dbh->prepare("SELECT drug_id FROM drugs " .
                "WHERE name=".$dbh->quote($tradename) )
    or die $dbh->errstr;
    $usth->execute() or die $usth->errstr;
    my @urow = $usth->fetchrow_array();

    my $query;
    my $drug_id;

    if (! @urow) {
        # add a new drug
        $query = "INSERT INTO drugs" .
            "( name ) VALUES " .
            "( ".$dbh->quote($tradename)." )";
        $dbh->do($query) or die $query;
        $drug_id = $dbh->{'mysql_insertid'};
        print $query . "\n";
        ++$countnew;
    }
    else {
        $drug_id = $urow[0];
        print "Skipped $tradename, already exists\n";
        ++$countup;
    }
}

#######################################################################
#                             Shutdown                                #
#######################################################################

print "\nInserted $countnew rows, skipped $countup rows.\n";

$dbh->disconnect;
