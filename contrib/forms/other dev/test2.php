<?
			echo $lenght;

	switch ($lenght):
		case ( 1 || 2):
			echo $lenght;
			break;
			
		case (3 || 4):
			echo $lenght;
			break;
	default:
		echo $lenght;
		break;
endswitch;

$a='h';
$a=substr ($a,  -2);
echo '<br>'.$a;

			$p_age['days']=substr ( $result['age'], -2 );
			$p_age['months']=substr ($result['age'], -4, 2 );
			$p_age['years']=substr ($result['age'], 0,2 );

echo '<br>';

	$result['age']='0908';
	$lenght=strlen($result["age"]);
	echo $lenght;

	$p_age['days']=substr ( $result['age'], -2);
	switch ($lenght) {
		case 3: case 4:
			$p_age['months']=substr ($result['age'], 0, -2 );
			echo 'case 34';
			break;
		case  5:	case  6:	case  7:
			$p_age['months']=substr ($result['age'], -4, -2 );
			$p_age['years']=substr ($result['age'],  0,-4 );
			echo 'case 67';
			break;
	}


print_r ($p_age);


function here ($he='') {
	print_r ( $he) ;
}

here ();

here ('heeeee');

$he=array (12,22,2233,2332);
here ($he);

?>