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
# This is a perl script that will create a new ranslation spreadsheet
# file from a constant file and an old spreadsheet file.  Output will
# go to newSpreadsheet.tsv and errors and message swill be logged to
# log.txt.
#
#  Example commands:
#
#  -Below will output the new spreadsheet:
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

# first create header on new spreadsheet while removing it from spreadsheet array
for (my $i =0; $i < $constantRow; $i++) {
 push (@newSpreadsheet, shift(@spreadsheet));
}
    
# place common constants into the spreadsheet array
my $idCounter = 1; # to assign constant id numbers
my $hitFlag; # flag to ensure ad new constants to spreadsheet
my @origSpreadConstants; # to keep track of removed constants later
my @finalSpreadConstants; # to keep track of removed constants later
my $first = 1; # to keep track of removed constants later
foreach my $var2 (@constants) {

 $hitFlag = 0; # reset the hit flag   
    
 foreach my $var (@spreadsheet) {

  my @tempArr = split($de,$var);
  my $tempCons = @tempArr[$constantColumn];
  
  # collect the original listing of constants during first loop
  if ($first) {
   push(@origSpreadConstants,$tempCons)
  }
     
  if ($tempCons eq $var2) {
   # add to array to keep track of removed constants
   push(@finalSpreadConstants,$tempCons);

   # create new id number
   @tempArr[$constantIdColumn] = $idCounter;
   my $newLine = join($de,@tempArr);
   $idCounter +=1;
     
   # place into array
   push (@newSpreadsheet,$newLine);
      
   # set the hit flag
   $hitFlag = 1;
  }
     
 }
 
 $first = 0;
 
 if (!($hitFlag)) {
  # constant is new, so add to spreadsheet
  push (@newSpreadsheet,$idCounter.$de.$var2);
  push (@finalSpreadConstants,$var2); # for later error checking
  print LOGFILE "ADDED: ".$var2."\n";
  $idCounter +=1;
 }
}

# send the removed constants to a log file
foreach my $var (@origSpreadConstants) {
 if (!(withinArray($var,@finalSpreadConstants))) {
  print LOGFILE "REMOVED: ".$var."\n";    
 }
}

# send the added constants to a log file
# this is redundant to hit method above
# no need for this method, so comment out
# foreach my $var (@finalSpreadConstants) {
#  if (!(withinArray($var,@origSpreadConstants))) {
#   print LOGFILE "ADDED: ".$var."\n";
#  }
# }

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

