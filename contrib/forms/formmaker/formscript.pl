#!/usr/bin/perl

use strict;
use warnings;

use CGI qw(:standard);

#if -noxl command line option is used, xl function will not be put into form
use Getopt::Long;
my $noxl = '';
my $bigtable = '';
my @redirect; #this array is for data if redirect field defined to send form data to other form...
my $redirect_string = '';
GetOptions('noxl' => \$noxl, 'bigtable' => \$bigtable);

#file templates here

#documentation
my $documentation =<<'START';

*************************************
*      Form Generating Script 3.0   *
*************************************

To run at the shell command line, type:

Perl formscript.pl [filename]

where filename is a text file with data relating to your form.  If you run
without a filename argument, a sample data file will be created in the same
directory named 'sample.txt' that you can use to see how to create your own.

The first line you enter in your textfile is the name of the form.
In the example this is "physical_sample"

Basically you enter one database field item per line like this:

Social History::popup_menu::smoker::non-smoker

or

Social History::radio_group::smoker::non-smoker


where the first item is the field name, the second item is the widget type, and Nth items are values.
spaces within the name will convert to '_'
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
date

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

By default now, the xl function which is for performing language translation is used.  To disable this feature in creating a form, use the commandline option -noxl as in:

./formscript.pl --noxl sample.txt

The bigtable option.  This commandline option ignores anything in the template file that is not a field and creates the form layout in one tidy table.  This may look nicer.  You can rebuild the form with and without this option without breaking anything even after the form is installed and in use.

./formscript.pl --bigtable sample.txt

Redirect option.  This option is set within the template file by defining a redirect field just like any other field.  The redirect keyword is followed by the redirect keyword again and then by the table name to submit data to.  That is followed by the database column name to save data to.  All form data will be combined into one string and submitted to this table.  Optionally, you may then list other columns and a string to submit for each as a constant.  Example:

redirect::redirect::CAMOS::content::category::exam::subcategory::by_dx::item::bronchitis

Please send feedback to drleeds@gmail.com.


START

#info.txt 
my $info_txt=<<'START';
FORM_NAME
START

#date header
#if there is one or more date fields, this will need to be inserted into the body of new.php
#for the popup javascript calendar
my $date_field_exists = 0;
my $date_header =<<'START';
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'> var mypcc = '1'; </script>
START

#new.php
my $new_php =<<'START';
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: FORM_NAME");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
DATE_HEADER
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'>[do not save]</a>
<form method=post action="<?echo $rootdir;?>/forms/FORM_NAME/save.php?mode=new" name="FORM_NAME" onsubmit="return top.restoreSession()">
<hr>
<h1>FORM_NAME</h1>
<hr>
DATABASEFIELDS
</form>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'>[do not save]</a>
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
<form method=post action="<?echo $rootdir;?>/forms/FORM_NAME/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()">
<h1> FORM_NAME </h1>
<hr>
DATABASEFIELDS
</form>
<?php
formFooter();
?>
START

