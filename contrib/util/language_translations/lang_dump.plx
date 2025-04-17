#!/usr/bin/perl
use strict;

use DBI;

#######################################################################
# Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This dumps language constants and their translations for one
# specified language to standard output in tab-delimited format,
# suitable for input to the companion script lang_load.plx.
#######################################################################

#######################################################################
#               Parameters that you should customize                  #
#######################################################################

my $DBNAME         = "openemr";  # database name

# Get this language code from the lang_id column of the lang_languages
# table in the source database.
#
my $LANGCODE       = 5;          # desired language

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

#######################################################################
#                            Main Loop                                #
#######################################################################

my $sth = $dbh->prepare("select lc.constant_name, ld.definition " .
  "from lang_constants as lc, lang_definitions as ld where " .
  "ld.cons_id = lc.cons_id and ld.lang_id = $LANGCODE")
  or die $dbh->errstr;
$sth->execute() or die $sth->errstr;

while (my @row = $sth->fetchrow_array()) {
  my ($constant_name, $definition) = @row;
  next if ($definition =~ /^\s*$/);
  print "$constant_name\t$definition\n";
}

#######################################################################
#                             Shutdown                                #
#######################################################################

$dbh->disconnect;

