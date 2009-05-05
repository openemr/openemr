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
# This is a perl script that will sort the constants in the constants file
# and the spreadsheet file.  Output will go to the sortedConstants.txt and
# sortedSpreadsheet.tsv files and and errors will be logged to log.txt.
#
#  Example commands:
#
#  -Below will output list of constants found only in both input files:
#  ./sortConstantsSpreadsheet.pl spreadsheet.tsv constants.txt
#

use strict;

my $spreadsheetOut = "sortedSpreadsheet.tsv";
my $constantsOut = "sortedConstants.txt";
my $logFile = "log.txt";
my $de = "\t"; # delimiter
my @constants;
my @sortedConstants;
my @spreadsheet;
my @sortedSpreadsheet;
my $constantRow = 5; # from spreadsheet, 0 is lowest
my $constantColumn = 1; # from spreadsheet, 0 is lowest
my $constantIdColumn = 0; # from spreadsheet, 0 is lowest
my $inputSpreadsheet;
my $inputConstants;

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
open(OUTPUTFILE1, ">$spreadsheetOut") or die "unable to open output file";
open(OUTPUTFILE2, ">$constantsOut") or die "unable to open output file";

# place input files into arrays and chomp them
open(MYINPUTFILE1, "<$inputSpreadsheet") or die "unable to open file";
@spreadsheet = <MYINPUTFILE1>;
close(MYINPUTFILE1);
for my $var (@spreadsheet) {
 chomp($var);
}
open(MYINPUTFILE2, "<$inputConstants") or die "unable to open file";
@constants = <MYINPUTFILE2>;
close(MYINPUTFILE2);
for my $var (@constants) {
 chomp($var);
}

# sort constants
@sortedConstants = sortConstants(@constants);

# sort spreadsheet
for (my $i=0; $i<$constantRow; $i++) {
 push(@sortedSpreadsheet,@spreadsheet[$i]);   
}
my $idCounter = 1; # to label id's
my $counter = 0; # to skip header
foreach my $var (@sortedConstants) {
 my $hit = 0; # flag for sanity checking
 
 foreach my $var2 (@spreadsheet) {
  $counter += 1;
  if ($counter <= $constantRow) {
   # skip header
   next;
  }
  my @tempArr = split($de,$var2);
  my $tempCons = @tempArr[$constantColumn];
  if ($var eq $tempCons){
   @tempArr[$constantIdColumn] = $idCounter;
   my $tempLine = join($de,@tempArr);
   push(@sortedSpreadsheet,$tempLine);
   $idCounter += 1;
   $hit = 1;
  }

 }

 if (!$hit) {
  print "ERROR: missed constant".$var."\n";    
 }
}

# output the files
foreach my $var (@sortedSpreadsheet) {
 print OUTPUTFILE1 $var."\n";
}
foreach my $var (@sortedConstants) {
 print OUTPUTFILE2 $var."\n";
}

# close output file
close(OUTPUTFILE1);
close(OUTPUTFILE2);


#
# FUNCTIONS
#

# function to sort constant list
# param - @arr
# return - @arr
#
sub sortConstants {
 my(@arr) = @_;
 my @first;
 my @last;
    
 foreach my $var (@arr) {
  if ($var =~ /^[a-z]/i) {
   push (@first,$var);   
  }
  else {
   push (@last,$var);    
  }
 }    
    
 my @sortFirst = sort { lc($a) cmp lc($b) } @first;
 my @sortLast = sort { lc($a) cmp lc($b) } @last;
    
 push (@sortFirst, @sortLast);
 my @sorted_arr = @sortFirst;
    
 return @sorted_arr;
}

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

