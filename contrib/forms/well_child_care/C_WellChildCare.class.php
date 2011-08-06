<?php

// this should be, but it is not a real class,
// uses function getPatientDAta sn sqlQuery from the library directory
// 

$link=mysql_connect ('localhost','root','');
mysql_select_db (openemr);


class C_WellChildCare {

	var $patient_demo;
	
	function C_WellChildCare ($pid) {
		$this->patient_demo=$this->patient_set ($pid);
		// print_r ($this->patient_demo);

	}
		
	function patient_set ($pid) {

		$query="SELECT  TO_DAYS( CURDATE( ) ) - TO_DAYS( DOB ) AS alldays, date_format(from_days( TO_DAYS( CURDATE( ) ) - TO_DAYS( DOB ) ), \"%y-%c-%e\") AS age, sex FROM patient_data WHERE pid=$pid LIMIT 1";

		$result=sqlQuery ($query);
		$p_age['alldays']=$result ['alldays'];
		$p_age['gender']=$result ['sex'];
		
		if ($result['age']!='00-0-0')  {
			list ($pyear,$pmonth,$pday)=explode ('-', $result["age"] );
			if ($pyear==1) $age=$pyear.' year '; 
			if ($pyear > 1) $age=$pyear.' years '; 
			if ($pmonth == 1) $age.=$pmonth.' month '; 
			if ($pmonth > 1) $age.=$pmonth.' months '; 
			if ($pday == 1) $age.=$pday.' day '; 
			if ($pday > 1) $age.=$pday.' days '; 
			$p_age['age']=$age ;
		} else {
			list ($m,$d) = explode ( '.', $result['alldays']/30.4);
			list ($d)=explode ('.', "0.$d"*30.4  );
			if ($m == 1) $age.=$m.' month '; 
			if ($m > 1) $age.=$m.' months '; 
			if ($d == 1) $age.=$d.' day '; 
			if ($d > 1) $age.=$d.' days '; 
			$p_age['age']=$age ;
		}
		
		return $p_age;
	}


