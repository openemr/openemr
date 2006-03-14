#!/usr/bin/perl

use strict;
use warnings;

use CGI qw(:standard);

#file templates here

#documentation
my $documentation =<<'START';

*******************************************
*      Form Generating Script 1.1.2       *
*******************************************

new for 1.1.2

Added a 'do not save' link at the top and bottom of the form.
Fixed problem with using single and double quotes in input file.
Changed deprecated PHP function mysql_escape_string to 
mysql_real_escape_string.

bugs: There may still be a problem with reserved MySQL words not
being caught.  There may be other bugs not discovered yet.

future plans: I plan on improving the output format in report.php.
For now, users can alter this form as needed.  Since formscript.pl
knows the fields to be used, it makes more sense to list them 
explicitly than to print them in a foreach loop.  I will get to
work on this soon.

1.1

This is a complete rewrite of an earlier Perl script I wrote to generate
forms for OpenEMR.  It is now all self contained within a single .pl file.
To run at the shell command line, type:

Perl formscript.pl [filename]

where filename is a text file with data relating to your form.  If you run
without a filename argument, a sample data file will be created in the same
directory named 'sample.txt' that you can use to see how to create your own.

Basically you enter one database field item per line like this:

Social History::popup_menu::smoker::non-smoker

or

Social History::radio_group::smoker::non-smoker


where the first item is the field name.  spaces within the name will convert to '_'
for the sql database field name.  If you use a SQL reserved word, the form generation
will fail and this program will notify you of the word(s) you used.

The '::' is the standard delimiter that I use between items.  The second item on the line
is the form widget type.  You can choose from: 

textfield
textarea 
checkbox
checkbox_group
radio_group
popup_menu
scrolling_list
scrolling_list_multiples

Putting a '+' at the beginning of the field name will let the form know that you want to
report negatives.  This means the following:

+cardiac_review::checkbox_group::chest pain::shortness of breath::palpitations

creates a group of checkboxes where if the user chooses the first two boxes, the database will
have the following line entered:

chest pain, shortness of breath.  Negative for palpitations.

The remaining items after the fieldname and the widget type  are the names for 
checkboxes or radio buttons or default text
for a textfield or text area.  You can also start a line with a '#' as the first character and this
will be an ignored comment line.  If you put html tags on their own lines, they will be integrated
into the form.  It will be most helpful to look at 'sample.txt' to see how this works.

This is 1.1 and is tested to the extent of installing the form and entering data within an encounter.  
Please send feedback to mail@doc99.com.  I will definitely
be fixing and improving it.

Mark Leeds


START

#info.txt 
my $info_txt=<<'START';
FORM_NAME
START

my $do_not_save=<<'START';
<?
echo "<a href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/patient_encounter.php'>[do not save]</a>";
?>
START

#new.php
my $new_php =<<'START';
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: FORM_NAME");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/FORM_NAME/save.php?mode=new" name="FORM_NAME">
<hr>
<h1> FORM_NAME </h1>
<hr>

DATABASEFIELDS

</form>
<?php
formFooter();
?>
START

#print.php
my $print_php=<<'START';
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: FORM_NAME");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/FORM_NAME/save.php?mode=new" name="my_form">
<h1> FORM_NAME </h1>
<hr>
DATABASEFIELDS
</form>
<?php
formFooter();
?>
START

#report.php
my $report_php=<<'START';
<?php
//------------report.php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
function FORM_NAME_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_FORM_NAME", $id);
if ($data) {
print "<table><tr>";
foreach($data as $key => $value) {
if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
	continue;
}
if ($value == "on") {
$value = "yes";
}
$key=ucwords(str_replace("_"," ",$key));
$output = stripslashes($value);
print "<td><span class=bold>$key: </span><span class=text>$output</span></td>";
$count++;
if ($count == $cols) {
$count = 0;
print "</tr><tr>\n";
}
}
}
print "</tr></table>";
}
?> 
START

