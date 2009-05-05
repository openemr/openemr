#!/usr/bin/perl
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# author Brady Miller
# email  brady@sparmy.com
# date   04/03/09
#
# This is a perl script that will simply compare two constants files
# and output the common constants.  Output will go to commonConstants.txt
# and errors will be logged to log.txt.
#
#  Example commands:
#
#  -Below will output list of constants found only in both input files:
#  ./commonConstants.pl constants.txt previousConstants.txt
#

use strict;

my $filenameOut = "commonConstants.txt";
my $logFile = "log.txt";
my @constants1;
my @constants2;
my @commonConstants;
my $inputFilename1;
my $inputFilename2;

# open log file
open(LOGFILE, ">$logFile") or die "unable to open log file";

# collect parameters
if (@ARGV > 2) {
 die "\nERROR: Too many parameters. Follow instructions found in buildLanguageDatabase.pl file.\n\n";
 }
elsif (@ARGV < 2) {
 die "\nERROR: Need a parameter(s). Follow instructions found in buildLanguageDatabase.pl file.\n\n";
}
elsif (@ARGV == 2) {
 $inputFilename1 = $ARGV[0];
 $inputFilename2 = $ARGV[1];
}
else {
 print LOGFILE "ERROR: with parameters\n\n";
 die "ERROR: with parameters\n\n";
}

# open output files
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# place input files into arrays and chomp them
open(MYINPUTFILE, "<$inputFilename1") or die "unable to open file";
@constants1 = <MYINPUTFILE>;
close(MYINPUTFILE);
for my $var (@constants1) {
 chomp($var);
}
open(MYINPUTFILE2, "<$inputFilename2") or die "unable to open file";
@constants2 = <MYINPUTFILE2>;
close(MYINPUTFILE2);
for my $var (@constants2) {
 chomp($var);
}

# place common constants into the common array
foreach my $var (@constants1) {
 if (withinArray($var,@constants2)) {
  push (@commonConstants,$var);   
 }
}

# output the common constants
foreach my $var (@commonConstants) {
 print OUTPUTFILE $var."\n";
}

# close output file
close(OUTPUTFILE);


#
# FUNCTIONS
#

# function to return whether a variable is in an array
# param - $variable @arr
# return - 1(true) or 0(false) integer
#
sub withinArray {
 my($variable,@arr) = @_;
 my $isMatch = 0;
 foreach my $tempVar (@arr) {
  if ($tempVar eq $variable) {
   $isMatch = 1;
   last;
  }
 }
 return $isMatch;
}

