#!/opt/local/bin/perl

#######################################################################
# Copyright (C) 2010 - Medical Information Integration, LLC
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This loads the FDA Orange Book data for drugs into the "drugs"
# table of OpenEMR. If a row already exists with the same drug name
# it is ignored, otherwise a new row is created with the drug
# trade name provided from the Orange Book data.
#
#

# Converts openemr log ouput containing deleted records and
# produces the necessary "insert into <table> values ... on duplicate key
# update... " 

# the log table's comments contains all of the deleted records in
# "tablename: field1='value1' field2='value2'" form.

# there are embedded newlines from the original web forms.  They are
# encoded to allow the split operation to work correctly, then restored.

# there is one mysql reserved word used as a column name in use that I
# have discovered.  the word 'interval' must be enclosed in backticks in
# order to be accepted by mysql.

# Use like this:
# This procedure will restore all records deleted on a given date/time) that are in the log table  
#
# First, you must look into the admin/logs screen and determine the date that the delete occured on. 
# 
# Adjust the time accordingly 
# 
# use the mysql command or similar tool to produce the restore file
# ========================================
# mysql <database>
#
# tee <outfile>;
# select distinct comments from log where event like 'delete' and  date >='<date> 00:00:00' and date <= '<date> 23:59:59';
# ========================================
# exit mysql monitor
# 
# Verify data is correct
# 
# Run: perl convert_logcomments.pl < outfile  > restore.sql
# 
# verify insert statements
# 
# To import data:
#
# mysql <database> < restore.sql
# 

use strict;
	
# %deleted is a hash that holds the read in values from the file on the
# command line.
my %deleted;

# %tablefields is simply a hash that holds the tablenames and all of the
# fields found for each table.  it makes it easier to construct the
# insert lines.
my %tablefields;

my @temp;
my $i;
my $cols;
my $x;
my $value;
my @values;
my $input;
my @records;
my @fields;
my $field;
my $table;
my $entry;
my $sql;
my $sqlpost;


# engage slurp mode aka pull the entire file into the $input variable
{
    local $/;
    $input=<>;

}

# encode embedded newlines (they are of the form "0x0d 0x0a"
# without spaces) these have been entered in at the web browser and must
# be preserved as-is.
#
$input =~s/\r\n/jasonnewline/g;

# Double backslashes need to be relegated to single backslashes
# leaning toothpick syndrome
$input=~s/\\\\/\\/g;


# fix reserved word issues
$input=~s/(interval)/`$1`/g;

@records = split /\n/, $input;


foreach (@records)
{

    # these next lines are skipping over the mysql monitor formatting
    next if ( m/^mysql/i );
    next if ( m/^\+/ );
    next if ( m/\|\s+comments\s+\|\s*$/ );
    next if ( m/^\s*$/ ); 
    next if ( m/^\d+\s+rows in/ );
    s/^\|\s*//;
    s/\s+\|\s*$//;

    # split on the FIRST colon in the line.
    ($table,$entry) = split /:\s*/,$_,2;

    $i=$#{ $deleted{$table}}+1;

    # nuke extra white space between quotes
    $entry =~ s/'(\s+)'/''/g;
    #debugging output	
    if ( 0 )
    {
	print "tablename: $table\n";
	print "$table entry #$i\n";
        print "\t\tentry: $entry\n\n";
    }

    {	# for a moment, override input record separator and chomp:
	# lopping off the last single  quote, if present.
	# otherwise, this last quote screws up the output
	local $/=q/'/;
	chomp $entry;
    }
    
    #split the line on single quotes followed by a space
    @temp = split /' /, $entry;
    foreach (@temp)
    {
	# $field is the table's field name
	# $value is the value.  
	($field,$value) = split /=/;
	$value .= "'";
	#un-encode embedded newlines
	$value =~ s/jasonnewline/\r\n/g;
	#debugging
	if (0)
	{
	    print "\t$field = $value\n";
	}
	$deleted{$table}[$i]{$field}=$value;
	$tablefields{$table}{$field} = 1;
    }	


}    

#normalized data
foreach $table (keys %deleted)
{
    #get the fields that should be present
    @fields=keys %{ $tablefields{$table} };
    for $i (0 .. $#{ $deleted{$table} } )
    {
	foreach $entry (@fields)
	{
	    if (!exists ( $deleted{$table}[$i]{$entry} ) )
	    {
		$deleted{$table}[$i]{$entry} = "";
	    }
	}	    
    }

}

# now to display properly:

foreach $table (keys %deleted)
{
#   This is the beginning of the statement
    $sql = "insert into $table ( ";
    @fields=keys %{ $tablefields{$table} };
    $sql .= join ",", @fields;
#    chop $sql;
    $sql .= ") values \n";

# and this is the middle

    for $i (0 .. $#{$deleted{$table}} )
    {	
	$values[$i]="(";
	foreach (@fields)
	{
	    $x=$deleted{$table}[$i]{$_};
	    if ( $x=~m/^\s*$/ )
	    {
		$values[$i] .= qq/'',/;
	    }
	    else
	    {
		$values[$i] .= $deleted{$table}[$i]{$_} . ",";
	    }	
	}
	chop $values[$i];
	$values[$i] .= "),\n";

    }
    
#this is the end of the statement
    # don't forget the "on duplicate key replace" part
    $sqlpost ="on duplicate key update ";
    # this composes the rest of $sqlpost
    foreach (@fields)
    {
		
	$sqlpost .= "$_=values($_), ";
    }
    chop $sqlpost;
    chop $sqlpost;
    $sqlpost .=";\n";

#now put them together: beginning, middle, end
print "$sql";
$x="";
for $i (0 .. $#values)
{
    $x.=  $values[$i] ;

}
$x =~s/,(\s)$/$1/;
print $x;
@values="";
print "$sqlpost";
print "\n";
}
