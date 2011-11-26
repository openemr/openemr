<?php

	include_once('../adodb.inc.php');
	include_once('../adodb-active-record.inc.php');
	

	$db = NewADOConnection('mysql://root@localhost/northwind');
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
			   
	
	$db->Execute("insert into children (person_id,name_first,name_last) values (1,'Jill','Lim')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (1,'Joan','Lim')");
	$db->Execute("insert into children (person_id,name_first,name_last) values (1,'JAMIE','Lim')");
	
	ADODB_Active_Record::TableHasMany('persons', 'children','person_id');
	class person extends ADOdb_Active_Record{}
	
	$person = new person();
#	$person->HasMany('children','person_id');  ## this is affects all other instances of Person
	
	$person->name_first     = 'John';
	$person->name_last      = 'Lim';
	$person->favorite_color = 'lavender';
	$person->save(); // this save will perform an INSERT successfully
	
	$person2 = new person();
	$person2->Load('id=1');
	
	$c = $person2->children;
	if (is_array($c) && sizeof($c) == 3 && $c[0]->name_first=='Jill' && $c[1]->name_first=='Joan'
		&& $c[2]->name_first == 'JAMIE') echo "OK Loaded HasMany</br>";
	else {
		var_dump($c);
		echo "error loading hasMany should have 3 array elements Jill Joan Jamie<br>";
	}
	
	class child extends ADOdb_Active_Record{};
	ADODB_Active_Record::TableBelongsTo('children','person','person_id','id');
	$ch = new Child('children',array('id'));
	
	$ch->Load('id=1');
	if ($ch->name_first !== 'Jill') echo "error in Loading Child<br>";
	
	$p = $ch->person;
	if (!$p || $p->name_first != 'John') echo "Error loading belongsTo<br>";
	else echo "OK loading BelongTo<br>";

	if ($p) {
		#$p->HasMany('children','person_id');  ## this is affects all other instances of Person
		$p->LoadRelations('children', 'order by id',1,2);
		if (sizeof($p->children) == 2 && $p->children[1]->name_first == 'JAMIE') echo "OK LoadRelations<br>";
		else {
		 	var_dump($p->children);
			echo "error LoadRelations<br>";
		}
		
		unset($p->children);
		$p->LoadRelations('children', " name_first like 'J%' order by id",1,2);
	}
	if ($p)
	foreach($p->children as $c) {
		echo " Saving $c->name_first <br>";
		$c->name_first .= ' K.';
		$c->Save();
	}

?>