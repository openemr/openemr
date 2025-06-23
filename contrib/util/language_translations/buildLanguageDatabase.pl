#!/usr/bin/perl
#
# Copyright (C) 2009-2013 Brady Miller <brady.g.miller@gmail.com>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Author   Brady Miller <brady.g.miller@gmail.com>
# Author   Ramin Moshiri <raminmoshiri@gmail.com>
#
# This is a perl script that will build the language translation sql
# dumpfiles from the tab delimited language translation spreadsheet.
# It will create two output dumpfiles:
#   languageTranslations_utf8.sql
#   languageTranslations_latin1.sql (minus utf languages)
# It will also validate the spreadsheet and create a new spreadsheet
# that can be used for further downstream editing and re-importing
# back into Google Docs. It also outputs a logfile log.txt with
# errors in validation and database statistics.
#
#  Example command:
#
#  -Below command will build the sql dumpfile from given tsv
#   language spreadsheet and compare with a constants list to
#   ensure constants didn't get edited (output will go to
#   the log file), and will also fix limited issues. In this case
#   a new spreadsheet file will also be created with the corrected
#   constants to allow downstream modification and re-importing of
#   file into Google Docs:
#  ./buildLanguageDatabase.pl openemr_language_table.tsv constants.txt
#
#

use strict;

# Array to hold languages to skip for the latin1 translation file
# (pending)

# Put current known constant mismatches here, which are basically
# constants that get modified during the pipeline and don't look
# like originals in the end. If this number increases, a english constant
# was likely modified in the spreadsheet, and can then use log output
# to localize and fix the problem.
my $mismatchesKnown = 190;

# Hold variables to calculate language database statistics
my $totalConstants;
my $totalDefinitions;
my @languages;
my @numberConstantsLanguages;

# Main variables
my $de = "\t";
my $filenameOut;
my $inputFilename;
my $logFile = "log.txt";
my $stats = "stats.txt";
my $constantIdColumn = 0; # 0 is lowest
my $constantColumn = 1; # 0 is lowest
my $constantRow = 6; # 0 is lowest
my $languageNumRow = 0; # 0 is lowest
my $languageIdRow = 1; # 0 is lowest
my $languageNameRow = 2; # 0 is lowest
my $languageIsRtlRow = 3; # 0 is lowest

# variables for checking/fixing constants application
my $checkFilename; # holds list of constants if checking
my $filenameOut_revised = "revisedSpreadsheet.tsv";
my $flagCheck = 0;
my @previousConstants;
my @inputFile;
my @revisedFile;
my @inputFileProcessed;

# to hold utf8 flag
my $utf8;

# open output file
open(LOGFILE, ">$logFile") or die "unable to open log file";

# open output file
open(STATFILE, ">$stats") or die "unable to open stats file";

# collect parameters
if (@ARGV > 2) {
 die "\nERROR: Too many parameters. Follow instructions found in buildLanguageDatabase.pl file.\n\n";
 }
elsif (@ARGV < 2) {
 die "\nERROR: Need more parameter(s). Follow instructions found in buildLanguageDatabase.pl file.\n\n";
}
elsif (@ARGV == 2) {
 $flagCheck = 1;
 $checkFilename = $ARGV[1];
 $inputFilename = $ARGV[0];
}
else {
 print LOGFILE "ERROR: with parameters\n\n";
}

# if checking, then open check file and store in array
if ($flagCheck) {
 open(MYINPUTFILE, "<$checkFilename") or die "unable to open file";
 @previousConstants = <MYINPUTFILE>;
 close(MYINPUTFILE);
}

# place spreadsheet into array
open(MYINPUTFILE2, "<$inputFilename") or die "unable to open file";
@inputFile = <MYINPUTFILE2>;
close(MYINPUTFILE2);

# Clean up spreadsheet
# FIRST, remove newlines, blank lines, escape characters, and windows returns
# SECOND, place the escape characters in all required sql characters
foreach my $tempLine (@inputFile) {
 chomp($tempLine);
 if ($tempLine !~ /^\s*$/) {
  # remove ^M characters (windows line feeds)
  $tempLine =~ s/\r//g;

  # remove all escape characters
  $tempLine =~ s/\\//g;

  # place all required escape characters
  $tempLine =~ s/\'/\\\'/g;
  $tempLine =~ s/\"/\\\"/g;

  # push into new array
  push (@inputFileProcessed,$tempLine);
 }
}

# check spreadsheet for rogue tabs and newlines
#  (the last column needs to be full for this to work
#   correctly, such as the dummy language)
quickCheckStructure(@inputFileProcessed);

