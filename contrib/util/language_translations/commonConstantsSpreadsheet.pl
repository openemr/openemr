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
# This is a perl script that will filter out items from the spreadsheet that are
# not contained in the constant file.  Output will go to newSpreadsheet.tsv
# and errors will be logged to log.txt.
#
#  Example commands:
#
#  -Below will output list of constants found only in both input files:
#  ./commonConstantsSpreadsheet.pl spreadsheet.tsv constants.txt
#

use strict;

my $filenameOut = "newSpreadsheet.tsv";
my $logFile = "log.txt";
my $de = "\t"; # delimiter
my @constants;
my @spreadsheet;
my @newSpreadsheet;
my $inputSpreadsheet;
my $inputConstants;
my $constantRow = 5; # from spreadsheet, 0 is lowest
my $constantColumn = 1; # from spreadsheet, 0 is lowest
my $constantIdColumn = 0; # from spreadsheet, 0 is lowest

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
 $inputSpreadsheet = $ARGV[0];
 $inputConstants = $ARGV[1];
}
else {
 print LOGFILE "ERROR: with parameters\n\n";
 die "ERROR: with parameters\n\n";
}

# open output files
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# place input files into arrays and chomp them
open(MYINPUTFILE, "<$inputSpreadsheet") or die "unable to open file";
@spreadsheet = <MYINPUTFILE>;
close(MYINPUTFILE);
for my $var (@spreadsheet) {
 chomp($var);
}
open(MYINPUTFILE2, "<$inputConstants") or die "unable to open file";
@constants = <MYINPUTFILE2>;
close(MYINPUTFILE2);
for my $var (@constants) {
 chomp($var);
}

# place common constants into the spreadsheet array
my $counter = 0; # to deal with header
my $idCounter = 1; # to assign constant id numbers
foreach my $var (@spreadsheet) {

 $counter += 1;
 
 # deal with the header rows
 if ($counter <= $constantRow) {
  push (@newSpreadsheet,$var);
  next;
 }

 # filter out rows that contain constants not in the
 # constant file
 my @tempArr = split($de,$var);
 my $tempCons = @tempArr[$constantColumn];
 if (withinArray($tempCons,@constants)) {

  # create new id number
  @tempArr[$constantIdColumn] = $idCounter;
  my $newLine = join($de,@tempArr);
  $idCounter +=1;
     
  # place into array
  push (@newSpreadsheet,$newLine);   
 }
}

# output the common constants
foreach my $var (@newSpreadsheet) {
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