#save.php
my $save_php=<<'START';
<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array(FIELDNAMES);
$negatives = array(NEGATIVES);
//process each field according to it's type
foreach($field_names as $key=>$val)
{
	if ($val == "checkbox")
	{
		if ($_POST[$key]) {$field_names[$key] = "positve";}
		else {$field_names[$key] = "negative";}
	}
	elseif (($val == "checkbox_group")||($val == "scrolling_list_multiples"))
	{
		$neg = '';
		if (array_key_exists($key,$negatives)) #a field requests reporting of negatives
		{
			foreach($_POST[$key] as $pos) #check positives against list
			{
				if (array_key_exists($pos, $negatives[$key]))
				{	#remove positives from list, leaving negatives
					unset($negatives[$key][$pos]);
				}
			}
			$neg = ".   Negative for ".implode(', ',$negatives[$key]);
		}
		$field_names[$key] = implode(', ',$_POST[$key]).$neg;	
	}
	else
	{
		$field_names[$key] = $_POST[$key];
	}
}

//end special processing

foreach ($field_names as $k => $var) {
$field_names[$k] = mysql_real_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_FORM_NAME", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "FORM_NAME", $newid, "FORM_NAME", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {


sqlInsert("update form_FORM_NAME set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), FIELDS where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
START

#table.sql
my $table_sql=<<'START';
CREATE TABLE IF NOT EXISTS `form_FORM_NAME` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
DATABASEFIELDS
PRIMARY KEY (id)
) TYPE=MyISAM;
START

#view.php
my $view_php =<<'START';
<!-- view.php -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: FORM_NAME");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir?>/forms/FORM_NAME/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<h1> FORM_NAME </h1>
<hr>
DATABASEFIELDS

</form>
<?php
formFooter();
?>
START

#preview.html
my $preview_html =<<'START';
<html><head>
</head>
<body> 
<form>
<hr>
<h1> FORM_NAME </h1>
<hr>
DATABASEFIELDS
</form>
</body>
</html>
START

#sample.txt
my $sample_txt =<<'START';
a1_preop_physical

chief_complaints::textarea

<h3>past surgical history</h3>
+surgical history::checkbox_group::cholecystectomy::tonsillectomy::apendectomy
<h4>other</h4>
surgical history other::textfield

<h3>past surgical history</h3>
+medical history::scrolling_list_multiples::asthma::diabetes::hypertension
<h4>other</h4>
medical history other::textfield

<h2>Allergies</h2>
+allergies::checkbox_group::penicillin::sulfa::iodine
<h4>other</h4>
allergies other::textfield

<h2>Social History</h2>
<h3>smoking</h3>
smoke history::radio_group::non-smoker::smoker
<h3>alcohol</h3>
etoh history::scrolling_list::none::occasional::daily::heavy use
START

my @reserved = ('ADD','ALL','ALTER','ANALYZE','AND','AS','ASC','ASENSITIVE','BEFORE','BETWEEN','BIGINT','BINARY','BLOB','BOTH','BY','CALL','CASCADE','CASE','CHANGE','CHAR','CHARACTER','CHECK','COLLATE','COLUMN','CONDITION','CONNECTION','CONSTRAINT','CONTINUE','CONVERT','CREATE','CROSS','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR','DATABASE','DATABASES','DAY_HOUR','DAY_MICROSECOND','DAY_MINUTE','DAY_SECOND','DEC','DECIMAL','DECLARE','DEFAULT','DELAYED','DELETE','DESC','DESCRIBE','DETERMINISTIC','DISTINCT','DISTINCTROW','DIV','DOUBLE','DROP','DUAL','EACH','ELSE','ELSEIF','ENCLOSED','ESCAPED','EXISTS','EXIT','EXPLAIN','FALSE','FETCH','FLOAT','FOR','FORCE','FOREIGN','FROM','FULLTEXT','GOTO','GRANT','GROUP','HAVING','HIGH_PRIORITY','HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IF','IGNORE','IN','INDEX','INFILE','INNER','INOUT','INSENSITIVE','INSERT','INT','INTEGER','INTERVAL','INTO','IS','ITERATE','JOIN','KEY','KEYS','KILL','LEADING','LEAVE','LEFT','LIKE','LIMIT','LINES','LOAD','LOCALTIME','LOCALTIMESTAMP','LOCK','LONG','LONGBLOB','LONGTEXT','LOOP','LOW_PRIORITY','MATCH','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MIDDLEINT','MINUTE_MICROSECOND','MINUTE_SECOND','MOD','MODIFIES','NATURAL','NOT','NO_WRITE_TO_BINLOG','NULL','NUMERIC','ON','OPTIMIZE','OPTION','OPTIONALLY','OR','ORDER','OUT','OUTER','OUTFILE','PRECISION','PRIMARY','PROCEDURE','PURGE','READ','READS','REAL','REFERENCES','REGEXP','RENAME','REPEAT','REPLACE','REQUIRE','RESTRICT','RETURN','REVOKE','RIGHT','RLIKE','SCHEMA','SCHEMAS','SECOND_MICROSECOND','SELECT','SENSITIVE','SEPARATOR','SET','SHOW','SMALLINT','SONAME','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_BIG_RESULT','SQL_CALC_FOUND_ROWS','SQL_SMALL_RESULT','SSL','STARTING','STRAIGHT_JOIN','TABLE','TERMINATED','THEN','TINYBLOB','TINYINT','TINYTEXT','TO','TRAILING','TRIGGER','TRUE','UNDO','UNION','UNIQUE','UNLOCK','UNSIGNED','UPDATE','USAGE','USE','USING','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARYING','WHEN','WHERE','WHILE','WITH','WRITE','XOR','YEAR_MONTH','ZEROFILL','ACTION','BIT','DATE','ENUM','NO','TEXT','TIME','TIMESTAMP');
my %reserved;
$reserved{uc $_}++ for @reserved;

#main program

if (@ARGV == 0)
{
	to_file('sample.txt',$sample_txt) if not -f 'sample.txt';
	print $documentation."\n";
	exit 0;
}

my $form_name = <>;
chomp($form_name);
my $check_reserved = uc $form_name;
if ($reserved{uc $check_reserved})
{
	print "You have chosen an SQL reserved word for your form name: $check_reserved.  Please try again.\n";
	exit 1;
}
$form_name =~ s/^\s+(\S)\s+$/$1/;
$form_name =~ s/\s+/_/g;
if (not -d $form_name)
{
	mkdir "$form_name" or die "Could not create directory $form_name: $!";
}
my @field_data; #the very important array of field data
chomp, push @field_data, [ split /::/ ] while <>;
my %negatives; #key=field name: these are the fields that require reporting of pertinant
		#negatives.  will only apply to checkbox_group and scrolling_list_multiples types
my @reserved_used;
#strip outer spaces from field names and field types and change inner spaces to underscores
#and check field names for SQL reserved words now
for (@field_data) 
{
	if ($_->[0] and $_->[1])
	{
		$_->[0] =~ s/^\s+(\S)\s+$/$1/;
		$_->[0] =~ s/\s+/_/g;
		$check_reserved = $_->[0] =~ m/(\w+)/ ? uc $1 : q{};
		push @reserved_used, $check_reserved if $reserved{$check_reserved};
		$_->[1] =~ s/^\s+(\S)\s+$/$1/;
		if ($_->[0] =~ /^\+/) #a leading '+' indicates to print negatives
		{		# or not checked values in a checkbox_group or scrolling_list_multiples
			$_->[0] =~ s/^\+(.*)/$1/;
			$negatives{$_->[0]}++;
		}
	}
}
if (@reserved_used)
{
	print "You have chosen the following reserved words as field names.  Please try again.\n";
	print "$_\n" for @reserved_used;
	exit 1;
}

my $text = make_form(@field_data);
my $out;

#info.txt
$out = replace($info_txt, 'FORM_NAME', $form_name);
to_file("$form_name/info.txt",$out);

#new.php
$out = replace($new_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $text);
to_file("$form_name/new.php",$out);

#print.php
$out = replace($print_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $text);
to_file("$form_name/print.php",$out);

#report.php
$out = replace($report_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $text);
to_file("$form_name/report.php",$out);

#save.php
$out = replace($save_php, 'FORM_NAME', $form_name);
$out = replace_save_php($out, @field_data);
to_file("$form_name/save.php",$out);

#view.php
$out = replace($view_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $text);
to_file("$form_name/view.php",$out);

#table.sql
$out = replace($table_sql, 'FORM_NAME', $form_name);
$out = replace_sql($out, @field_data);
to_file("$form_name/table.sql",$out);

#preview.html
$out = replace($preview_html, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $text);
to_file("$form_name/preview.html",$out);

# subs

sub replace
{
	my $text = shift;
	my %words = @_;
	$text =~ s/$_/$words{$_}/g for keys %words;
	return $text;
}


sub replace_save_php #a special case
{
	my $text = shift;
	my @fields = map {$_->[0]} grep{$_->[0] and $_->[1]} @_;
	for (@fields)
	{
		 $_ = "$_='\".\$field_names[\"$_\"].\"'";
	}
	my $fields = join ',',@fields;
	$text =~ s/FIELDS/$fields/;
	@fields = ();
	my @negatives;
	for (@_)
	{
		if ($_->[0] and $_->[1])
		{
			push @fields, "'$_->[0]' => '$_->[1]'";
			if ($negatives{$_->[0]})
			{	
				my @temp;
				my $count = 2;
				while ($count < scalar(@$_))
				{
					push @temp, "'$_->[$count]' => '$_->[$count]'"; 
					$count++;
				}
				push @negatives, "'$_->[0]' => array(".join(', ', @temp).")";	
			}
		}
	}
 	$fields = join ', ', @fields;
	$text =~ s/FIELDNAMES/$fields/;
	my $negatives = join ', ', @negatives;
	$text =~ s/NEGATIVES/$negatives/;
	return $text;
}

sub replace_sql #a special case
{
	my $text = shift;
	my @fields = map {$_->[0]} grep{$_->[0] and $_->[1]} @_;
	my $replace = '';
	$replace .= "$_ TEXT,\n" for @fields;
	$text =~ s/DATABASEFIELDS/$replace/;
	return $text;
}

sub make_form
{
	my @data = @_;
	my $return = submit(-name=>'submit form') . $do_not_save;
	$return .= "<table>";	
	for (@data)
	{
		next if not $_->[0];
		next if $_->[0] =~ /^#/; #ignore perl type comments
		if ($_->[0] =~ /^\w/ and $_->[1])	
		{
			for (@$_)
			{
				s/'/\'/g;
				s/"/\"/g;
			}
			my $field_name = shift @$_;
			my $field_type = shift @$_;
			my $label = $field_name;
			$label =~ s/_/ /g;
			if ($field_type =~ /^textfield$/)
			{
				$return .= Tr(td($label),td(textfield(-name=>$field_name, -value=> join @$_)))."\n";
			}
			elsif ($field_type =~ /^textarea$/)
			{
				$return .= Tr(td($label),td(textarea(-name=>$field_name, -rows=>4, -columns=>40, -value=> join @$_)))."\n";
			}
			elsif ($field_type =~ /^radio_group$/)
			{
				$return .= Tr(td($label),td(radio_group(-name=>$field_name, -values=>$_)))."\n";;
			}
			elsif ($field_type =~ /^checkbox$/)
			{
				$return .= Tr(td($label),td(checkbox(-name=>$field_name, -value=>'yes', -label=> join @$_)))."\n";
			}
			elsif ($field_type =~ /^checkbox_group$/)
			{
				$return .= Tr(td($label),td(checkbox_group(-name=>$field_name.'[]', -values=>$_)))."\n";
			}
			elsif ($field_type =~ /^popup_menu/)
			{
				$return .= Tr(td($label),td(popup_menu(-name=>$field_name, -values=>$_)))."\n";
			}
			elsif ($field_type =~ /^scrolling_list/)
			{
				my $mult = 'false';
				my $mult2 = '';
				$mult = 'true' if $field_type =~ /multiples$/;
				$mult2 = '[]' if $field_type =~ /multiples$/;
				$return .= Tr(td($label),td(scrolling_list(-name=>$field_name.$mult2, -values=>$_, -size=>scalar(@$_), -multiple=>$mult)))."\n";
			}
		unshift @$_, $field_type;
		unshift @$_, $field_name;
		}
		else #probably an html tag or something
		{
			$return .= "</table>";	
			$return .= $_->[0]."\n";	
			$return .= "<table>";
		}
	}		
	$return .= "</table>";
	$return .= submit(-name=>'submit form') . $do_not_save;
	return $return;
}

sub to_file
{
	my $filename = shift;
	my $string = shift;
	my $file;
	open $file, '>', $filename or die "cannot open $filename: $!";
	print $file $string;
	close $file or die "cannot close $filename: $!";
}
