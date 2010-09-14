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
# This reads from standard input a language translation dump for one
# language, as written by lang_dump.plx, and loads any missing
# translations (definitions) for matching constants.
#
# Input lines not matching a constant already in the target database
# are ignored, as are lines where the target database already has a
# non-empty translation defined for the constant.
#######################################################################

#######################################################################
#               Parameters that you should customize                  #
#######################################################################

my $DBNAME         = "openemr";  # database name

# Get this language code from the lang_id column of the lang_languages
# table in the target database.
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

my $countnew = 0;
my $countup  = 0;

#######################################################################
#                            Main Loop                                #
#######################################################################

while (my $line = <STDIN>) {
  next unless ($line =~ /\t/);
  my @cols = split /\t/, $line;

  my ($constant_name, $definition) = @cols;
  $constant_name =~ s/'/''/g;  # just in case there are any quotes
  $definition    =~ s/'/''/g;
  $definition    =~ s/\s*$//g; # remove all trailing whitespace

  my $sth = $dbh->prepare("SELECT cons_id FROM lang_constants " .
    "WHERE constant_name LIKE '$constant_name' " .
    "ORDER BY cons_id LIMIT 1")
    or die $dbh->errstr;
  $sth->execute() or die $sth->errstr;
  my @row = $sth->fetchrow_array();
  next if (! @row);
  my ($cons_id) = @row;

  my $sth = $dbh->prepare("SELECT def_id, definition FROM lang_definitions " .
    "WHERE cons_id = '$cons_id' AND lang_id = $LANGCODE " .
    "ORDER BY def_id LIMIT 1")
    or die $dbh->errstr;
  $sth->execute() or die $sth->errstr;
  my @row = $sth->fetchrow_array();

  my $query;
  if (! @row) {
    $query = "INSERT INTO lang_definitions " .
      "( cons_id, lang_id, definition ) VALUES " .
      "( '$cons_id', $LANGCODE, '$definition' )";
    ++$countnew;
  }
  else {
    my ($def_id, $old_definition) = @row;
    next unless ($old_definition =~ /^\s*$/);
    $query = "UPDATE lang_definitions SET definition = '$definition' " .
      "WHERE def_id = '$def_id'";
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

print "\nInserted $countnew rows, updated $countup rows.\n";

$dbh->disconnect;

