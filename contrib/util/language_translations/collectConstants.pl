#!/usr/bin/perl
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# author Brady Miller
# email  brady@sparmy.com
# date   03/25/09
#
# This is a perl script that will collect unique constants within
# OpenEMR source code.
#  It effectively finds all xl("constants","") within OpenEMR.
#  It can also compare to a previous list to find new constants.
#  
#  Example commands:
#
#  -Below command will find all unique constants, alphabetize,
#   and dump into file constants.txt:
#  ./collectConstants /var/www/openemr
#
#  -Below command will find all only the new unique constants,
#   alphabetize these, and dump to file constants.txt:
#  ./collectConstants /var/www/openemr previousConstants.txt 
#
#

use strict;

# simpleList is flag that is pertinent when compareFlag is not
# used. If set (1), then just makes simple list. If not set (0)
# then output is formatted into a tab delimited spreadsheet.
my $simpleList = 1;
my $directoryIn; #name is set below
my $comparisonFile; #name is set below
my $filenameOut = "constants.txt";
my $logFile = "log.txt";
my $compareFlag; #this is set below
my @previousConstants; #will hold previous constants
my @uniqueConstants; #will hold the unique constants
my @filenames; #will hold all file name
my @inputFile;

my $headerLineOne   = "\t1\t2\t3\t4\t5\t6";
my $headerLineTwo   = "\ten\tse\tes\tde\tdu\the";
my $headerLineThree = "\tEnglish\tSwedish\tSpanish\tGerman\tDutch\tHebrew";

# check for parameter to set isCompact flag
if (@ARGV > 2) {
 die "\nERROR: Too many parameters. Follow instructions found in collectConstants.pl file.\n\n";
}
elsif (@ARGV == 0) {
 die "\nERROR: Need a parameter. Follow instructions found in collectConstants.pl file.\n\n";
}
elsif (@ARGV == 2) {
 $comparisonFile = $ARGV[1];
 $directoryIn = $ARGV[0];
 $compareFlag = 1;
}
elsif (@ARGV == 1) {
 $directoryIn = $ARGV[0];
 $compareFlag = 0;
}
else {
 die "\nERROR: Problem with  parameters. Follow instructions found in collectConstants.pl file.\n\n";
}

# open log file and output file
open(LOGFILE, ">$logFile") or die "unable to open log file";
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# if comparing, then open comparison file and store in array
if ($compareFlag) {
 open(MYINPUTFILE, "<$comparisonFile") or die "unable to open file";
 @previousConstants = <MYINPUTFILE>;
 close(MYINPUTFILE);
    
 # chomp it
 foreach my $var (@previousConstants) {
     chomp($var);
 }  
}

# create filenames array
recurse($directoryIn);

# step thru each file to find constants
foreach my $var (@filenames) {
 
 print LOGFILE $var." prepping.\n";
 
 open(MYINPUTFILE2, "<$var") or die "unable to open file";
 @inputFile = <MYINPUTFILE2>;
 close(MYINPUTFILE2);
 
 # remove newlines
 foreach my $tempLine (@inputFile) {
  chomp($tempLine);   
 }
  
 my $fileString = join(" ", @inputFile);
 # print LOGFILE $fileString;

 my $traditionalXL = 0; #flag
 my $smartyXL = 0; #flag
 
 
 if ($fileString =~ /xl\s*\(/i) {
  # line contains a traditional xl(function)
  $traditionalXL = 1;
 }

 if ($fileString =~ /\{\s*xl\s*t\s*=\s*/i) {
  # line contains a smarty xl function
  $smartyXL = 1;
 }
 
 # Report files with both smarty and traditional xl functions on same page
 if ($smartyXL && $traditionalXL) {
  print LOGFILE "WARNING: Found traditional and smarty xl functions on same page: $var\n";
 }

 # break apart each xl function statement if exist
 my @xlInstances;
 if ($smartyXL) {
  @xlInstances = split(/\{\s*xl\s*t\s*=\s*/i, $fileString);
 }  
 elsif ($traditionalXL) {
  @xlInstances = split(/xl\s*\(+/i, $fileString);   
 }
 else {
  # no xl functions to parse on this page
  next;
 }

 # drop the first element
 shift(@xlInstances);
  
 my $sizeArray = @xlInstances;  
 if ($sizeArray > 0) {  
  foreach my $var2 (@xlInstances) {
   # remove spaces from front of $var2
   my $editvar2 = $var2;
   $editvar2 =~ s/^\s+//;
       
   # collect delimiter, ' or "
   my $de = substr($editvar2,0,1);
    
   # skip if blank
   if ($de eq "") {
    next;	
   }
    
   # skip if ) (special case from howto files)
   if ($de eq ")") {
    print LOGFILE "MESSAGE:  Special case character ) skipped\n";
    print LOGFILE $editvar2."\n";
    next;
   }

   # skip $. Raally rare usage of xl() function.
   # There are about 25 lines of this in entire codebase
   # and likely just several contants. Can put in manually
   # if require.
   if ($de eq "\$") {
    print LOGFILE "MESSAGE:  Special case character \$ skipped\n";
    print LOGFILE $editvar2."\n";
    next;
   }  
       
   print LOGFILE "$de"."\n";
    
   # remove delimiter from string
   $editvar2 = substr($editvar2,1);
     
   # remove the evil ^M characters (report file)
   if ($editvar2 =~ /\r/) {
    print LOGFILE "WARNING: File contains dos end lines: $var\n";    
   }
   $editvar2 =~ s/\r//g;
      
   # hide instances of \$de
   $editvar2 =~ s/\\$de/__-_-__/g;
    
   # collect the constant   
   my @tempStringArr = split(/$de/,$editvar2); 
   my $tempString = @tempStringArr[0];
    
   # revert hidden instances of \$de
   $tempString =~ s/__-_-__/\\$de/g;
    
   # check to see if unique etc.
   if (!(withinArray($tempString,@uniqueConstants))) {
    # hit is unique
      
    if ($compareFlag) {
     # ensure not in comparison file
     if (!(withinArray($tempString,@previousConstants))) {
      # Have a real hit
      push(@uniqueConstants,$tempString);
     }
    }
       
    else {
     # Have a real hit
     push(@uniqueConstants,$tempString);
    }
   }
  }
 }
 
 print LOGFILE $var." checked.\n";
}

#alphabetize the constants
my @sorted = sort { lc($a) cmp lc($b) } @uniqueConstants;

if ($compareFlag || $simpleList) {
 # output simple list
 foreach my $var (@sorted) {
  print OUTPUTFILE $var."\n"; 
 }
}
else {
 # output tab delimited table
 print OUTPUTFILE $headerLineOne."\n";
 print OUTPUTFILE $headerLineTwo."\n";
 print OUTPUTFILE $headerLineThree."\n";
 my $counter = 1;
 foreach my $var (@sorted) {
  print OUTPUTFILE $counter."\t".$var."\n";
  $counter += 1;
 }
}



#
# function to collect list of filename
# param - directory
# globals - @filenames
# return - nothing
#
sub recurse($) {
 my($path) = @_;
    
 ## append a trailing / if it's not there
 $path .= '/' if($path !~ /\/$/);
    
 ## print the directory being searched
 # print $path,"\n";
    
 ## loop through the files contained in the directory
 for my $eachFile (glob($path.'*')) {
	  
  ## if the file is a directory
  if( -d $eachFile) {
   ## pass the directory to the routine ( recursion )
   recurse($eachFile);
  } else {
   ## print the file ... tabbed for readability
   push(@filenames,$eachFile);
  }
 }
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