#report.php
#The variable $mykey and $myval are used by the xl_fix function for replacement purposes
my $report_php=<<'START';
<?php
//------------report.php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
function FORM_NAME_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_FORM_NAME", $id);
if ($data) {
print "<hr><table><tr>";
foreach($data as $key => $value) {
if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
	continue;
}
if ($value == "on") {
$value = "yes";
}
$key=ucwords(str_replace("_"," ",$key));
$mykey = $key.": ";
$myval = stripslashes($value);
print "<td><span class=bold>".$mykey."</span><span class=text>".$myval."</span></td>";
$count++;
if ($count == $cols) {
$count = 0;
print "</tr><tr>\n";
}
}
}
print "</tr></table><hr>";
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
  $pos = '';
  $neg = '';
	if ($val == "checkbox")
	{
		if ($_POST[$key]) {$field_names[$key] = "yes";}
		else {$field_names[$key] = "negative";}
	}
	elseif (($val == "checkbox_group")||($val == "scrolling_list_multiples"))
	{
		if (array_key_exists($key,$negatives)) #a field requests reporting of negatives
		{
                  if ($_POST[$key]) 
                  {
			foreach($_POST[$key] as $var) #check positives against list
			{
				if (array_key_exists($var, $negatives[$key]))
				{	#remove positives from list, leaving negatives
					unset($negatives[$key][$var]);
				}
			}
                  }
			if (is_array($negatives[$key]) && count($negatives[$key])>0) 
			{
				$neg = "Negative for ".implode(', ',$negatives[$key]).'.';
			}
		}
		if (is_array($_POST[$key]) && count($_POST[$key])>0) 
		{
			$pos = implode(', ',$_POST[$key]);
		}
		if($pos) {$pos = 'Positive for '.$pos.'.  ';}
		$field_names[$key] = $pos.$neg;	
	}
	else
	{
		$field_names[$key] = $_POST[$key];
	}
        if ($field_names[$key] != '')
        {
//          $field_names[$key] .= '.';
          $field_names[$key] = preg_replace('/\s*,\s*([^,]+)\./',' and $1.',$field_names[$key]); // replace last comma with 'and' and ending period
        } 
}

