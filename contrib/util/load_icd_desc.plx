#!/usr/bin/perl
use strict;

use DBI;
use WWW::Mechanize;
use HTML::TokeParser;

#######################################################################
# Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
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

#######################################################################
#                 Parameters that you may customize                   #
#######################################################################

# An empty database name will cause SQL INSERT statements to be dumped
# to stdout, with no database access.  To update your OpenEMR database
# directly, specify its name here.
#
my $DBNAME = "";
# $DBNAME = "openemr";

# Change this appropriately for years other than 2008.
#
my $START_URL = "http://www.icd9data.com/2008/Volume1/default.htm";

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
#   or die $DBI::errstr;

# Comment this out if you want to keep old nonmatching codes.
#
$dbh->do("delete from codes where code_type = 2") or die "oops" if ($DBNAME);

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

  while(my $tag = $parser->get_tag("li", "h1")) {
    if ($tag->[0] eq "li") {
      $tag = $parser->get_tag;
      next unless ($tag->[0] eq "a");
      my $nexturl = $browser->base();
      $nexturl =~ s'/[^/]+$'/';
      scrape($nexturl . $tag->[1]{href});
    }
    else {
      $tag = $parser->get_tag;
      next unless ($tag->[0] eq "img");
      next unless ($tag->[1]{src} =~ /SpecificGreen/);
      $tag = $parser->get_tag;
      next unless ($tag->[0] eq "a");
      my $tmp = $parser->get_trimmed_text;
      unless ($tmp =~ /Diagnosis (\S+)/) {
        print STDERR "Parse error in '$tmp' at $url\n";
        next;
      }
      my $code = $1;
      $tag = $parser->get_tag("h2", "h1");
      die "h2 tag missing!\n" unless ($tag->[0] eq "h2");
      my $desc = $parser->get_trimmed_text;
      $desc =~ s/'/''/g;  # some descriptions will have quotes

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
