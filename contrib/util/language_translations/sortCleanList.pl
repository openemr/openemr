#!/usr/bin/perl
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# author Brady Miller
# email  brady.g.miller@gmail.com
# date   05/21/09
#
# This is a perl script that will simply sort and remove redundant entries
#  from a list to maintain the manuallyAddedConstants.txt and the
#  manuallyRemovedConstants.txt files.
#
#  Example command:
#
#  -Below command will simply sort and remove redundant entries from a list:
#  ./sortCleanList.pl manuallyAddedConstants.txt
#
#

use strict;

my $inputFile;
my $logFile = "log.txt";
my $filenameOut = "list.txt";

# check parameter
if (@ARGV > 1) {
 die "\nERROR: Too many parameters. Follow instructions found in sortCleanList.pl file.\n\n";
}
elsif (@ARGV < 1) {
 die "\nERROR: Not enough parameters. Follow instructions found in sortCleanList.pl file.\n\n";
}
elsif (@ARGV == 1) {
 $inputFile = $ARGV[0];
}
else {
 die "\nERROR: Problem with  parameters. Follow instructions found in sortCleanList.pl file.\n\n";
}

# open log file and output file
open(LOGFILE, ">$logFile") or die "unable to open log file";
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# if comparing, then open input file and store in array
open(MYINPUTFILE, "<$inputFile") or die "unable to open file";
my @inputList = <MYINPUTFILE>;
close(MYINPUTFILE);

# remove blankl lines, windows characters, and chomp it
my @processInputList1;
foreach my $var (@inputList) { 
 chomp($var);
 # remove ^M characters (windows line feeds)
 $var =~ s/\r//g;
 # skip blank lines
 if ($var eq "") {
  next;    
 }
 # push into new array
 push(@processInputList1,$var);
}

# remove redundancies
my @processInputList2;
foreach my $var (@processInputList1) {
 if (withinArray($var,@processInputList2)) {
  print LOGFILE "Redundant variable removed: " . $var . "\n";
 }
 else {
  push (@processInputList2, $var);   
 }
}

# sort list
my @sortedInputList = sortConstants(@processInputList2);

# output to file
foreach my $var (@sortedInputList) {
 print OUTPUTFILE $var . "\n";
}

# close files
close(LOGFILE);
close(OUTPUTFILE);


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