//end special processing
if(get_magic_quotes_gpc()) {
  foreach ($field_names as $k => $var) {
    $field_names[$k] = stripslashes($var);
  }
}
foreach ($field_names as $k => $var) {
  #if (strtolower($k) == strtolower($var)) {unset($field_names[$k]);}
  $field_names[$k] = mysql_real_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
reset($field_names);
NOREDIRECT
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
START

#save_noredirect
#if there is no redirect command, replace NOREDIRECT with this
my $noredirect=<<'START';
$newid = formSubmit("form_FORM_NAME", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "FORM_NAME", $newid, "FORM_NAME", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_FORM_NAME set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), FIELDS where id=$id");
}
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
$obj = formFetch("form_FORM_NAME", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form.

function chkdata_Txt(&$obj, $var) {
        return htmlentities($obj{"$var"});
}
function chkdata_Date(&$obj, $var) {
        return htmlentities($obj{"$var"});
}
function chkdata_CB(&$obj, $nam, $var) {
	if (preg_match("/Negative.*$var/",$obj{$nam})) {return;} else {return "checked";}
}
function chkdata_Radio(&$obj, $nam, $var) {
	if (strpos($obj{$nam},$var) !== false) {return "checked";}
}
 function chkdata_PopOrScroll(&$obj, $nam, $var) {
	if (preg_match("/Negative.*$var/",$obj{$nam})) {return;} else {return "selected";}
}

?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
DATE_HEADER
<form method=post action="<?echo $rootdir?>/forms/FORM_NAME/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form" onsubmit="return top.restoreSession()">
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
physical_sample

chief_complaints::textarea

<h3>past surgical history</h3>
+surgical history::checkbox_group::cholecystectomy::tonsillectomy::apendectomy::hernia
<h4>other</h4>
surgical history other::textfield

<h3>past medical history</h3>
+medical history::scrolling_list_multiples::asthma::diabetes::hypertension::GERD
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
<h3>last mammogram</h3>
last mammogram::date
START

my @reserved = ('ADD','ALL','ALTER','ANALYZE','AND','AS','ASC','ASENSITIVE','BEFORE','BETWEEN','BIGINT','BINARY','BLOB','BOTH','BY','CALL','CASCADE','CASE','CHANGE','CHAR','CHARACTER','CHECK','COLLATE','COLUMN','CONDITION','CONNECTION','CONSTRAINT','CONTINUE','CONVERT','CREATE','CROSS','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR','DATABASE','DATABASES','DAY_HOUR','DAY_MICROSECOND','DAY_MINUTE','DAY_SECOND','DEC','DECIMAL','DECLARE','DEFAULT','DELAYED','DELETE','DESC','DESCRIBE','DETERMINISTIC','DISTINCT','DISTINCTROW','DIV','DOUBLE','DROP','DUAL','EACH','ELSE','ELSEIF','ENCLOSED','ESCAPED','EXISTS','EXIT','EXPLAIN','FALSE','FETCH','FLOAT','FOR','FORCE','FOREIGN','FROM','FULLTEXT','GOTO','GRANT','GROUP','HAVING','HIGH_PRIORITY','HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IF','IGNORE','IN','INDEX','INFILE','INNER','INOUT','INSENSITIVE','INSERT','INT','INTEGER','INTERVAL','INTO','IS','ITERATE','JOIN','KEY','KEYS','KILL','LEADING','LEAVE','LEFT','LIKE','LIMIT','LINES','LOAD','LOCALTIME','LOCALTIMESTAMP','LOCK','LONG','LONGBLOB','LONGTEXT','LOOP','LOW_PRIORITY','MATCH','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MIDDLEINT','MINUTE_MICROSECOND','MINUTE_SECOND','MOD','MODIFIES','NATURAL','NOT','NO_WRITE_TO_BINLOG','NULL','NUMERIC','ON','OPTIMIZE','OPTION','OPTIONALLY','OR','ORDER','OUT','OUTER','OUTFILE','PRECISION','PRIMARY','PROCEDURE','PURGE','READ','READS','REAL','REFERENCES','REGEXP','RENAME','REPEAT','REPLACE','REQUIRE','RESTRICT','RETURN','REVOKE','RIGHT','RLIKE','SCHEMA','SCHEMAS','SECOND_MICROSECOND','SELECT','SENSITIVE','SEPARATOR','SET','SHOW','SMALLINT','SONAME','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_BIG_RESULT','SQL_CALC_FOUND_ROWS','SQL_SMALL_RESULT','SSL','STARTING','STRAIGHT_JOIN','TABLE','TERMINATED','THEN','TINYBLOB','TINYINT','TINYTEXT','TO','TRAILING','TRIGGER','TRUE','UNDO','UNION','UNIQUE','UNLOCK','UNSIGNED','UPDATE','USAGE','USE','USING','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARYING','WHEN','WHERE','WHILE','WITH','WRITE','XOR','YEAR_MONTH','ZEROFILL','ACTION','BIT','DATE','ENUM','NO','TEXT','TIME','TIMESTAMP');
my %reserved;
$reserved{$_}++ for @reserved;     # Shortened syntax for assigning value of 1 to each associative element in array.
					     # IE:  UNLOCK = 1, WRITE = 1, ETC... Associative array.




#*********************************************************************************
#******************************** MAIN PROGRAM ***********************************
#*********************************************************************************

if (@ARGV == 0)
{
	to_file('sample.txt',$sample_txt) if not -f 'sample.txt';
	print $documentation."\n";
	exit 0;
}
my $template_file_name = $ARGV[0];
my $form_name = <>;
chomp($form_name);
my $compare = $form_name;
$compare =~ tr/[a-z]/[A-Z]/;
if ($reserved{$compare})
{
	print "You have chosen an SQL reserved word for your form name: $form_name.  Please try again.\n";
	exit 1;
}
$form_name =~ s/^\s+(\S)\s+$/$1/;	#Remove spaces from beginning and end of form name and save $1 which is a backreference to subexpression ("\S" MEANS Any non-whitespace character)) to $form_name.
$form_name =~ s/\s+/_/g;		#Substitute all blank spaces with _ globally --> g means globally.
if (! -e $form_name)
{
	mkdir "$form_name" or die "Could not create directory $form_name: $!";
}
my @field_data;                                                                                  #the very important array of field data
chomp, push @field_data, [ split /::/ ] while <>;		#while <> continues through currently open file (parameter from command line invoking perl ie: "subjective.txt"), chomping return characters, splitting on :: or more (::::) and putting into field_data array.
my %negatives;               #key=field name: these are the fields that require reporting of pertinant
		                     #negatives.  will only apply to checkbox_group and scrolling_list_multiples types
my @reserved_used;
#strip outer spaces from field names and field types and change inner spaces to underscores
#and check field names for SQL reserved words now
for (@field_data) 
{
	if ($_->[0] and $_->[1])	#$_->[0] is field name and $_->[1] is field type.  IE:  @field_data[4]->[0] and @field_data[4]->[1]
	{
		$_->[0] =~ s/^\s+(\S)\s+$/$1/;        #\s means spaces, \S means non spaces. (\S) creates backreference pointed to by $1 at end. ***FIELD NAME***
		$_->[0] = lc $_->[0];		#MAKE SURE FILENAMES ARE ALL LOWERCASE (to avoid problems later)
		$_->[0] =~ s/\s+|-+/_/g;                 # So now @field_data[1]->[0] contains the field name without spaces at beginning or end and this replaces spaces with _  ie: "field type" becomes "field_type"
		push @reserved_used, $_->[0] if $reserved{$_->[0]};
		$_->[1] =~ s/^\s+(\S)\s+$/$1/;
		if ($_->[0] =~ /^\+/) #a leading '+' indicates to print negatives
		{		# or not checked values in a checkbox_group or scrolling_list_multiples
			$_->[0] =~ s/^\+(.*)/$1/;
			$negatives{$_->[0]}++;      #Shortened syntax for putting $field_name, 1 into "negatives" associative array.
							    #Same as %negatives = (%negatives, $_->[0], 1)
		}
	}
}
if (@reserved_used)
{
	print "You have chosen the following reserved words as field names.  Please try again.\n";
	print "$_\n" for @reserved_used;
	exit 1;
}



#****************************************************************************
#**Send field data to the Make_form subroutine and receive it back as $text**
#****************************************************************************

my $make_form_results = make_form(@field_data);
my $out;



#***************************************************************************
#**************************REPLACEMENT SECTION******************************
#***************************************************************************
#***This section replaces the 'PLACE_HOLDERS' in the $whatever.php above.***
#***$text holds the results from the "make_form" subroutine below.       ***
#***************************************************************************


#info.txt
$out = replace($info_txt, 'FORM_NAME', $form_name); #Custom delcared sub 3 parameters
to_file("$form_name/info.txt",$out);

#new.php
$out = replace($new_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $make_form_results);
$out = xl_fix($out);
if ($date_field_exists) {
  $out = replace($out,'DATE_HEADER',$date_header);
}
to_file("$form_name/new.php",$out);

#print.php
$out = replace($print_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $make_form_results);
$out = xl_fix($out);
to_file("$form_name/print.php",$out);

#report.php
$out = replace($report_php, 'FORM_NAME', $form_name);	#Here's where we set $out = to it's corresponding input (whatever_php) and replace the place holder 'FORM_NAME' with the correct $form_name
$out = replace($out, 'DATABASEFIELDS', $make_form_results);		#Then replace 'DATABASEFIELDS' in 'whatever_php' with $make_form_results, generated from make_form subroutine.
$out = xl_fix2($out);
to_file("$form_name/report.php",$out);

#save.php
$out = replace($save_php, 'NOREDIRECT', $noredirect) if not $redirect_string;
$out = replace($save_php, 'NOREDIRECT', $redirect_string) if $redirect_string;
$out = replace($out, 'FORM_NAME', $form_name);
$out = replace_save_php($out, @field_data);		#Or send it to a special case where extra things can be added to the output. ("replace_save_php" is down below under "sub-routines")
to_file("$form_name/save.php",$out);

#view.php
$out = replace($view_php, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $make_form_results);
#$out = replace($out, 'FIELDARRAY', "'".join("'=>1,'",map {shift @$_;shift @$_;shift @$_;join("'=>1,'",@$_)} grep{$_->[3]} @field_data)."'=>1");
#$out = replace($out, 'FIELDARRAY', "'".join("','",map {shift @$_;shift @$_;shift @$_;join("','",@$_)} grep{$_->[3]} @field_data)."'");
$out = replace_view_php($out);
$out = xl_fix($out);
if ($date_field_exists) {
  $out = replace($out,'DATE_HEADER',$date_header);
}
to_file("$form_name/view.php",$out);

#table.sql
$out = replace($table_sql, 'FORM_NAME', $form_name);
$out = replace_sql($out, @field_data);
to_file("$form_name/table.sql",$out);

#preview.html
$out = replace($preview_html, 'FORM_NAME', $form_name);
$out = replace($out, 'DATABASEFIELDS', $make_form_results);
to_file("$form_name/preview.html",$out);

#copy template file to form directory



#******************************************************************
#************************* SUBROUTINES ***************************
#******************************************************************

sub replace
{
	my $text = shift;  #This shifts through the supplied arguments ($whatever_php, 'FORM_NAME', and $form_name)
				#This $text is a LOCAL variable.  Does not overwrite other $make_form_results
				#Shift starts with the first value.  If variable (as in $whatever_php) expands and goes through line by line
	my %words = @_;
	$text =~ s/$_/$words{$_}/g for keys %words;
	return $text;
}


sub replace_save_php #a special case
{
	my $text = shift;
	my @fields = map {$_->[0]} grep{$_->[0] and $_->[1]} @_;  #Checks to see that Field_name and Field_type exist --Grep statement and map to @array.
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
				my $count = 3;
				while ($count < scalar(@$_))
				{
					push @temp, "'$_->[$count]' => '$_->[$count]'"; 
					$count++;
				}
				push @negatives, "'$_->[0]' => array(".join(',', @temp).")";	
			}
		}
	}
 	$fields = join ',', @fields;
	$text =~ s/FIELDNAMES/$fields/;
	my $negatives = join ',', @negatives;
	$text =~ s/NEGATIVES/$negatives/;
	return $text;
}