# check and fix modified constants (and constant id's)
if ($flagCheck) {
 # first create data for replacement spreadsheet if needed
 @revisedFile = checkConstants("special",@inputFileProcessed);
 # then clean data to create mysql dumpfiles
 @inputFileProcessed = checkConstants("normal",@inputFileProcessed);
}

# run through twice to make a utf8 table and a latin1 table
#  revised spreadsheet. Build statistics and revised
#  spreadsheet during utf8 run.
for (my $i=0;$i<2;$i++) {

 # set utf flag
 if ($i == 0) {
  # build utf8 table
  $filenameOut = "languageTranslations_utf8.sql";
  $utf8 = 1;
 }
 else {
  # build latin1 table
  $filenameOut = "languageTranslations_latin1.sql";
  $utf8 = 0
 }

 # open output file
 open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

 my $outputString = "";

 # add UTF8 set names for both utf8 and latin1 encoding, since
 #  the dumpfile is encoded in UTF8
 $outputString .= "\
--
-- Ensure correct encoding
--
";
 $outputString .= "SET NAMES utf8mb4;\n\n";

 # parse lang_languages
 $outputString .= createLanguages($utf8, @inputFileProcessed);

 # parse lang_constants
 $outputString .= createConstants($utf8, @inputFileProcessed);

 # parse lang_definitions
 $outputString .= createDefinitions($utf8, @inputFileProcessed);

 print OUTPUTFILE $outputString;

 # calculate statistics
 if ($utf8) {
  my $count = 0;
  my $countLanguages = 0;
  my $subtractDefinitions = 0;
  my @tempArray;
  my @statArray;
  foreach my $var (@languages) {
   # push all info into the log file
   push (@tempArray, $var.": ".fstr((($numberConstantsLanguages[$count]/$totalConstants)*100),2)."% (".$numberConstantsLanguages[$count]." definitions)\n");
   if ($var eq "dummy") {
    # do not count dummy language or dummy language constants
    $subtractDefinitions += $numberConstantsLanguages[$count];
   }
   else {
    if ($numberConstantsLanguages[$count] > 0) {
     # only count non-empty languages in total count
     $countLanguages += 1;
     # only include non-empty and non-dummy languages in stats
     push (@statArray, $var.": ".fstr((($numberConstantsLanguages[$count]/$totalConstants)*100),2)."% (".$numberConstantsLanguages[$count]." definitions)\n");
    }
   }
   $count += 1;
  }
  print LOGFILE "\nLanguage Statistics:\n";
  print STATFILE "\nLanguage Statistics:\n";

  # Report total number of real non empty languages
  print LOGFILE "Total number of languages with translations: ".$countLanguages."\n";
  print STATFILE "Total number of languages with translations: ".$countLanguages."\n";

  # Report total number of constants
  print LOGFILE "Total number of constants: ".$totalConstants."\n";
  print STATFILE "Total number of constants: ".$totalConstants."\n";

  # Report total number of real definitions
  print LOGFILE "Total number of real definitions: ".($totalDefinitions-$subtractDefinitions)."\n";
  print STATFILE "Total number of real definitions: ".($totalDefinitions-$subtractDefinitions)."\n";

  # Send log stat info
  my @sorted_tempArray = sort { lc($a) cmp lc($b) } @tempArray;
  foreach my $var (@sorted_tempArray) {
   print LOGFILE $var;
  }

  # Send official stat info
  my @sorted_statArray = sort { lc($a) cmp lc($b) } @statArray;
  foreach my $var (@sorted_statArray) {
   print STATFILE $var;
  }
 }

 # send the processed spreadsheet to file to allow downstream modifications
 # if checking and fixing modified constants
 if ($flagCheck && $utf8) {
  open(MYOUTPUTFILE2, ">$filenameOut_revised") or die "unable to open file";
  foreach my $var (@revisedFile) {
   print MYOUTPUTFILE2 $var."\n";
  }
  close(MYOUTPUTFILE2)
 }

 # close files
 close(OUTPUTFILE);
}

close(LOGFILE);
close(STATFILE);

#
#
# FUNCTIONS
#
#

#
# function to check spreadsheet for rogue tabs
#  to work, the last column needs to be filled (such as a dummy language)
# will output errors to LOGFILE
# param - @arr array of spreadsheet
# globals - @inputFile, LOGFILE, $de, $languageNumRow
#
sub quickCheckStructure() {
 my (@arr) = @_;

 # use the languagNumRow as the standard for number of tabs
 #  on each row
 my $numberColumns = split($de,$arr[$languageNumRow]);
 my $numberTabs = $numberColumns - 1;

 # ensure every row on spreadsheet has equal number of tabs
 my $counter = 1;
 foreach my $var (@arr) {
  my $tempNumber = split($de,$var);
  my $tempTabs = $tempNumber - 1;
  if ($numberTabs != $tempTabs) {
   print LOGFILE "\nERROR: $counter row with incorrect number of tabs. There are $tempTabs in this row and should be $numberTabs.\n";
   if ($tempTabs > $numberTabs) {
    # too many tabs
    print LOGFILE "\t(This is likely secondary to a rogue tab character(s) on row $counter.)\n";
   }
   else {
    # not enough tabs
    print LOGFILE "\t(This is likely secondary to a rogue newline character(s) on row $counter or one row above.)\n";
   }
  }
  $counter += 1;
 }

 return;
}

