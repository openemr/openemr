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
# dumpfile from the tab delimited language translation spreadsheet.
# Output will go to languageTranslations.sql and errors will be logged
# to errorLog.txt.
#  
#  Example commands:
#
#  -Below command will build the sql dumpfile from given tsv 
#   language spreadsheet:
#  ./buildLanguageDatabase.pl openemr_language_table.tsv
#
#  -Below command will build the sql dumpfile from given tsv
#   language spreadsheet and compare with a constants list to
#   ensure constants didn't get edited (output will go to
#   the log file:
#  ./buildLanguageDatabase.pl openemr_language_table.tsv constants.txt
#

use strict;

# Put current known constant mismatches here, which are basically
# constants that get modified during the pipeline and don't look
# like originals in the end. If this number increases, a english constant
# was likely modified in the spreadsheet, and can then use log output
# to localize and fix the problem.  As of list of 3.0.1 constants
# the known number of mismatched constants is 57 .
my $mismatchesKnown = 57;

my $de = "\t";
my $filenameOut = "languageTranslations.sql";
my $logFile = "log.txt";
my $constantIdColumn = 0; # 0 is lowest
my $constantColumn = 1; # 0 is lowest 
my $constantRow = 5; # 0 is lowest
my $languageNumRow = 0; # 0 is lowest
my $languageIdRow = 1; # 0 is lowest
my $languageNameRow = 2; # 0 is lowest
my $inputFilename;
my $checkFilename; # holds list of constants if checking
my $flagCheck = 0;
my @previousConstants;
my @inputFile;
my @inputFileProcessed;

# open output file
open(LOGFILE, ">$logFile") or die "unable to open log file";

# ensure only one parameter given (for now)
if (@ARGV > 2) {
 die "\nERROR: Too many parameters. Follow instructions found in buildLanguageDatabase.pl file.\n\n";
 }
elsif (@ARGV < 1) {
 die "\nERROR: Need a parameter(s). Follow instructions found in buildLanguageDatabase.pl file.\n\n";
}
elsif (@ARGV == 2) {
 $flagCheck = 1;
 $inputFilename = $ARGV[0];
 $checkFilename = $ARGV[1];
}
elsif (@ARGV == 1) {
 $inputFilename = $ARGV[0];
}
else {
 print LOGFILE "ERROR: with parameters\n\n";
}

# open output file
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# if checking, then open check file and store in array (after set this up
if ($flagCheck) {
 open(MYINPUTFILE, "<$checkFilename") or die "unable to open file";
 @previousConstants = <MYINPUTFILE>;
 close(MYINPUTFILE);
}

# place spreadsheet into array 
open(MYINPUTFILE2, "<$inputFilename") or die "unable to open file";
@inputFile = <MYINPUTFILE2>;
close(MYINPUTFILE2);

# do check first if list of constants was given
if ($flagCheck) {
 checkConstants();
}


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

# parse lang_languages
my $outputString = createLanguages();

# parse lang_constants
$outputString .= createConstants();

# parse lang_definitions
$outputString .= createDefinitions();

print OUTPUTFILE $outputString;


    
#
# function to compare to original constants
# globals - @previousConstants, @inputFile, $constantRow, $de, LOGFILE,
#           $constantIdColumn, $constantColumn
# return - none
#
sub checkConstants () {

 print LOGFILE "Checking constants:\n\n";
 my $counter = $constantRow;
 my $badCount = 0;
 foreach my $var (@previousConstants) {
  chomp($var);
  my @tempRow = split($de,@inputFile[$counter]);
  my $tempId = $tempRow[$constantIdColumn];
  my $tempConstant = $tempRow[$constantColumn];
  if ($var ne $tempConstant) {
   print LOGFILE "Following constant not same:\n";
   print LOGFILE "\toriginal- val:$var\n";
   print LOGFILE "\tspreadsheet- ID:$tempId val:$tempConstant\n";
   $badCount += 1;
  }

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
    
 return;
}

#
# function to build lang_languages dumpfile
# globals - @inputFileProcessed, $constantColumn, $constantRow,
#           $languageNumRow, $languageIdRow, $languageNameRow 
# return - output string
#
sub createLanguages() {

 # create table input
 my $tempReturn;
 my $tempCounter;
 my @numberRow = split($de,$inputFileProcessed[$languageNumRow]);
 my @idRow = split($de,$inputFileProcessed[$languageIdRow]);
 my @nameRow = split($de,$inputFileProcessed[$languageNameRow]);
 for (my $i = $constantColumn; $i < @numberRow; $i++) {
  $tempReturn .= "INSERT INTO `lang_languages` VALUES (".$numberRow[$i].", '".$idRow[$i]."', '".$nameRow[$i]."');\n";
  $tempCounter = $numberRow[$i];
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
  `lang_code` char(2) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `lang_description` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=".$tempCounter." ;
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
# globals - @inputFileProcessed, $constantColumn, $constantRow, $constantIdColumn
# return - nothing
#
sub createConstants() {

 # create table input
 my $tempReturn;
 my $tempCounter; 
 for (my $i = $constantRow; $i < @inputFileProcessed; $i++) {
  my @tempRow = split($de,@inputFileProcessed[$i]);
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
  `constant_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  UNIQUE KEY `cons_id` (`cons_id`),
  KEY `cons_name` (`constant_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=".$tempCounter." ;
\n
-- 
-- Dumping data for table `lang_constants`
--\n\n";

 # insert table input
 $return .= $tempReturn;
     
 # create footer
 $return .= "
--\n\n";

 return $return;
}

#
# function to build lang_definitions dumpfile
# globals - @inputFileProcessed, $constantColumn, $constantRow,
#           $languageNumRow, $constantIdColumn, 
# return - nothing
#
sub createDefinitions() {

 # create table input
 my $tempReturn;
 my $tempCounter; 
 my @numberRow = split($de,$inputFileProcessed[$languageNumRow]);
 my $counter = 1;
 for (my $i = $constantColumn + 1; $i < @numberRow; $i++) {
  for (my $j = $constantRow; $j < @inputFileProcessed; $j++) {
   my @tempRow = split($de,@inputFileProcessed[$j]);
   my $tempId = $tempRow[$constantIdColumn];
   my $tempDefinition = $tempRow[$i];
   if ($tempDefinition !~ /^\s*$/) {
    $tempReturn .= "INSERT INTO `lang_definitions` VALUES (".$counter.", ".$tempId.", ".$numberRow[$i].", '".$tempDefinition."');\n";
    $tempCounter = $counter;
    $counter += 1;
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
  `definition` mediumtext character set utf8 collate utf8_unicode_ci,
  UNIQUE KEY `def_id` (`def_id`),
  KEY `definition` (`definition`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=".$tempCounter." ;
\n
-- 
-- Dumping data for table `lang_definitions`
--\n\n";

 # insert table input
 $return .= $tempReturn;

 # create footer
 $return .= "
--\n\n";

 return $return;
}
