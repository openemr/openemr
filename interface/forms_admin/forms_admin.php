<?
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
if ($_GET['method'] == "enable"){
	updateRegistered ( $_GET['id'], "state=1" );
}
elseif ($_GET['method'] == "disable"){
	updateRegistered ( $_GET['id'], "state=0" );
}
elseif ($_GET['method'] == "install_db"){
	$dir = getRegistryEntry ( $_GET['id'], "directory" );
	if (installSQL ("$srcdir/../interface/forms/{$dir['directory']}"))
		updateRegistered ( $_GET['id'], "sql_run=1" );
	else
		$err = "ERR: could not open table.sql, broken form?";
}
elseif ($_GET['method'] == "register"){
	registerForm ( $_GET['name'] ) or $err="err while registering form!";
}
$bigdata = getRegistered("%") or $bigdata = false;


//START OUT OUR PAGE....
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<span class="title">Forms Administration</span>
<br><br>


<?php //ERROR REPORTING
if ($err)
	echo "<span class=bold>$err</span><br><br>\n";
?>


<?php //REGISTERED SECTION ?>
<span class=bold>Registered</span><br>
<table border=0 cellpadding=1 cellspacing=2 width="500">
<?php
$color="#CCCCCC";
if ($bigdata != false)
foreach($bigdata as $registry)
{
	?>
	<tr>
		<td bgcolor="<?=$color?>" width="2%">
			<span class=text><?=$registry['id'];?></span> 
		</td>
		<td bgcolor="<?=$color?>" width="30%">
			<span class=bold><?=$registry['name'];?></span> 
		</td>
		<?php
			if ($registry['sql_run'] == 0)
				echo "<td bgcolor='$color' width='10%'><span class='text'>registered</span>";
			elseif ($registry['state'] == "0")
				echo "<td bgcolor='#FFCCCC' width='10%'><a class=link_submit href='./forms_admin.php?id={$registry['id']}&method=enable' target='Main'>disabled</a>";
			else
				echo "<td bgcolor='#CCFFCC' width='10%'><a class=link_submit href='./forms_admin.php?id={$registry['id']}&method=disable' target='Main'>enabled</a>";
		?></td>
		<td bgcolor="<?=$color?>" width="10%">
			<span class=text><?php
			
			if ($registry['unpackaged'])
				echo "PHP extracted";
			else
				echo "PHP compressed";
			
			?></span> 
		</td>
		<td bgcolor="<?=$color?>" width="10%">
			<?php
			if ($registry['sql_run'])
				echo "<span class=text>DB installed</span>";
			else
				echo "<a class=link_submit href='./forms_admin.php?id={$registry['id']}&method=install_db' target='Main'>install DB</a>";
			?> 
		</td>
	</tr>
	<?php
	if ($color=="#CCCCCC")
		$color="#999999";
	else
		$color="#CCCCCC";
} //end of foreach
	?>
</table>
<hr>


<?php  //UNREGISTERED SECTION ?>
<span class=bold>Unregistered</span><br>
<table border=0 cellpadding=1 cellspacing=2 width="500">
<?php
$dpath = "$srcdir/../interface/forms/";
$dp = opendir($dpath);
$color="#CCCCCC";
for ($i=0; false != ($fname = readdir($dp)); $i++)
	if ($fname != "." && $fname != ".." && $fname != "CVS" && (is_dir($dpath.$fname) || stristr($fname, ".tar.gz") || stristr($fname, ".tar") || stristr($fname, ".zip") || stristr($fname, ".gz")))
		$inDir[$i] = $fname;

if ($bigdata != false)
foreach ( $bigdata as $registry )
	if ( $key = array_search($registry['directory'], $inDir) )
		unset($inDir[$key]);

foreach ( $inDir as $fname )
{
	if (stristr($fname, ".tar.gz") || stristr($fname, ".tar") || stristr($fname, ".zip") || stristr($fname, ".gz"))
		$phpState = "PHP compressed";
	else
		$phpState =  "PHP extracted";
	?>
	<tr>
		<td bgcolor="<?=$color?>" width="1%">
			<span class=text> </span> 
		</td>
		<td bgcolor="<?=$color?>" width="20%">
			<span class=bold><?=$fname?></span> 
		</td>
		<td bgcolor="<?=$color?>" width="10%"><?php
			if ($phpState == "PHP extracted")
				echo '<a class=link_submit href="./forms_admin.php?name='.urlencode($fname).'&method=register" target=Main>register</a>';
			else
				echo '<span class=text>n/a</span>';
		?></td>
		<td bgcolor="<?=$color?>" width="20%">
			<span class=text><?=$phpState?></span> 
		</td>
		<td bgcolor="<?=$color?>" width="10%">
			<span class=text>n/a</span> 
		</td>
	</tr>
	<?php
	if ($color=="#CCCCCC")
	        $color="#999999";
	else
	        $color="#CCCCCC";
	flush();
}//end of foreach
?>
</table>

</body>
</html>