sub replace_sql #a special case
{
	my $text = shift;
	my $replace = '';
        for (grep{$_->[0] and $_->[1]} @_) 
        {
	  next if $_->[0] eq 'redirect';
	  $replace .= $_->[0]." TEXT,\n" if $_->[1] !~ /^date$/;
	  $replace .= $_->[0]." DATE,\n" if $_->[1] =~ /^date$/;
        }
	$text =~ s/DATABASEFIELDS/$replace/;
	return $text;
}

sub replace_view_php           #a special case  (They're all special cases aren't they? ;^ )  )
{
	my $text = shift;
	$text =~ s/(<\/label>)\s?(<label>)/$1\n$2/g;		#MAKE LAYOUT MORE READABLE. NEWLINE FOR EACH <LABEL> TAG
	my @text = split (/\n/,$text);				#PUT EACH LINE OF TEXT INTO AN ARRAY SPLIT ON NEWLINE (\n)
	my @temp = ();
	my $selname = "";
	foreach (@text)
	{
		if ($_ =~ /<select name="(\w*)/) #SELECT NAME FOR POPUP & SCROLLING MENUS.
			{
			  $selname = $1;
			  goto go;
			}
	
	  goto go if $_ =~ s/(<textarea\sname=")([\w\s]+)("[\w\s="]*>)/$1$2$3<?php \$result = chkdata_Txt(\$obj,"$2"); echo \$result;?>/;  #TEXTAREA
		 
	  goto go if $_ =~ s/(<input\stype="text"\s)(name=")([\w\s]+)(")([^>]*)/$1$2$3$4 value="<?php \$result = chkdata_Txt(\$obj,"$3"); echo \$result;?>"/;               #TEXT
		 
	  goto go if $_ =~ s/(<input\stype="checkbox"\sname=")([\w\s]+)(\[\])("\svalue=")([\w\s]+)(")([^>]*)/$1$2$3$4$5$6 <?php \$result = chkdata_CB(\$obj,"$2","$5"); echo \$result;?>/;   #CHECKBOX-GROUP
		 
	  goto go if $_ =~ s/(<input\stype="checkbox"\sname=")([\w\s]+)("\svalue=")([\w\s]+)(")([^>]*)/$1$2$3$4$5 <?php \$result = chkdata_CB(\$obj,"$2","$4"); echo \$result;?>/; #CHECKBOX
		 
	  goto go if $_ =~ s/(<input\stype="radio"\sname=")([\w\s]+)("\svalue=")([\w\s]+)(")([^>]*)/$1$2$3$4$5 <?php \$result = chkdata_Radio(\$obj,"$2","$4"); echo \$result;?>/; #RADIO-GROUP

	  goto go if $_ =~ s/(<option value=")([\w\s]+)(")/$1$2$3 <?php \$result = chkdata_PopOrScroll(\$obj,"$selname","$2"); echo \$result;?>/g; #SCROLLING-LISTS-BOTH & POPUP-MENU
	  goto go if $_ =~ s/(.*?)name='(.*?)'(.*?)datekeyup(.*?)dateblur(.*?)\/>/$1name='$2'$3datekeyup$4dateblur$5 value="<?php \$result = chkdata_Date(\$obj,"$2"); echo \$result;?>">/; #DATE

		go:	push (@temp, $_, "\n");

	}

	$text = "@temp";
	return $text;
}


sub make_form #MAKE_FORM
{
	my @data = @_;
	my $return = submit(-name=>'submit form');
	$return .= '<br>'."\n";
	$return .= "\n".'<table>'."\n\n" if $bigtable;
	for (@data)
	{
		next if not $_->[0];		#Go to next iteration of loop if no "field name"
		next if $_->[0] =~ /^#/;	#ignore perl type comments
		if ($_->[0] =~ /^\w/ and $_->[1])	#Check that the "field name" contains valid characters and that there is a "field type" in array iteration.
		{
			my $field_name = shift @$_;	#Get current field_name for iteration of array.  Shift removes it from the array and moves to next.
			my $field_type = shift @$_;
			my $label = $field_name;
			$label =~ s/_/ /g;
			$label = ucfirst($label);
			$return .= "\n".'<table>'."\n\n" if not $bigtable;
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
				$return .= Tr(td($label),td(radio_group(-name=>$field_name, -values=>$_, -default=>'-')))."\n";;
			}
			elsif ($field_type =~ /^checkbox$/)
			{
				$return .= Tr(td($label),td(checkbox(-name=>$field_name, -value=>'yes', -label=> join @$_)))."\n";
			}
			elsif ($field_type =~ /^checkbox_group$/)
			{
				$return .= Tr(td($label),td(checkbox_group(-name=>$field_name.'[]', -values=>$_)))."\n";
			}
			elsif ($field_type =~ /^popup_menu$/)
			{
				$return .= Tr(td($label),td(popup_menu(-name=>$field_name, -values=>$_)))."\n";
			}
			elsif ($field_type =~ /^scrolling_list$/)
			{
				$return .= Tr(td($label),td(scrolling_list(-name=>$field_name, -values=>$_, -size=>scalar(@$_))))."\n";
			}
			elsif ($field_type =~ /^scrolling_list_multiples/)
			{
			  	$return .= Tr(td($label),td(scrolling_list(-name=>$field_name.'[]', -values=>$_, -size=>scalar(@$_), -multiple=>'true')))."\n";
			}
			elsif ($field_type =~ /^header/)
			{
			  	$return .= Tr(td($label),td(hidden(-name=>$field_name, -value=>$field_name)))."\n";
			}
			elsif ($field_type =~ /^date$/)
			{
$date_field_exists = 1;
$return .= <<"START";
<tr><td>
<span class='text'><?php xl('$label (yyyy-mm-dd): ','e') ?></span>
</td><td>
<input type='text' size='10' name='$field_name' id='$field_name' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event' />
<img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
id='img_$field_name' border='0' alt='[?]' style='cursor:pointer'
title='Click here to choose a date'>
<script>
Calendar.setup({inputField:'$field_name', ifFormat:'%Y-%m-%d', button:'img_$field_name'});
</script>
</td></tr>
START
			}
			elsif ($field_type =~ /^redirect/)
			{
				#you could argue that this does not belong here and maybe more appropriately on the command line.
				#I just wanted to make it so redirect could be part of the template file and leverage existing functionality.
				@redirect = (@$_);
				my $formname = shift(@redirect);
				my $mainfield = shift(@redirect);
				my $field_constants;
				if (@redirect) {
					my %temp = @redirect;	
					foreach(keys %temp) {
						$field_constants .= "'$_' => '".$temp{$_}."', ";
					}
					$field_constants =~ s/, $/\)/;
					$field_constants = "array('$mainfield' => \$data,".$field_constants;
				} else {
					$field_constants = "array('$mainfield' => \$data)";
				}
#				my $t1 = "<tr><td><b>";
#				my $t2 = "</b></td></tr>";
#				my $t3 = "<tr><td>";
#				my $t4 = "</td>><td>";
#				my $t5 = "</tr></td>";
				my ($t1,$t2,$t3,$t4,$t5) = ('','','','','');
				$redirect_string = "\n}\n" .
#					"\$data = \"<table>\\n\";\n" .
					"foreach (\$field_names as \$k => \$v) {\n" .
					"  if (\$k == \$v && \$v != '') {\/\/header\n" .
					"    \$data .= \"$t1\\n\\n\".\$k.\"$t2\\n\\n\";\n" .
					"  }\n" .
					"  elseif (\$v != '') {\n" .
					"    \$data .= \"$t3\".\$k.\": $t4\".\$v.\"$t5\\n\";\n" .
					"  }\n" .
					"}\n" .
#					"\$data .= \"</table>\\n\";\n" .
					"\$newid = formSubmit(\"form_$formname\", $field_constants, \$_GET[\"id\"], \$userauthorized);\n" .
					"addForm(\$encounter, \"$formname\", \$newid, \"$formname\", \$pid, \$userauthorized);"
			}
			unshift @$_, $label;
			unshift @$_, $field_type;
			unshift @$_, $field_name;
			$return .= "\n".'</table>'."\n" if not $bigtable;
		}
		elsif (!$bigtable) #probably an html tag or something -- Get to this point if no Field_name and Field_type found in array.
		{

			  if ($_->[0] !~ /<br>\s*$|<\/td>\s*$|<\/tr>\s*$|<\/p>\s*$/) {
			    $return .= '<br>'."\n";
			  }

			  $return .= $_->[0]."\n";	
			  
			  
		}
	}		
	$return .= "<table>" if not $bigtable;
	$return .= "</table>";
	$return .= submit(-name=>'submit form');
	return $return;
}

#***********************************************************************************************************
#**Receive 'full file path' and '$out' (finished output) from REPLACEMENT SECTION above and write to file.**
#***********************************************************************************************************

sub to_file
{
	my $filename = shift;
	my $string = shift;
	my $file;
	open $file, '>', $filename or die "cannot open $filename: $!";
	print $file $string;
	close $file or die "cannot close $filename: $!";
}
sub xl_fix #make compliant with translation feature
{
	my $string = shift;
	return $string if $noxl;
	$string =~ s/>([^\s][^<>]+?)<\//> <\? xl("$1",'e') \?> <\//gs; 
        return $string;
}
sub xl_fix2 #make compliant with translation feature for report.php
{
	my $string = shift;
	return $string if $noxl;
	$string =~ s/\.(\$\w+)\./\.xl("$1")\./gs; 
        return $string;
}