	function put_form ( $obj='' ) {
	//common part
	?>
	<TABLE span class="text">
	<TR><TD colspan=6><b><U>Well Child Care:</U></b> <?php echo $this->patient_demo['age'] ?></TD></TR>
	<TR>
		<TD>Height:(inches)</TD>
		<TD><INPUT TYPE="text" NAME="height" size="6" maxlength="6" value="<?php print $obj["height"]; ?>"></TD>
		<TD>Weight:(#)</TD>
		<TD><INPUT TYPE="text" NAME="weight" size="6" maxlength="6" value="<?php print $obj["weight"]; ?>"></TD>
		<TD>Head Circ:(cm)</TD>
		<TD><INPUT TYPE="text" NAME="head_c" size="4" maxlength="4" value="<?php print $obj["head_c"]; ?>"></TD>
	</TR>
	<TR>
		<TD>Temp:(F)</TD>
		<TD><INPUT TYPE="text" NAME="temp" size="6" maxlength="6" value="<?php print $obj["temp"]; ?>"></TD>
		<TD>Pulse:</TD>
		<TD><INPUT TYPE="text" NAME="pulse" size="2" maxlength="2" value="<?php print $obj["pulse"]; ?>"></TD>
		<TD>Resp:</TD>
		<TD><INPUT TYPE="text" NAME="respiration" size="2" maxlength="2" value="<?php print $obj["respiration"]; ?>"></TD>
	</TR>
	<TR><TD colspan=6><b>Sensory screen & developmental assesment:</b></TD></TR>
	<TR>
		<TD>Normal Vision:</TD>
		<TD><SELECT NAME="normal_vision"><option <?if ( $obj["normal_vision"]=='yes' ) print 'selected';  ?>>yes<option <?if ( $obj["normal_vision"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Normal Hearing:</TD>
		<TD><SELECT NAME="normal_hearing"><option <?if ( $obj["normal_hearing"]=='yes' ) print 'selected';  ?>>yes<option <?if ( $obj["normal_hearing"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<TR>
		<TD>Normal for age:</TD>
		<TD><SELECT NAME="normal_development"><option <?if ( $obj["normal_development"]=='yes' ) print 'selected';  ?>>yes<option <?if ( $obj["normal_development"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Referred</TD>
		<TD><SELECT NAME="further_testing"><option <?if ( $obj["further_testing"]=='n/a' ) print 'selected';  ?>>n/a<option <?if ( $obj["further_testing"]=='yes' ) print 'selected';  ?>>yes<option <?if ( $obj["further_testing"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<TR><TD colspan=6><b>Assessment and Recommendations:</b></TD></TR>

	<!-- // show this 2 week info  -->
	<?php

	if ($this->patient_demo['alldays'] < 30) {
	?>
	<TR>
		<TD>Birth weight:</TD>
		<TD><?php 
			if ( $obj["birth_wt"]!='' ) print $obj["birth_wt"]; 
				else print '<INPUT TYPE=text NAME=birth_wt size=5 maxlength=5>'; 
			?>
		<TD>Birth height:</TD>
		<TD><?php 
			if ( $obj["birth_ht"]!='' ) print $obj["birth_ht"]; 
				else print '<INPUT TYPE=text NAME=birth_ht size=5 maxlength=5>'; 
			?>
		</TD>
		<TD>Gravida/Para:</TD>
		<TD><?php 
			if ( $obj["gravida"]!='' ) print $obj["gravida"]; 
				else print '<INPUT TYPE=text NAME=gravida size=5 maxlength=5>'; 
			if ( $obj["para"]!='' ) print $obj["para"]; 
				else print '<INPUT TYPE=text NAME=para size=5 maxlength=5>'; 
			?>
		</TD>
	</TR>
	<?php

	}
	?>
	<!-- two weeks spec -->
	<?php

	if ($this->patient_demo['alldays'] < 30 ) {
	?>
		<TR>
			<TD>Car Seat:</TD>
			<TD><INPUT TYPE="checkbox" NAME="car_seat" value="yes" <?if ( $obj["car_seat"]!='' ) print 'checked';  ?>></TD>
			<TD>Feeding:</TD>
			<TD><INPUT TYPE="checkbox" NAME="feeding" value="yes" <?if ( $obj["feeding"]!='' ) print 'checked';  ?>></TD>
			<TD>Immunization:</TD>
			<TD><INPUT TYPE="checkbox" NAME="immunization" value="yes" <?if ( $obj["immunization"]!='' ) print 'checked';  ?>></TD>
		</TR>
		<TR>
			<TD>Lower Water temp:</TD>
			<TD><INPUT TYPE="checkbox" NAME="lower_water_temp" value="yes" <?if ( $obj["lower_water_temp"]!='' ) print 'checked';  ?>></TD>
			<TD>Sibling jealousy:</TD>
			<TD><INPUT TYPE="checkbox" NAME="sibling_jealousy" value="yes" <?if ( $obj["sibling_jealousy"]!='' ) print 'checked';  ?>></TD>
			<TD>Stimulation:</TD>
			<TD><INPUT TYPE="checkbox" NAME="stimulation" value="yes" <?if ( $obj["stimulation"]!='' ) print 'checked';  ?>></TD>
		</TR>
		<TR>
			<TD>Sleep:</TD>
			<TD><INPUT TYPE="checkbox" NAME="sleep" value="yes" <?if ( $obj["sleep"]!='' ) print 'checked';  ?>></TD>
			<TD>Babysitters:</TD>
			<TD><INPUT TYPE="checkbox" NAME="babysitters" value="yes" <?if ( $obj["babysitters"]!='' ) print 'checked';  ?>></TD>
			<TD>Colic:</TD>
			<TD><INPUT TYPE="checkbox" NAME="colic" value="yes" <?if ( $obj["colic"]!='' ) print 'checked';  ?>></TD>
		</TR>
	<?		
	}
	?>
	<!-- // from 2 weeks to two years -->
	<?php

	if ($this->patient_demo['alldays'] < 750 ) {
	?>
		<TR>
			<TD>Breast Feeding:</TD>
			<TD><SELECT NAME="breast_feeding"><option <?if ( $obj["breast_feeding"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["breast_feeding"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
			<TD>Formula:</TD>
			<TD><SELECT NAME="formula"><option <?if ( $obj["formula"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["formula"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
			<TD></TD>
			<TD></TD>
		</TR>
		<TR>
			<TD>WIC referred:</TD>
			<TD><SELECT NAME="WIC_referred"><option <?if ( $obj["WIC_referred"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["WIC_referred"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
			<TD>Vitamins:</TD>
			<TD><SELECT NAME="vitamins"><option <?if ( $obj["vitamins"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["vitamins"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
			<TD>Iron:</TD>
			<TD><SELECT NAME="iron"><option <?if ( $obj["iron"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["iron"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
		</TR>
	<?		
	}
	?>
	<!-- // 2 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 31 AND $this->patient_demo['alldays'] <= 90 ) {
	?>
	<TR>
		<TD>Car seat:</TD>
		<TD><INPUT TYPE="checkbox" NAME="car_seat" value="yes" <?if ( $obj["car_seat"]!='' ) print 'checked';  ?>></TD>
		<TD>Fever Education:</TD>
		<TD><INPUT TYPE="checkbox" NAME="fever_education" value="yes" <?if ( $obj["fever_education"]!='' ) print 'checked';  ?>></TD>
		<TD>Sick care:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sick_care" value="yes" <?if ( $obj["sick_care"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Safety:</TD>
		<TD><INPUT TYPE="checkbox" NAME="safety" value="yes" <?if ( $obj["safety"]!='' ) print 'checked';  ?>></TD>
		<TD>Pacifiers:</TD>
		<TD><INPUT TYPE="checkbox" NAME="pacifiers" value="yes" <?if ( $obj["pacifiers"]!='' ) print 'checked';  ?>></TD>
		<TD>Talk to child:</TD>
		<TD><INPUT TYPE="checkbox" NAME="talk_to_child" value="yes" <?if ( $obj["talk_to_child"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>
	<!-- // 4 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 91 AND $this->patient_demo['alldays'] <= 150 ) {
	?>
		<TR>
			<TD>Solid Foods:</TD>
			<TD><INPUT TYPE="checkbox" NAME="solid_foods" value="yes" <?if ( $obj["solid_foods"]!='' ) print 'checked';  ?>></TD>
			<TD>Choking:</TD>
			<TD><INPUT TYPE="checkbox" NAME="choking" value="yes" <?if ( $obj["choking"]!='' ) print 'checked';  ?>></TD>
			<TD>Teething:</TD>
			<TD><INPUT TYPE="checkbox" NAME="teething" value="yes" <?if ( $obj["teething"]!='' ) print 'checked';  ?>></TD>
		</TR>
		<TR>
			<TD>Falls:</TD>
			<TD><INPUT TYPE="checkbox" NAME="falls" value="yes" <?if ( $obj["falls"]!='' ) print 'checked';  ?>></TD>
			<TD>Baby-proof:</TD>
			<TD><INPUT TYPE="checkbox" NAME="baby_proof" value="yes" <?if ( $obj["baby_proof"]!='' ) print 'checked';  ?>></TD>
			<TD>Colds:</TD>
			<TD><INPUT TYPE="checkbox" NAME="colds" value="yes" <?if ( $obj["colds"]!='' ) print 'checked';  ?>></TD>
		</TR>
	<?		
	}
	?>

	<!-- // 6 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 151 AND $this->patient_demo['alldays'] <= 230 ) {
	?>
		<TR>
			<TD>Baby-Proof:</TD>
			<TD><INPUT TYPE="checkbox" NAME="baby_proof" value="yes" <?if ( $obj["baby_proof"]!='' ) print 'checked';  ?>></TD>
			<TD>Stranger Anxiety:</TD>
			<TD><INPUT TYPE="checkbox" NAME="stranger_anxiety" value="yes" <?if ( $obj["stranger_anxiety"]!='' ) print 'checked';  ?>></TD>
			<TD>No Peanuts:</TD>
			<TD><INPUT TYPE="checkbox" NAME="no_peanuts" value="yes" <?if ( $obj["no_peanuts"]!='' ) print 'checked';  ?>></TD>
		</TR>
		<TR>
			<TD>No bottle in bed:</TD>
			<TD><INPUT TYPE="checkbox" NAME="no_bottle_in_bed" value="yes" <?if ( $obj["no_bottle_in_bed"]!='' ) print 'checked';  ?>></TD>
			<TD>Poisons - Ipecac:</TD>
			<TD><INPUT TYPE="checkbox" NAME="poisons_ipecac" value="yes" <?if ( $obj["poisons_ipecac"]!='' ) print 'checked';  ?>></TD>
			<TD>Pool & Tub safety</TD>
			<TD><INPUT TYPE="checkbox" NAME="pool_tub_safety" value="yes" <?if ( $obj["pool_tub_safety"]!='' ) print 'checked';  ?>></TD>
		</TR>
	<?		
	}
	?>
	<!-- common part from 9 to 24 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 230 AND $this->patient_demo['alldays'] <= 730 ) {
	?>
	<TR>
		<TD>Whole Milk:</TD>
		<TD><INPUT TYPE="checkbox" NAME="whole_milk" value="yes" <?if ( $obj["whole_milk"]!='' ) print 'checked';  ?>></TD>
		<TD>Solids:</TD>
		<TD><INPUT TYPE="checkbox" NAME="solids" value="yes" <?if ( $obj["solids"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?php

	}
	?>
	<!-- common part from 12 to 24 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 350 AND $this->patient_demo['alldays'] <= 730 ) {
	?>
	<TR>
		<TD>Cup:</TD>
		<TD><INPUT TYPE="checkbox" NAME="cup" value="yes" <?if ( $obj["cup"]!='' ) print 'checked';  ?>></TD>
		<TD>Bottle:</TD>
		<TD><INPUT TYPE="checkbox" NAME="bottle" value="yes" <?if ( $obj["bottle"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?php

	}
	?>

	<!-- // 9 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 231 AND $this->patient_demo['alldays'] <= 320 ) {
	?>
	<TR>
		<TD>Talk to child:</TD>
		<TD><INPUT TYPE="checkbox" NAME="talk_to_child" value="yes" <?if ( $obj["talk_to_child"]!='' ) print 'checked';  ?>></TD>
		<TD>Baby-proof:</TD>
		<TD><INPUT TYPE="checkbox" NAME="baby_proof" value="yes" <?if ( $obj["baby_proof"]!='' ) print 'checked';  ?>></TD>
		<TD>Poison-Ipecac</TD>
		<TD><INPUT TYPE="checkbox" NAME="poison_ipecac" value="yes" <?if ( $obj["poison_ipecac"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Pool & tub safety:</TD>
		<TD><INPUT TYPE="checkbox" NAME="pool_tub_safety" value="yes" <?if ( $obj["pool_tub_safety"]!='' ) print 'checked';  ?>></TD>
		<TD>Self Feeding:</TD>
		<TD><INPUT TYPE="checkbox" NAME="self_feeding" value="yes" <?if ( $obj["self_feeding"]!='' ) print 'checked';  ?>></TD>
		<TD>Shoes:</TD>
		<TD><INPUT TYPE="checkbox" NAME="shoes" value="yes" <?if ( $obj["shoes"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>
	<!-- // 12 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 321 AND $this->patient_demo['alldays'] <= 410 ) {
	?>
	<TR>
		<TD>Discipline:</TD>
		<TD><INPUT TYPE="checkbox" NAME="discipline" value="yes" <?if ( $obj["discipline"]!='' ) print 'checked';  ?>></TD>
		<TD>Name Objects:</TD>
		<TD><INPUT TYPE="checkbox" NAME="name_objects" value="yes" <?if ( $obj["name_objects"]!='' ) print 'checked';  ?>></TD>
		<TD>Use of cup:</TD>
		<TD><INPUT TYPE="checkbox" NAME="use_of_cup" value="yes" <?if ( $obj["use_of_cup"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Junk food:</TD>
		<TD><INPUT TYPE="checkbox" NAME="junk_food" value="yes" <?if ( $obj["junk_food"]!='' ) print 'checked';  ?>></TD>
		<TD>No toilet training:</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_toilet_training" value="yes" <?if ( $obj["no_toilet_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Dental Hygiene</TD>
		<TD><INPUT TYPE="checkbox" NAME="dental_hygiene" value="yes" <?if ( $obj["dental_hygiene"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>

	<!-- // 15 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 411 AND $this->patient_demo['alldays'] <= 500 ) {
	?>
	<TR>
		<TD>Tantrum, normal</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_toilet_training" value="yes" <?if ( $obj["no_toilet_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Discipline:</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_toilet_training" value="yes" <?if ( $obj["no_toilet_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Safety:</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_toilet_training" value="yes" <?if ( $obj["no_toilet_training"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Toilet training</TD>
		<TD><INPUT TYPE="checkbox" NAME="preparing_training" value="yes" <?if ( $obj["preparing_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Sleeping:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sleeping" value="yes" <?if ( $obj["sleeping"]!='' ) print 'checked';  ?>></TD>
		<TD>Eating:</TD>
		<TD><INPUT TYPE="checkbox" NAME="eating" value="yes" <?if ( $obj["eating"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>


	<!-- // 18 months -->
	<?php

	if ($this->patient_demo['alldays'] >= 501 AND $this->patient_demo['alldays'] <= 640 ) {
	?>
	<TR>
		<TD>Snacks:</TD>
		<TD><INPUT TYPE="checkbox" NAME="snacks" value="yes" <?if ( $obj["snacks"]!='' ) print 'checked';  ?>></TD>
		<TD>No Botle:</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_botle" value="yes" <?if ( $obj["no_botle"]!='' ) print 'checked';  ?>></TD>
		<TD>Sibling Iteration:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sibling_iteration" value="yes" <?if ( $obj["sibling_iteration"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Toilet training:</TD>
		<TD><INPUT TYPE="checkbox" NAME="toilet_training" value="yes" <?if ( $obj["toilet_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Read to child:</TD>
		<TD><INPUT TYPE="checkbox" NAME="read_to_child" value="yes" <?if ( $obj["read_to_child"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?		
	}
	?>
	<!-- // 24 months to 3 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 641 AND $this->patient_demo['alldays'] <= 1095 ) {
	?>
	<TR>
		<TD>Car seat:</TD>
		<TD><INPUT TYPE="checkbox" NAME="car_seat" value="yes" <?if ( $obj["car_seat"]!='' ) print 'checked';  ?>></TD>
		<TD>Toilet training:</TD>
		<TD><INPUT TYPE="checkbox" NAME="toilet_training" value="yes" <?if ( $obj["toilet_training"]!='' ) print 'checked';  ?>></TD>
		<TD>Read to child:</TD>
		<TD><INPUT TYPE="checkbox" NAME="read_to_child" value="yes" <?if ( $obj["read_to_child"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Guns:</TD>
		<TD><INPUT TYPE="checkbox" NAME="guns" value="yes" <?if ( $obj["guns"]!='' ) print 'checked';  ?>></TD>
		<TD>Self Care:</TD>
		<TD><INPUT TYPE="checkbox" NAME="self_care" value="yes" <?if ( $obj["self_care"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?		
	}
	?>

	<!-- // 3 to 5 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 1096 AND $this->patient_demo['alldays'] <= 1825 ) {
	?>
	<TR>
		<TD>School readiness:</TD>
		<TD><INPUT TYPE="checkbox" NAME="school_readiness" value="yes" <?if ( $obj["school_readiness"]!='' ) print 'checked';  ?>></TD>
		<TD>Household chores:</TD>
		<TD><INPUT TYPE="checkbox" NAME="household_chores" value="yes" <?if ( $obj["household_chores"]!='' ) print 'checked';  ?>></TD>
		<TD>Demographic info:</TD>
		<TD><INPUT TYPE="checkbox" NAME="own_demographic" value="yes" <?if ( $obj["own_demographic"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Seat belts:</TD>
		<TD><INPUT TYPE="checkbox" NAME="seat_belts" value="yes" <?if ( $obj["seat_belts"]!='' ) print 'checked';  ?>></TD>
		<TD>No matches:</TD>
		<TD><INPUT TYPE="checkbox" NAME="no_matches" value="yes" <?if ( $obj["no_matches"]!='' ) print 'checked';  ?>></TD>
		<TD>Street safety:</TD>
		<TD><INPUT TYPE="checkbox" NAME="street_safety" value="yes" <?if ( $obj["street_safety"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Sexual curiosity:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sexual_curiosity" value="yes" <?if ( $obj["sexual_curiosity"]!='' ) print 'checked';  ?>></TD>
		<TD colspan=4></TD>
	</TR>
	<?		
	}
	?>
	<!--  // 5 to 7.5 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 1826 AND $this->patient_demo['alldays'] <= 2737 ) {
	?>
	<TR>
		<TD>Communication:</TD>
		<TD><INPUT TYPE="checkbox" NAME="communication" value="yes" <?if ( $obj["communication"]!='' ) print 'checked';  ?>></TD>
		<TD>Limit Setting:</TD>
		<TD><INPUT TYPE="checkbox" NAME="limit_setting" value="yes" <?if ( $obj["limit_setting"]!='' ) print 'checked';  ?>></TD>
		<TD>Peer Relations:</TD>
		<TD><INPUT TYPE="checkbox" NAME="peer_relations" value="yes" <?if ( $obj["peer_relations"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<TR>
		<TD>Parental role:</TD>
		<TD><INPUT TYPE="checkbox" NAME="parental_role" value="yes" <?if ( $obj["parental_role"]!='' ) print 'checked';  ?>></TD>
		<TD>Reg Physic act:</TD>
		<TD><INPUT TYPE="checkbox" NAME="reg_physic_act" value="yes" <?if ( $obj["reg_physic_act"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?		
	}
	?>

	<!-- // 7.5 to 9.5 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 2738 AND $this->patient_demo['alldays'] <= 3467 ) {
	?>
	<TR>
		<TD>Sexual info:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sexual_info" value="yes" <?if ( $obj["sexual_info"]!='' ) print 'checked';  ?>></TD>
		<TD>Reg Physic Act:</TD>
		<TD><INPUT TYPE="checkbox" NAME="reg_physic_act" value="yes" <?if ( $obj["reg_physic_act"]!='' ) print 'checked';  ?>></TD>
		<TD>Peer Relations:</TD>
		<TD><INPUT TYPE="checkbox" NAME="peer_relations" value="yes" <?if ( $obj["peer_relations"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>

	<!-- // 9.5 to 11 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 3468 AND $this->patient_demo['alldays'] <= 4015 ) {
	?>
	<TR>
		<TD>Puberty:</TD>
		<TD><INPUT TYPE="checkbox" NAME="puberty" value="yes" <?if ( $obj["puberty"]!='' ) print 'checked';  ?>></TD>
		<TD>Comunicates Affection:</TD>
		<TD><INPUT TYPE="checkbox" NAME="affection" value="yes" <?if ( $obj["affection"]!='' ) print 'checked';  ?>></TD>
		<TD></TD>
		<TD></TD>
	</TR>
	<?		
	}
	?>

	<!-- // more than 9.5 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 3468 ) {
	?>
	<TR>
		<TD>Reg Physic Act:</TD>
		<TD><INPUT TYPE="checkbox" NAME="reg_physic_act" value="yes" <?if ( $obj["reg_physic_act"]!='' ) print 'checked';  ?>></TD>
		<TD>Sexual Info:</TD>
		<TD><INPUT TYPE="checkbox" NAME="sexual_info" value="yes" <?if ( $obj["sexual_info"]!='' ) print 'checked';  ?>></TD>
		<TD>Smoke, Alcohol, Drugs</TD>
		<TD><INPUT TYPE="checkbox" NAME="smoke_alcohol_drugs" value="yes" <?if ( $obj["smoke_alcohol_drugs"]!='' ) print 'checked';  ?>></TD>
	</TR>
	<?		
	}
	?>
	<!-- // more than 13.5 years -->
	<?php

	if ($this->patient_demo['alldays'] >= 4745 ) {
	?>
	<TR>
		<TD>Pregnancy prevention:</TD>
		<TD><INPUT TYPE="checkbox" NAME="pregnancy_prevention" value="yes" <?if ( $obj["pregnancy_prevention"]!='' ) print 'checked';  ?>></TD>
		<TD colspan=4></TD>
	</TR>
	<?		
	}
	?>
	<!-- // common part since 18 monhts -->
	<?php

	if ($this->patient_demo['alldays'] >= 501  ) {
	?>
	<TR>
	<TR>
		<TD>Dental Higiene:</TD>
		<TD><INPUT TYPE="checkbox" NAME="dental_hygiene" value="yes" <?if ( $obj["dental_hygiene"]!='' ) print 'checked';  ?>></TD>
		<TD>Nutr Assesment:</TD>
		<TD><SELECT NAME="nutritional_assessment"><option <?if ( $obj["nutritional_assessment"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["nutritional_assessment"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
		<TD>Nutr Asses. ref:</TD>
		<TD><SELECT NAME="nutritional_assessment_referred"><option <?if ( $obj["nutritional_assessment_referred"]=='no' ) print 'selected';  ?>>no<option <?if ( $obj["nutritional_assessment_referred"]=='yes' ) print 'selected';  ?>>yes</SELECT></TD>
	</TR>
	<?		
	}
	?>
	<!-- closing the variable part -->
	</table>
	<TABLE span class="text">
	<tr><td colspan=8><b>Physical Exam:</b>Are the following Normal?</td></tr>
	<TR>
		<TD>Skin:</TD>
		<TD><SELECT NAME="normal_skin"><option <?php if ( $obj["normal_skin"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_skin"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Head:</TD>
		<TD><SELECT NAME="normal_head"><option <?php if ( $obj["normal_head"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_head"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Eyes:</TD>
		<TD><SELECT NAME="normal_eyes"><option <?php if ( $obj["normal_eyes"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_eyes"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Ears:</TD>
		<TD><SELECT NAME="normal_ears"><option <?php if ( $obj["normal_ears"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_ears"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
	</TR>
	<TR>
		<TD>Nose:</TD>
		<TD><SELECT NAME="normal_nose"><option <?php if ( $obj["normal_nose"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_nose"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Mouth/Thr.</TD>
		<TD><SELECT NAME="normal_mouth_thr"><option <?php if ( $obj["normal_mouth_thr"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_mouth_thr"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Nodes:</TD>
		<TD><SELECT NAME="normal_nodes"><option <?php if ( $obj["normal_nodes"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_nodes"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Heart:</TD>
		<TD><SELECT NAME="normal_heart"><option <?php if ( $obj["normal_heart"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_heart"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
	</TR>
	<TR>
		<TD>Lungs:</TD>
		<TD><SELECT NAME="normal_lungs"><option <?php if ( $obj["normal_lungs"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_lungs"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Abdomen:</TD>
		<TD><SELECT NAME="normal_abdomen"><option <?php if ( $obj["normal_abdomen"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_abdomen"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Fem. Pulse:</TD>
		<TD><SELECT NAME="normal_fem_pulse"><option <?php if ( $obj["normal_fem_pulse"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_fem_pulse"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Ext. Gen:</TD>
		<TD><SELECT NAME="normal_genitalia"><option <?php if ( $obj["normal_genitalia"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_genitalia"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
	</TR>
	<TR>
		<TD>Extrem:</TD>
		<TD><SELECT NAME="normal_extremities"><option <?php if ( $obj["normal_extremities"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_extremities"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Spine:</TD>
		<TD><SELECT NAME="normal_spine"><option <?php if ( $obj["normal_spine"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_spine"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Neuro:</TD>
		<TD><SELECT NAME="normal_neuro"><option <?php if ( $obj["normal_neuro"]=='yes' ) print 'selected'; ?> >yes<option <?if ( $obj["normal_neuro"]=='no' ) print 'selected';  ?>>no</SELECT></TD>
		<TD>Tanner St.</TD>
		<TD><SELECT NAME="tanner_stage"><option <?php if ( $obj["tanner_stage"]=='n/a' ) print 'selected'; ?> >n/a<option <?php if ( $obj["tanner_stage"]=='I' ) print 'selected'; ?> >I<option <?if ( $obj["tanner_stage"]=='II' ) print 'selected';  ?>>II<option <?if ( $obj["tanner_stage"]=='III' ) print 'selected';  ?>>III<option <?if ( $obj["tanner_stage"]=='IV' ) print 'selected';  ?>>IV<option <?if ( $obj["tanner_stage"]=='V' ) print 'selected';  ?>>V</SELECT></TD>
	</TR>
	<tr>
		<TD colspan=8><b>Notes, Coments, Assesment & Plan</b></TD>
	</TR>
	<tr>
		<TD colspan=8><TEXTAREA NAME="notes" ROWS="2" COLS="50"><?php echo $obj["notes"] ?></TEXTAREA></TD>
	</tr>
	</TABLE>

	</FORM>
	<?php

	}
}
?>
