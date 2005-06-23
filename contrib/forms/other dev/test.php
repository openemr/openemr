<?


$date1=time();
$date2=mktime (0,0,0,10,6,2003);


function date_diff ( $date1, $date2 ) {
  $some=date("z \\d\\a\\y\\s H\\h i\\m s\\s",$date1-$date2);
	print $some;
}

date_diff ( $date1, $date2 );


// years months weeks

	function ymw () {

			list ($pyear,$pmonth,$pday)=split ('[/.-]','2000-10-6');
			$now=getdate();
			$nmonth=$now['month']; $nday=$now['mday'];$nyear=$now['year'];
			$months= ($nyear-$pyear *12) + ($nmonth-$pmonth);

			$now=time();
			$born=mktime(0,0,0,$pmonth,$pday,$pyear);
			echo '<br>born'.$born.'<br>';
			echo $pmont.'-'.$pday.'-'.$pyear.'<br>';
			$seconds=$now - mktime (0,0,0,$pmonth,$pday,$pyear);
			$age=date ('Y-m-d',mktime (0,0,$seconds ));
			print $age;


			return ;

	}

ymw();



function patient_age ($pid) {
	$query="SELECT ( CURDATE( ) - DOB ) AS age, ( TO_DAYS ( CURDATE( )) - TO_DAYS ( DOB ) ) AS alldays  FROM patient_data WHERE pid='$pid' LIMIT 1";
	$result=sqlQuery ($query);
	$lenght=strlen($result["age"]);
	$p_age['alldays']=$result ['alldays'];
	$p_age['days']=substr ( $result['age'], -2);
	switch ($lenght):
		case (3 || 4):
			$p_age['months']=substr ($result['age'], 0, -2 );
			break;
		case (5 || 6 || 7):
			$p_age['months']=substr ($result['age'], -4, 2 );
			$p_age['years']=substr ($result['age'], 0, -4 );
	endswitch;
	return $p_age;
}




patient_age (1);


?>