#
# function to compare to original constants
# normal flag will fix constants escape characters to prepare for mysql dumpfile
# special flag will not fix escape characters to prepare for spreadsheet revision file
# param - flag (normal or special), array of processed file
# globals - @previousConstants, $constantRow, $de, LOGFILE,
#           $constantIdColumn, $constantColumn
# return - none
#
sub checkConstants () {
 my ($flag, @page) = @_;

 print LOGFILE "Checking constants:\n\n";
 my $counter = $constantRow;
 my $badCount = 0;
 my $idErrorFlag = 0;
 foreach my $var (@previousConstants) {
  chomp($var);
  my @tempRow = split($de,$page[$counter]);
  my $tempId = $tempRow[$constantIdColumn];
  my $tempConstant = $tempRow[$constantColumn];

  # ensure constant has not been altered
  if ($var ne $tempConstant) {
   print LOGFILE "Following constant not same:\n";
   print LOGFILE "\toriginal- val:$var\n";
   print LOGFILE "\tspreadsheet- ID:$tempId val:$tempConstant\n";
   $badCount += 1;

   # apply fix
   my $fixedVar = $var;
   if ($flag eq "normal") {
    $fixedVar =~ s/\\//g;
    $fixedVar =~ s/\'/\\\'/g;
    $fixedVar =~ s/\"/\\\"/g;
   }
   $tempRow[$constantColumn] = $fixedVar;
   $page[$counter] = join($de,@tempRow);
  }

  # ensure constant id number has not been altered
  my $realID = ($counter - $constantRow + 1);
  if ($realID != $tempId) {
   $idErrorFlag = 1;
   print LOGFILE "\nERROR: Constant ID number ".$realID." has been modified to ".$tempId."!!!\n\n";

   # apply fix (replace with original after reset escape characters)
   $tempRow[$constantIdColumn] = $realID;
   $page[$counter] = join($de,@tempRow);
  }

  # increment counter
  $counter += 1;
 }

 print LOGFILE "\nDone checking constants:\n";
 print LOGFILE "\t".$badCount." mismatches found (known is ".$mismatchesKnown.")\n";
 if ($badCount == $mismatchesKnown) {
  print LOGFILE "Good, constants weren't modified by translators\n\n";
 }
 else {
  print LOGFILE "ERROR: Constant(s) have been modified by translators\n\n";
 }
 if ($idErrorFlag) {
  print LOGFILE "ERROR: Constant ID number(s) have been modified by translators\n\n";
 }

 return @page;
}

