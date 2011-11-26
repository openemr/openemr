<?php

	include_once('../adodb.inc.php');
	include_once('../adodb-active-record.inc.php');
	
	// uncomment the following if you want to test exceptions
	if (@$_GET['except']) {
		if (PHP_VERSION >= 5) {
			include('../adodb-exceptions.inc.php');
			echo "<h3>Exceptions included</h3>";
		}
	}

	$db = NewADOConnection('mysql://root@localhost/northwind?persist');
	$db->debug=1;
	ADOdb_Active_Record::SetDatabaseAdapter($db);

	
	$db->Execute("CREATE TEMPORARY TABLE `persons` (
	                `id` int(10) unsigned NOT NULL auto_increment,
	                `name_first` varchar(100) NOT NULL default '',
	                `name_last` varchar(100) NOT NULL default '',
	                `favorite_color` varchar(100) NOT NULL default '',
	                PRIMARY KEY  (`id`)
	            ) ENGINE=MyISAM;
	           ");
			   
	$db->Execute("CREATE TEMPORARY TABLE `children` (
	                `id` int(10) unsigned NOT NULL auto_increment,
					`person_id` int(10) unsigned NOT NULL,
	                `name_first` varchar(100) NOT NULL default '',
	                `name_last` varchar(100) NOT NULL default '',
	                `favorite_pet` varchar(100) NOT NULL default '',
	                PRIMARY KEY  (`id`)
	            ) ENGINE=MyISAM;
	           ");
			   
	class Person extends ADOdb_Active_Record{ function ret($v) {return $v;} }
	$person = new Person();
	ADOdb_Active_Record::$_quoteNames = '111';
	
	echo "<p>Output of getAttributeNames: ";
	var_dump($person->getAttributeNames());
	
	/**
	 * Outputs the following:
	 * array(4) {
	 *    [0]=>
	 *    string(2) "id"
	 *    [1]=>
	 *    string(9) "name_first"
	 *    [2]=>
	 *    string(8) "name_last"
	 *    [3]=>
	 *    string(13) "favorite_color"
	 *  }
	 */
	
	$person = new Person();
	$person->name_first = 'Andi';
	$person->name_last  = 'Gutmans';
	$person->save(); // this save() will fail on INSERT as favorite_color is a must fill...
	
	
	$person = new Person();
	$person->name_first     = 'Andi';
	$person->name_last      = 'Gutmans';
	$person->favorite_color = 'blue';
	$person->save(); // this save will perform an INSERT successfully
	
	echo "<p>The Insert ID generated:"; print_r($person->id);
	
	$person->favorite_color = 'red';
	$person->save(); // this save() will perform an UPDATE
	
	$person = new Person();
	$person->name_first     = 'John';
	$person->name_last      = 'Lim';
	$person->favorite_color = 'lavender';
	$person->save(); // this save will perform an INSERT successfully
	
	// load record where id=2 into a new ADOdb_Active_Record
	$person2 = new Person();
	$person2->Load('id=2');
	
	$activeArr = $db->GetActiveRecordsClass($class = "Person",$table = "Persons","id=".$db->Param(0),array(2));
	$person2 = $activeArr[0];
	echo "<p>Name (should be John): ",$person->name_first, " <br> Class (should be Person): ",get_class($person2),"<br>";	
	
	$db->Execute("insert into children (person_id,name_first,name_last) values (2,'Jill','Lim')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (2,'Joan','Lim')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (2,'JAMIE','Lim')");
	
	$newperson2 = new Person();
	$person2->HasMany('children','person_id');
	$person2->Load('id=2');
	$person2->name_last='green';
	$c = $person2->children;
	$person2->save();

	if (is_array($c) && sizeof($c) == 3 && $c[0]->name_first=='Jill' && $c[1]->name_first=='Joan'
		&& $c[2]->name_first == 'JAMIE') echo "OK Loaded HasMany</br>";
	else {
		var_dump($c);
		echo "error loading hasMany should have 3 array elements Jill Joan Jamie<br>";
	}
	
	class Child extends ADOdb_Active_Record{};
	$ch = new Child('children',array('id'));
	$ch->BelongsTo('person','person_id','id');
	$ch->Load('id=1');
	if ($ch->name_first !== 'Jill') echo "error in Loading Child<br>";
	
	$p = $ch->person;
	if ($p->name_first != 'John') echo "Error loading belongsTo<br>";
	else echo "OK loading BelongTo<br>";

	$p->hasMany('children','person_id');
	$p->LoadRelations('children', "	Name_first like 'J%' order by id",1,2);
	if (sizeof($p->children) == 2 && $p->children[1]->name_first == 'JAMIE') echo "OK LoadRelations<br>";
	else echo "error LoadRelations<br>";
	
		$db->Execute("CREATE TEMPORARY TABLE `persons2` (
	                `id` int(10) unsigned NOT NULL auto_increment,
	                `name_first` varchar(100) NOT NULL default '',
	                `name_last` varchar(100) NOT NULL default '',
	                `favorite_color` varchar(100) default '',
	                PRIMARY KEY  (`id`)
	            ) ENGINE=MyISAM;
	           ");
	
	$p = new adodb_active_record('persons2');
	$p->name_first = 'James';
	
	$p->name_last = 'James';
	
	$p->HasMany('children','person_id');
	$p->children;
	var_dump($p);
	$p->Save();
?>