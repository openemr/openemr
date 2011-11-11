<?php
global $err_count;
$err_count = 0;

	function found($obj, $cond)
	{
		$res = var_export($obj, true);
		return (strpos($res, $cond));		
	}
	
	function notfound($obj, $cond)
	{
		return !found($obj, $cond);
	}
	
	function ar_assert($bool)
	{
		global $err_count;
		if(!$bool)
			$err_count ++;
		return $bool;
	}
	
		define('WEB', true);
	function ar_echo($txt)
	{
		if(WEB)
			$txt = str_replace("\n", "<br />\n", $txt);
		echo $txt;
	}

	include_once('../adodb.inc.php');
	include_once('../adodb-active-recordx.inc.php');
	

	$db = NewADOConnection('mysql://root@localhost/test');
	$db->debug=0;
	ADOdb_Active_Record::SetDatabaseAdapter($db);

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("Preparing database using SQL queries (creating 'people', 'children')\n");

	$db->Execute("DROP TABLE `people`");
	$db->Execute("DROP TABLE `children`");
	$db->Execute("DROP TABLE `artists`");
	$db->Execute("DROP TABLE `songs`");

	$db->Execute("CREATE TABLE `people` (
	                `id` int(10) unsigned NOT NULL auto_increment,
	                `name_first` varchar(100) NOT NULL default '',
	                `name_last` varchar(100) NOT NULL default '',
	                `favorite_color` varchar(100) NOT NULL default '',
	                PRIMARY KEY  (`id`)
	            ) ENGINE=MyISAM;
	           ");
	$db->Execute("CREATE TABLE `children` (
					`person_id` int(10) unsigned NOT NULL,
	                `name_first` varchar(100) NOT NULL default '',
	                `name_last` varchar(100) NOT NULL default '',
	                `favorite_pet` varchar(100) NOT NULL default '',
	                `id` int(10) unsigned NOT NULL auto_increment,
	                PRIMARY KEY  (`id`)
	            ) ENGINE=MyISAM;
	           ");
	
	$db->Execute("CREATE TABLE `artists` (
	                `name` varchar(100) NOT NULL default '',
	                `artistuniqueid` int(10) unsigned NOT NULL auto_increment,
	                PRIMARY KEY  (`artistuniqueid`)
	            ) ENGINE=MyISAM;
	           ");

	$db->Execute("CREATE TABLE `songs` (
	                `name` varchar(100) NOT NULL default '',
	                `artistid` int(10) NOT NULL,
	                `recordid` int(10) unsigned NOT NULL auto_increment,
	                PRIMARY KEY  (`recordid`)
	            ) ENGINE=MyISAM;
	           ");

	$db->Execute("insert into children (person_id,name_first,name_last,favorite_pet) values (1,'Jill','Lim','tortoise')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (1,'Joan','Lim')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (1,'JAMIE','Lim')");
			   
	$db->Execute("insert into artists (artistuniqueid, name) values(1,'Elvis Costello')");
	$db->Execute("insert into songs (recordid, name, artistid) values(1,'No Hiding Place', 1)");
	$db->Execute("insert into songs (recordid, name, artistid) values(2,'American Gangster Time', 1)");

	// This class _implicitely_ relies on the 'people' table (pluralized form of 'person')
	class Person extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct();
			$this->hasMany('children');
		}
	}
	// This class _implicitely_ relies on the 'children' table
	class Child extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct();
			$this->belongsTo('person');
		}
	}
	// This class _explicitely_ relies on the 'children' table and shares its metadata with Child
	class Kid extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct('children');
			$this->belongsTo('person');
		}
	}
	// This class _explicitely_ relies on the 'children' table but does not share its metadata
	class Rugrat extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct('children', false, false, array('new' => true));
		}
	}
	
	class Artist extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct('artists', array('artistuniqueid'));
			$this->hasMany('songs', 'artistid');
		}
	}
	class Song extends ADOdb_Active_Record
	{
		function __construct()
		{
			parent::__construct('songs', array('recordid'));
			$this->belongsTo('artist', 'artistid');
		}
	}

	ar_echo("Inserting person in 'people' table ('John Lim, he likes lavender')\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	$person->name_first     = 'John';
	$person->name_last      = 'Lim';
	$person->favorite_color = 'lavender';
	$person->save(); // this save will perform an INSERT successfully

	$person = new Person();
	$person->name_first		= 'Lady';
	$person->name_last		= 'Cat';
	$person->favorite_color	= 'green';
	$person->save();
	
	$child = new Child();
	$child->name_first		= 'Fluffy';
	$child->name_last		= 'Cat';
	$child->favorite_pet	= 'Cat Lady';
	$child->person_id		= $person->id;
	$child->save();
	
	$child = new Child();
	$child->name_first		= 'Sun';
	$child->name_last		= 'Cat';
	$child->favorite_pet	= 'Cat Lady';
	$child->person_id		= $person->id;
	$child->save();
	
	$err_count = 0;

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("person->Find('id=1') [Lazy Method]\n");
	ar_echo("person is loaded but its children will be loaded on-demand later on\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	$people = $person->Find('id=1');
	ar_echo((ar_assert(found($people, "'name_first' => 'John'"))) ? "[OK] Found John\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo("\n-- Lazily Loading Children:\n\n");
	foreach($people as $aperson)
	{
		foreach($aperson->children as $achild)
		{
			if($achild->name_first);
		}
	}
	ar_echo((ar_assert(found($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] Found relation: child\n" : "[!!] Missing relation: child\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Joan'"))) ? "[OK] Found Joan\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'JAMIE'"))) ? "[OK] Found JAMIE\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("person->Find('id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("person is loaded, and so are its children\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	$people = $person->Find('id=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($people, "'name_first' => 'John'"))) ? "[OK] Found John\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] Found relation: child\n" : "[!!] Missing relation: child\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Joan'"))) ? "[OK] Found Joan\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'JAMIE'"))) ? "[OK] Found JAMIE\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("person->Find('id=1' ... ADODB_JOIN_AR) [Join Method]\n");
	ar_echo("person and its children are loaded using a single query\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	// When I specifically ask for a join, I have to specify which table id I am looking up
	// otherwise the SQL parser will wonder which table's id that would be.
	$people = $person->Find('people.id=1', false, false, array('loading' => ADODB_JOIN_AR));
	ar_echo((ar_assert(found($people, "'name_first' => 'John'"))) ? "[OK] Found John\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] Found relation: child\n" : "[!!] Missing relation: child\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Joan'"))) ? "[OK] Found Joan\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'JAMIE'"))) ? "[OK] Found JAMIE\n" : "[!!] Find failed\n");
		
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("person->Load('people.id=1') [Join Method]\n");
	ar_echo("Load() always uses the join method since it returns only one row\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	// Under the hood, Load(), since it returns only one row, always perform a join
	// Therefore we need to clarify which id we are talking about.
	$person->Load('people.id=1');
	ar_echo((ar_assert(found($person, "'name_first' => 'John'"))) ? "[OK] Found John\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($person, "'favorite_pet' => 'tortoise'"))) ? "[OK] Found relation: child\n" : "[!!] Missing relation: child\n");
	ar_echo((ar_assert(found($person, "'name_first' => 'Joan'"))) ? "[OK] Found Joan\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($person, "'name_first' => 'JAMIE'"))) ? "[OK] Found JAMIE\n" : "[!!] Find failed\n");
	
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("child->Load('children.id=1') [Join Method]\n");
	ar_echo("We are now loading from the 'children' table, not from 'people'\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$child = new Child();
	$child->Load('children.id=1');
	ar_echo((ar_assert(found($child, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($child, "'favorite_color' => 'lavender'"))) ? "[OK] Found relation: person\n" : "[!!] Missing relation: person\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("child->Find('children.id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$child = new Child();
	$children = $child->Find('id=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($children, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($children, "'favorite_color' => 'lavender'"))) ? "[OK] Found relation: person\n" : "[!!] Missing relation: person\n");
	ar_echo((ar_assert(notfound($children, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($children, "'name_first' => 'JAMIE'"))) ? "[OK] No JAMIE relation\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("kid->Find('children.id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("Where we see that kid shares relationships with child because they are stored\n");
	ar_echo("in the common table's metadata structure.\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$kid = new Kid('children');
	$kids = $kid->Find('children.id=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($kids, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($kids, "'favorite_color' => 'lavender'"))) ? "[OK] Found relation: person\n" : "[!!] Missing relation: person\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'JAMIE'"))) ? "[OK] No JAMIE relation\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("kid->Find('children.id=1' ... ADODB_LAZY_AR) [Lazy Method]\n");
	ar_echo("Of course, lazy loading also retrieve medata information...\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$kid = new Kid('children');
	$kids = $kid->Find('children.id=1', false, false, array('loading' => ADODB_LAZY_AR));
	ar_echo((ar_assert(found($kids, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($kids, "'favorite_color' => 'lavender'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo("\n-- Lazily Loading People:\n\n");
	foreach($kids as $akid)
	{
		if($akid->person);
	}
	ar_echo((ar_assert(found($kids, "'favorite_color' => 'lavender'"))) ? "[OK] Found relation: person\n" : "[!!] Missing relation: person\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'JAMIE'"))) ? "[OK] No JAMIE relation\n" : "[!!] Found relation when I shouldn't\n");
	
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("rugrat->Find('children.id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("In rugrat's constructor it is specified that\nit must forget any existing relation\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$rugrat = new Rugrat('children');
	$rugrats = $rugrat->Find('children.id=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($rugrats, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($rugrats, "'favorite_color' => 'lavender'"))) ? "[OK] No relation found\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($rugrats, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($rugrats, "'name_first' => 'JAMIE'"))) ? "[OK] No JAMIE relation\n" : "[!!] Found relation when I shouldn't\n");
	
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("kid->Find('children.id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("Note how only rugrat forgot its relations - kid is fine.\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$kid = new Kid('children');
	$kids = $kid->Find('children.id=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($kids, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($kids, "'favorite_color' => 'lavender'"))) ? "[OK] I did not forget relation: person\n" : "[!!] I should not have forgotten relation: person\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($kids, "'name_first' => 'JAMIE'"))) ? "[OK] No JAMIE relation\n" : "[!!] Found relation when I shouldn't\n");
	
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("rugrat->Find('children.id=1' ... ADODB_WORK_AR) [Worker Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$rugrat = new Rugrat('children');
	$rugrats = $rugrat->Find('children.id=1', false, false, array('loading' => ADODB_WORK_AR));
	$arugrat = $rugrats[0];
	ar_echo((ar_assert(found($arugrat, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($arugrat, "'favorite_color' => 'lavender'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	
	ar_echo("\n-- Loading relations:\n\n");
	$arugrat->belongsTo('person');
	$arugrat->LoadRelations('person', 'order by id', 0, 2);
	ar_echo((ar_assert(found($arugrat, "'favorite_color' => 'lavender'"))) ? "[OK] Found relation: person\n" : "[!!] Missing relation: person\n");
	ar_echo((ar_assert(found($arugrat, "'name_first' => 'Jill'"))) ? "[OK] Found Jill\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($arugrat, "'name_first' => 'Joan'"))) ? "[OK] No Joan relation\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($arugrat, "'name_first' => 'JAMIE'"))) ? "[OK] No Joan relation\n" : "[!!] Found relation when I shouldn't\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("person->Find('1=1') [Lazy Method]\n");
	ar_echo("And now for our finale...\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$person = new Person();
	$people = $person->Find('1=1', false, false, array('loading' => ADODB_LAZY_AR));
	ar_echo((ar_assert(found($people, "'name_first' => 'John'"))) ? "[OK] Found John\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	ar_echo((ar_assert(notfound($people, "'name_first' => 'Fluffy'"))) ? "[OK] No Fluffy yet\n" : "[!!] Found Fluffy relation when I shouldn't\n");
	ar_echo("\n-- Lazily Loading Everybody:\n\n");
	foreach($people as $aperson)
	{
		foreach($aperson->children as $achild)
		{
			if($achild->name_first);
		}
	}
	ar_echo((ar_assert(found($people, "'favorite_pet' => 'tortoise'"))) ? "[OK] Found relation: child\n" : "[!!] Missing relation: child\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Joan'"))) ? "[OK] Found Joan\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'JAMIE'"))) ? "[OK] Found JAMIE\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Lady'"))) ? "[OK] Found Cat Lady\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Fluffy'"))) ? "[OK] Found Fluffy\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($people, "'name_first' => 'Sun'"))) ? "[OK] Found Sun\n" : "[!!] Find failed\n");
	
	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("artist->Load('artistuniqueid=1') [Join Method]\n");
	ar_echo("Yes, we are dabbling in the musical field now..\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$artist = new Artist();
	$artist->Load('artistuniqueid=1');
	ar_echo((ar_assert(found($artist, "'name' => 'Elvis Costello'"))) ? "[OK] Found Elvis Costello\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($artist, "'name' => 'No Hiding Place'"))) ? "[OK] Found relation: song\n" : "[!!] Missing relation: song\n");


	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("song->Load('recordid=1') [Join Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$song = new Song();
	$song->Load('recordid=1');
	ar_echo((ar_assert(found($song, "'name' => 'No Hiding Place'"))) ? "[OK] Found song\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("artist->Find('artistuniqueid=1' ... ADODB_JOIN_AR) [Join Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$artist = new Artist();
	$artists = $artist->Find('artistuniqueid=1', false, false, array('loading' => ADODB_JOIN_AR));
	ar_echo((ar_assert(found($artists, "'name' => 'Elvis Costello'"))) ? "[OK] Found Elvis Costello\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($artists, "'name' => 'No Hiding Place'"))) ? "[OK] Found relation: song\n" : "[!!] Missing relation: song\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("song->Find('recordid=1' ... ADODB_JOIN_AR) [Join Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$song = new Song();
	$songs = $song->Find('recordid=1', false, false, array('loading' => ADODB_JOIN_AR));
	ar_echo((ar_assert(found($songs, "'name' => 'No Hiding Place'"))) ? "[OK] Found song\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("artist->Find('artistuniqueid=1' ... ADODB_WORK_AR) [Work Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$artist = new Artist();
	$artists = $artist->Find('artistuniqueid=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($artists, "'name' => 'Elvis Costello'"))) ? "[OK] Found Elvis Costello\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(found($artists, "'name' => 'No Hiding Place'"))) ? "[OK] Found relation: song\n" : "[!!] Missing relation: song\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("song->Find('recordid=1' ... ADODB_JOIN_AR) [Join Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$song = new Song();
	$songs = $song->Find('recordid=1', false, false, array('loading' => ADODB_WORK_AR));
	ar_echo((ar_assert(found($songs, "'name' => 'No Hiding Place'"))) ? "[OK] Found song\n" : "[!!] Find failed\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("artist->Find('artistuniqueid=1' ... ADODB_LAZY_AR) [Lazy Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$artist = new Artist();
	$artists = $artist->Find('artistuniqueid=1', false, false, array('loading' => ADODB_LAZY_AR));
	ar_echo((ar_assert(found($artists, "'name' => 'Elvis Costello'"))) ? "[OK] Found Elvis Costello\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($artists, "'name' => 'No Hiding Place'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	foreach($artists as $anartist)
	{
		foreach($anartist->songs as $asong)
		{
			if($asong->name);
		}
	}
	ar_echo((ar_assert(found($artists, "'name' => 'No Hiding Place'"))) ? "[OK] Found relation: song\n" : "[!!] Missing relation: song\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("song->Find('recordid=1' ... ADODB_LAZY_AR) [Lazy Method]\n");
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
	$song = new Song();
	$songs = $song->Find('recordid=1', false, false, array('loading' => ADODB_LAZY_AR));
	ar_echo((ar_assert(found($songs, "'name' => 'No Hiding Place'"))) ? "[OK] Found song\n" : "[!!] Find failed\n");
	ar_echo((ar_assert(notfound($songs, "'name' => 'Elvis Costello'"))) ? "[OK] No relation yet\n" : "[!!] Found relation when I shouldn't\n");
	foreach($songs as $asong)
	{
		if($asong->artist);
	}
	ar_echo((ar_assert(found($songs, "'name' => 'Elvis Costello'"))) ? "[OK] Found relation: artist\n" : "[!!] Missing relation: artist\n");

	ar_echo("\n\n-------------------------------------------------------------------------------------------------------------------\n");
	ar_echo("Test suite complete. " . (($err_count > 0) ? "$err_count errors found.\n" : "Success.\n"));
	ar_echo("-------------------------------------------------------------------------------------------------------------------\n");
?>