#
# function to build lang_languages dumpfile
# param - integer flag for utf8, array of processed file
# globals - $constantColumn, $constantRow,
#           $languageNumRow, $languageIdRow, $languageNameRow,
#           @numberConstantsLanguages, @languages
# return - output string
#
sub createLanguages() {
 my ($flag, @page) = @_;
 my $charset;
 if ($flag) {
  $charset = "utf8";
 }
 else {
  $charset = "latin1";
 }

 # create table input
 my $tempReturn;
 my $tempCounter;
 my @numberRow = split($de,$page[$languageNumRow]);
 my @idRow = split($de,$page[$languageIdRow]);
 my @nameRow = split($de,$page[$languageNameRow]);
 my @rtlRow = split($de,$page[$languageIsRtlRow]);
 $tempReturn .= "INSERT INTO `lang_languages`   (`lang_id`, `lang_code`, `lang_description`, `lang_is_rtl`) VALUES\n";
 for (my $i = $constantColumn; $i < @numberRow; $i++) {
  $tempReturn .= "(".$numberRow[$i].", '".$idRow[$i]."', '".$nameRow[$i]."', ".$rtlRow[$i]."),\n";
  $tempCounter = $numberRow[$i];

  # set up for statistics later
  push (@languages, $nameRow[$i]);
  $numberConstantsLanguages[$numberRow[$i]-1] = 0;
 }
 $tempReturn  =~ s/,\n$/;\n/;
 $tempCounter += 1;

 # create header
 my $return = "\
--
-- Table structure for table `lang_languages`
--
\n
DROP TABLE IF EXISTS `lang_languages`;
CREATE TABLE `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) NOT NULL default '',
  `lang_description` varchar(100) default NULL,
  `lang_is_rtl` TINYINT DEFAULT 0,
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=".$tempCounter." ;
\n
--
-- Dumping data for table `lang_languages`
--\n\n";

 # insert table input
 $return .= $tempReturn;

 # create footer
 $return .= "
--\n\n";

 return $return;
}

#
# function to build lang_constants dumpfile
# param - integer flag for utf, array of processed file
# globals - $constantColumn, $constantRow, $constantIdColumn, $totalConstants
# return - nothing
#
sub createConstants() {
 my (@page) = @_;
 my ($flag, @page) = @_;
 my $charset;
 if ($flag) {
  $charset = "utf8";
 }
 else {
  $charset = "latin1";
 }

 # create table input
 my $tempReturn;
 my $tempCounter;
 $tempReturn .= "INSERT INTO `lang_constants`   (`cons_id`, `constant_name`) VALUES\n";
 for (my $i = $constantRow; $i < @page; $i++) {
  my @tempRow = split($de,$page[$i]);
  my $tempId = $tempRow[$constantIdColumn];
  my $tempConstant = $tempRow[$constantColumn];
  $tempReturn .= "(".$tempId.", '".$tempConstant."'),\n";
  $tempCounter = $tempId;
 }
 $tempReturn  =~ s/,\n$/;\n/;
 $tempCounter += 1;

 # create header
 my $return = "\
--
-- Table structure for table `lang_constants`
--
\n
DROP TABLE IF EXISTS `lang_constants`;
CREATE TABLE `lang_constants` (
  `cons_id` int(11) NOT NULL auto_increment,
  `constant_name` mediumtext BINARY,
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `constant_name` (`constant_name`(100))
) ENGINE=InnoDB AUTO_INCREMENT=".$tempCounter." ;
\n
--
-- Dumping data for table `lang_constants`
--\n\n";

 # insert table input
 $return .= $tempReturn;

 # create footer
 $return .= "
--\n\n";

 # fill total constants for statistics later
 $totalConstants = $tempCounter - 1;

 return $return;
}

#
# function to build lang_definitions dumpfile
# param - integer flag for utf8, array of processed file
# globals - $constantColumn, $constantRow,
#           $languageNumRow, $constantIdColumn, @numberConstantsLanguages,
#           $totalDefinitions
# return - nothing
#
sub createDefinitions() {
 my (@page) = @_;
 my ($flag, @page) = @_;
 my $charset;
 if ($flag) {
  $charset = "utf8";
 }
 else {
  $charset = "latin1";
 }

 # create table input
 my $tempReturn;
 my $tempCounter;
 my @numberRow = split($de,$page[$languageNumRow]);
 my $counter = 1;
 for (my $i = $constantColumn + 1; $i < @numberRow; $i++) {
  for (my $j = $constantRow; $j < @page; $j++) {
   my @tempRow = split($de,$page[$j]);
   my $tempId = $tempRow[$constantIdColumn];
   my $tempDefinition = $tempRow[$i];
   my $tempLangNumber = $numberRow[$i];
   if ($tempDefinition !~ /^\s*$/) {
    $tempReturn .= "INSERT INTO `lang_definitions` VALUES (".$counter.", ".$tempId.", ".$tempLangNumber.", '".$tempDefinition."');\n";
    $tempCounter = $counter;
    $counter += 1;

    # set up for statistics
    $numberConstantsLanguages[($tempLangNumber - 1)] += 1;
   }
  }
 }
 $tempCounter += 1;

 # create header
 my $return = "\
--
-- Table structure for table `lang_definitions`
--
\n
DROP TABLE IF EXISTS `lang_definitions`;
CREATE TABLE `lang_definitions` (
  `def_id` int(11) NOT NULL auto_increment,
  `cons_id` int(11) NOT NULL default '0',
  `lang_id` int(11) NOT NULL default '0',
  `definition` mediumtext,
  UNIQUE KEY `def_id` (`def_id`),
  KEY `cons_id` (`cons_id`)
) ENGINE=InnoDB AUTO_INCREMENT=".$tempCounter." ;
\n
--
-- Dumping data for table `lang_definitions`
--\n\n";

 # insert table input
 $return .= $tempReturn;

 # create footer
 $return .= "
--\n\n";

 # fill total definitions for statistics later
 $totalDefinitions = $tempCounter - 1;

 return $return;
}

# Function to drop decimals
# param: 1st is number, 2nd is nubmer of desired decimals
sub fstr () {
 my ($value,$precision) = @_;
 if ($value == 0) {
  return "0";
 }
 my $s = sprintf("%.${precision}f", $value);
 $s =~ s/\.?0*$//;
 return $s;
}
