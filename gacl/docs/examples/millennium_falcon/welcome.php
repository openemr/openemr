	<p>
		<b>Pre-Requirements:</b>
		<ul>
			<li><b>You should setup a phpGACL database and edit your gacl.class.php to point to the database.</b></li>
	<?php
		require_once('millenniumFalcon.inc');
		echo "<li><b>CURRENT DATABASE: ".$gacl_options['db_name']."</b></li>";
		echo "<li><font color='red' size='+1'><b>WARNING: THE ".$gacl_options['db_name']." DATABASEWILL BE RESET IF YOU RUN ANY EXAMPLE!</b></font></li>";
	?>
	</ul>
</p>
<p>
	These 3 examples work hand in hand with the phpGACL documentation Millenium Falcon example, which can be found here: <a href='../docs/manual.pdf' target='_new'>phpGACL documentation</a><br />
	Run the examples and have a look at the code, but don't forget to check out phpGACLs excellent <a href='../../../admin/index.php' target='_new'>admin suite</a>.
</p>
<p>
	<ul>
		<li><a href='index.php?do=example1'>Defining Access Control</a> Manual pages: 7-8</li>
		<li><a href='index.php?do=example2'>Fine-grain Access Control</a>  Manual pages: 8-9</li>
		<li><a href='index.php?do=example3'>Multi-level Groups</a>  Manual pages: 9</li>
	</ul>
	</p>
	<p>
		By now you should have a good idea about the phpGACL API and how to:
		<ul>
			<li> Creating ACO sections and ACOs.</li>
			<li> Creating ARO sections and AROs.</li>
			<li> Creating ARO Groups and assigning AROs.</li>
			<li> Editing Groups.</li>
			<li> Deleting Groups / Objects.</li>
		</ul>
		The rest of the API functions are in the manual!
	</p>
	<br /><br /><br /><br />
