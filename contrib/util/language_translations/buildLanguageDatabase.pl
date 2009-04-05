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
#

use strict;

my $de = "\t";
my $filenameOut = "languageTranslations.sql";
my $logFile = "errorLog.txt";
my $constantIdColumn = 0; # 0 is lowest
my $constantColumn = 1; # 0 is lowest 
my $constantRow = 5; # 0 is lowest
my $languageNumRow = 0; # 0 is lowest
my $languageIdRow = 1; # 0 is lowest
my $languageNameRow = 2; # 0 is lowest
my $inputFilename;
my @inputFile;
my @inputFileProcessed;

# ensure only one parameter given (for now)
if (@ARGV > 1) {
 die "\nERROR: Too many parameters. Follow instructions found in buildLanguageDatabase.pl file.\n\n";
 }
elsif (@ARGV < 1) {
 die "\nERROR: Need a parameter. Follow instructions found in buildLanguageDatabase.pl file.\n\n";
}
else {
}
$inputFilename = $ARGV[0];

# open log file and output file
open(LOGFILE, ">$logFile") or die "unable to open log file";
open(OUTPUTFILE, ">$filenameOut") or die "unable to open output file";

# if comparing, then open comparison file and store in array (after set this up
# if ($compareFlag) {
# open(MYINPUTFILE, "<$comparisonFile") or die "unable to open file";
# @previousConstants = <MYINPUTFILE>;
# close(MYINPUTFILE);
# }


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

# parse lang_languages
my $outputString = createLanguages();

# parse lang_constants
$outputString .= createConstants();

# parse lang_definitions
$outputString .= createDefinitions();

print OUTPUTFILE $outputString;


#
# function to build lang_languages dumpfile
# globals - @inputFileProcessed, $constantColumn, $constantRow,
#           $languageNumRow, $languageIdRow, $languageNameRow 
# return - output string
#
sub createLanguages() {

 # create header
 my $return = "\
--
-- Table structure for table `lang_languages`
--
\n
DROP TABLE IF EXISTS `lang_languages`;
CREATE TABLE `lang_languages` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_code` char(2) character set latin1 NOT NULL default '',
  `lang_description` varchar(100) character set utf8 collate utf8_unicode_ci default NULL,
  UNIQUE KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;
\n
--
-- Dumping data for table `lang_languages`
--\n\n";

 # create table input
 my @numberRow = split($de,$inputFileProcessed[$languageNumRow]);
 my @idRow = split($de,$inputFileProcessed[$languageIdRow]);
 my @nameRow = split($de,$inputFileProcessed[$languageNameRow]);
 for (my $i = $constantColumn; $i < @numberRow; $i++) {
  $return .= "INSERT INTO `lang_languages` VALUES (".$numberRow[$i].", '".$idRow[$i]."', '".$nameRow[$i]."');\n";  
 }

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2640 ;
\n
-- 
-- Dumping data for table `lang_constants`
--\n\n";

 # create table input
 for (my $i = $constantRow; $i < @inputFileProcessed; $i++) {
  my @tempRow = split($de,@inputFileProcessed[$i]);
  my $tempId = $tempRow[$constantIdColumn];
  my $tempConstant = $tempRow[$constantColumn];
  $return .= "INSERT INTO `lang_constants` VALUES (".$tempId.", '".$tempConstant."');\n"; 
 }
     
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=174 ;
\n
-- 
-- Dumping data for table `lang_definitions`
--\n\n";

 # create table input
 my @numberRow = split($de,$inputFileProcessed[$languageNumRow]);
 my $counter = 1;
 for (my $i = $constantColumn + 1; $i < @numberRow; $i++) {
  for (my $j = $constantRow; $j < @inputFileProcessed; $j++) {
   my @tempRow = split($de,@inputFileProcessed[$j]);
   my $tempId = $tempRow[$constantIdColumn];
   my $tempDefinition = $tempRow[$i];
   if ($tempDefinition !~ /^\s*$/) {
    $return .= "INSERT INTO `lang_definitions` VALUES (".$counter.", ".$tempId.", ".$numberRow[$i].", '".$tempDefinition."');\n"; 
    $counter += 1;
   }
  }
 }

 # create footer
 $return .= "
--\n\n";

 return $return;
}
