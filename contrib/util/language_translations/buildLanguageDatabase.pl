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
# This is a perl script that will build the language translation sql
# dumpfiles from the tab delimited language translation spreadsheet.
# It will create two output dumpfiles:
#   languageTranslations_utf8.sql (utf8 encoding)
#   languageTranslations_latin1.sql (latin1 encoding)
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
#  ./buildLanguageDatabase.pl openemr_language_table constants.txt
#
#  For above to work you would need to have two files in your current
#  directory:
#      openemr_language_table_utf8.tsv
#      openemr_language_table_latin1.tsv
#
#

use strict;


# Put current known constant mismatches here, which are basically
# constants that get modified during the pipeline and don't look
# like originals in the end. If this number increases, a english constant
# was likely modified in the spreadsheet, and can then use log output
# to localize and fix the problem.  As of list of 3.0.1 constants
# the known number of mismatched constants is 57 .
my $mismatchesKnown = 79;

# Hold variables to calculate language database statistics
my $totalConstants;
my $totalDefinitions;
my @languages;
my @numberConstantsLanguages;

my $de = "\t";
my $filenameOut;
my $inputFilename;
my $logFile = "log.txt";
my $constantIdColumn = 0; # 0 is lowest
my $constantColumn = 1; # 0 is lowest 
my $constantRow = 5; # 0 is lowest
my $languageNumRow = 0; # 0 is lowest
my $languageIdRow = 1; # 0 is lowest
my $languageNameRow = 2; # 0 is lowest

# variables for checking/fixing constants application 
my $checkFilename; # holds list of constants if checking
my $filenameOut_revised = "revisedSpreadsheet.tsv";
my $flagCheck = 0;
my @previousConstants;

# to hold utf8 flag
my $utf8;

# open output file
open(LOGFILE, ">$logFile") or die "unable to open log file";

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
}
elsif (@ARGV == 1) {
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

# run through twice to make a utf8 table and a latin1 table
#  revised spreadsheet, log file and stats only from the utf8 processing
for (my $i=0;$i<2;$i++) {
 # set arrays
 my @revisedFile;
 my @inputFile;
 my @inputFileProcessed;
    
 # set utf flag
 if ($i == 0) {
  # build utf8 table
  $filenameOut = "languageTranslations_utf8.sql";
  $inputFilename = $ARGV[0]."_utf8.tsv";
  $utf8 = 1;
 }
 else {
  # build latin1 table
  $filenameOut = "languageTranslations_latin1.sql";
  $inputFilename = $ARGV[0]."_latin1.tsv";
  $utf8 = 0
 }

 # open output file
 open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";
    
 # place spreadsheet into array 
 open(MYINPUTFILE2, "<$inputFilename") or die "unable to open file";
 @inputFile = <MYINPUTFILE2>;
 close(MYINPUTFILE2);

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

 # check and fix modified constants (and constant id's)
 if ($flagCheck) {
  # first create data for replacement spreadsheet if needed
  @revisedFile = checkConstants("special",@inputFileProcessed);
  # then clean data to create mysql dumpfiles
  @inputFileProcessed = checkConstants("normal",@inputFileProcessed);
 }


 my $outputString = "";
 
 # add header if utf8 encoding
 if ($utf8) {
  $outputString .= "\
--
-- Ensure UTF8 connection
--
SET NAMES utf8;\n\n";
 }

 # parse lang_languages
 $outputString .= createLanguages($utf8, @inputFileProcessed);

 # parse lang_constants
 $outputString .= createConstants($utf8, @inputFileProcessed);

 # parse lang_definitions
 $outputString .= createDefinitions($utf8, @inputFileProcessed);

 print OUTPUTFILE $outputString;

 # calculate statistics
 if ($utf8) {
  print LOGFILE "\nLanguage Statistics:\n";
  print LOGFILE "Total number of english constants: ".$totalConstants."\n";
  print LOGFILE "Total number of definitions: ".$totalDefinitions."\n";
  my $count = 0;
  my @tempArray;
  foreach my $var (@languages) {
    if ($var ne "English") {
     push (@tempArray, $var.": ".fstr((($numberConstantsLanguages[$count]/$totalConstants)*100),0)."% (".$numberConstantsLanguages[$count]." definitions)\n");
     # push (@tempArray, $var.": ".($numberConstantsLanguages[$count])." definitions\n");
   }
   $count += 1;
  }
  my @sorted_tempArray = sort { lc($a) cmp lc($b) } @tempArray;
  foreach my $var (@sorted_tempArray) {
   print LOGFILE $var;
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
 for (my $i = $constantColumn; $i < @numberRow; $i++) {
  $tempReturn .= "INSERT INTO `lang_languages` VALUES (".$numberRow[$i].", '".$idRow[$i]."', '".$nameRow[$i]."');\n";
  $tempCounter = $numberRow[$i];
     
  # set up for statistics later
  push (@languages, $nameRow[$i]);
  $numberConstantsLanguages[$numberRow[$i]-1] = 0;
 }
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
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=".$tempCounter." ;
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
 for (my $i = $constantRow; $i < @page; $i++) {
  my @tempRow = split($de,$page[$i]);
  my $tempId = $tempRow[$constantIdColumn];
  my $tempConstant = $tempRow[$constantColumn];
  $tempReturn .= "INSERT INTO `lang_constants` VALUES (".$tempId.", '".$tempConstant."');\n";
  $tempCounter = $tempId;
 }
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
  `constant_name` varchar(255) default NULL,
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `cons_name` (`constant_name`)
) ENGINE=MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=".$tempCounter." ;
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
  KEY `definition` (`definition`(100))
) ENGINE=MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=".$tempCounter." ;
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
