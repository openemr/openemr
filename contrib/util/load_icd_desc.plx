#!/usr/bin/perl
use strict;

#######################################################################
# Copyright (C) 2007-2010 Rod Roark <rod@sunsetsystems.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#######################################################################
# This loads ICD9 codes and descriptions into the "codes" table of
# OpenEMR, scraping from from www.icd9data.com.
# Alternatively you can just dump the INSERT statements to stdout.
#######################################################################

# You might need to install one or more of these dependencies.
# The Debian/Ubuntu package names are noted as comments:
#
use DBI;              # libdbi-perl and libdbd-mysql-perl
use WWW::Mechanize;   # libwww-mechanize-perl
use HTML::TokeParser; # libhtml-parser-perl

#######################################################################
#                 Parameters that you may customize                   #
#######################################################################

# Change this as needed for years other than 2010.
#
my $START_URL = "http://www.icd9data.com/2010/Volume1/default.htm";

# An empty database name will cause SQL INSERT statements to be dumped
# to stdout, with no database access.  To update your OpenEMR database
# directly, specify its name here.
#
my $DBNAME = "";

# You can hard-code the database user name and password (see below),
# or else put them into the environment with bash commands like these
# before running this script:
#
#   export DBI_USER=username
#   export DBI_PASS=password
#
my $dbh = DBI->connect("dbi:mysql:dbname=$DBNAME") or die $DBI::errstr
  if ($DBNAME);

# my $dbh = DBI->connect("dbi:mysql:dbname=$DBNAME", "username", "password")
#   or die $DBI::errstr if ($DBNAME);

# Comment this out if you want to keep old nonmatching codes.
#
$dbh->do("delete from codes where code_type = 2") or die "oops"
  if ($DBNAME);

#######################################################################
#                             Startup                                 #
#######################################################################

$| = 1;      # Turn on autoflushing of stdout.

my $countup  = 0;
my $countnew = 0;

#######################################################################
#                            Main Logic                               #
#######################################################################

# This function recursively scrapes all of the web pages.
#
sub scrape {
  my $url = shift;

  my $browser = WWW::Mechanize->new();
  $browser->get($url);
  my $parser = HTML::TokeParser->new(\$browser->content());

  while(my $tag = $parser->get_tag("li", "div")) {

    # The <li><a> sequence is recognized as a link to another list
    # that must be followed.  We handle those recursively.
    if ($tag->[0] eq "li") {
      $tag = $parser->get_tag;
      $tag = $parser->get_tag if ($tag->[0] eq "strong");
      next unless ($tag->[0] eq "a");
      my $nexturl = $browser->base();
      # $nexturl =~ s'/[^/]+$'/';
      $nexturl =~ s'/20.+$'';
      scrape($nexturl . $tag->[1]{href});
    }

    # The <div><img> sequence starts an ICD9 code and description.
    # If the "specific green" image is used then we know this code is
    # valid as a specific diagnosis, and we will grab it.
    else {
      $tag = $parser->get_tag;
      next unless ($tag->[0] eq "img");
      next unless ($tag->[1]{src} =~ /SpecificGreen/);
      $tag = $parser->get_tag("a");
      my $tmp = $parser->get_trimmed_text;
      unless ($tmp =~ /Diagnosis Code (\S+)/) {
        print STDERR "Parse error in '$tmp' at $url\n";
        next;
      }
      my $code = $1;
      $tag = $parser->get_tag("div");
      my $desc = $parser->get_trimmed_text;
      $desc =~ s/'/''/g;  # some descriptions will have quotes

      # This creates the needed SQL statement, and optionally writes the
      # code and its description to the codes table.
      my $query = "INSERT INTO codes " .
        "( code_type, code, modifier, code_text  ) VALUES " .
        "( 2, '$code', '', '$desc' )";
      if ($DBNAME) {
        my $usth = $dbh->prepare("SELECT id FROM codes " .
          "WHERE code_type = 2 AND code = '$code'")
          or die $dbh->errstr;
        $usth->execute() or die $usth->errstr;
        my @urow = $usth->fetchrow_array();
        if (! @urow) {
          ++$countnew;
        }
        else {
          $query = "UPDATE codes SET code_text = '$desc' " .
            "WHERE code_type = 2 AND code = '$code'";
          ++$countup;
        }
        $dbh->do($query) or die $query;
      }

      print $query . ";\n";
    }
  }
}

# This starts the ball rolling.
scrape($START_URL);

#######################################################################
#                             Shutdown                                #
#######################################################################

if ($DBNAME) {
  print "\nInserted $countnew rows, updated $countup codes.\n";
  $dbh->disconnect;
}

