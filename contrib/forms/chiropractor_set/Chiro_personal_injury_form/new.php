<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Chiro_personal_injury_form");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form method=post action="<?echo $rootdir;?>/forms/Chiro_personal_injury_form/save.php?mode=new" name="Chiro_personal_injury_form" onSubmit="return top.restoreSession()">
<hr>
<h1> <? xl("Chiro personal injury form",'e') ?> </h1>
<hr>
<input type="submit" name="submit form" value="submit form" /> <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a>

<Table width="100%" cellpadding="0" cellspacing="0">       

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Patient Name/(Nombre):</td><td class="text"   ><input type="text" name="_patient_name"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Middle Name</td><td class="text"   ><input type="text" name="_middle_name"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >    

<table>

<tr><td class="text" > Last Name</td><td class="text"   ><input type="text" name="_last_name"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    colspan="3">

<table>

<tr><td class="text" > Address/ (Direction)</td><td class="text"   ><input type="text" name="_address_direction"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    width="33%">

<table>

<tr><td class="text" > City:</td><td class="text"   ><input type="text" name="_city"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"    width="33%">

<table>

<tr><td class="text" > State:</td><td class="text"   ><input type="text" name="_state"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"    width="33%">

<table>

<tr><td class="text" > Zip:</td><td class="text"   ><input type="text" name="_zip"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Phone #(Telefono) Home</td><td class="text"   ><input type="text" name="_phone_number_home"  /></td></tr>

</table>
    </td>

    <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" >Work</td><td class="text"   ><input type="text" name="_phone_number_work"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >&nbsp;

    
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Sex:(Sexo):</td><td class="text"   ><label><input type="checkbox" name="_sex[]" value="male" /> <? Xl("male",'e') ?> </label> <label><input type="checkbox" name="_sex[]" value="female" /> <? Xl("female",'e') ?> </label></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Date of Birth:(Feeha de Nacimiento)</td><td class="text"   ><input type="text" name="_date_of_birth"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Social Security.#:(Seguro Social)</td><td class="text"   ><input type="text" name="_social_security"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"     colspan="3">

<table>

<tr><td class="text" > Nature of Accident(Accidence):</td><td class="text"   ><label><input type="checkbox" name="_nature_of_accident[]" value="automobile" /> <? Xl("Automobile(Auto)",'e') ?> </label> <label><input type="checkbox" name="_nature_of_accident[]" value="slip and fall" /> <? Xl("Slip And Fall(Caida)",'e') ?> </label> <label><input type="checkbox" name="_nature_of_accident[]" value="work related" /> <? Xl("Work Related(Trabajo)",'e') ?> </label></td></tr>

</table>

<table>

<tr><td class="text" > Other(Otros)</td><td class="text"   ><input type="text" name="_other"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    colspan="3" >

    <table width="100%" cellpadding="0" cellspacing="0">

    <tr>

   <td class="text"    style="border:solid 1px #000000"    colspan="2">

<table>

<tr><td class="text" > Date of Accident: (Feeha da Accidente)</td><td class="text"   ><input type="text" name="_date_of_accident"  /></td></tr>

</table>
    </td>    
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    width="50%">

<table>

<tr><td class="text" > Insurance Name:</td><td class="text"   ><input type="text" name="_insurance_name"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"    width="50%">

<table>

<tr>
 <td class="text"   > Phone #:</td> 
 <td class="text"   ><input type="text" name="_phone_no"  /></td></tr>

</table>
    </td>   
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    colspan="2">     

<table>

<tr><td class="text" > Address (Direction):</td><td class="text"   ><input type="text" name="_address_of_insurance_company"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Claim # (Numerom de Recalmo):</td><td class="text"   ><input type="text" name="_claim_number"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Policy (Numero de Poliza):</td><td class="text"   ><input type="text" name="_policy_number"  /></td></tr>

</table>
    </td>  
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Attorney Name(Nombre de Abogado):</td><td class="text"   ><input type="text" name="_attorney_name"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr>
 <td class="text"   > Attorney Phone#(Telefone de Abogado)</td> 
 <td class="text"   ><input type="text" name="_attorney_phone_number"  /></td></tr>

</table>
    </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    colspan="2">

<table>

<tr><td class="text" > Attorney address / (Direccion):</td><td class="text"   ><input type="text" name="_attorney_address"  /></td></tr>

</table>
    </td>
    </tr>    

    </table>
    </td>
    </tr>

    <tr>

   <td class="text"       colspan="3" >

    <table width="100%" cellpadding="0" cellspacing="0">

    <tr>

    

   <td class="text"    style="border:solid 1px #000000"    width="50%">

<table>

<tr><td class="text" > Health Insurance(Plan Medico):</td><td class="text"   ><input type="text" name="_health_insurance"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"    width="50%">

<table>

<tr><td class="text" >Phone#</td><td class="text"   ><input type="text" name="_health_insurance_phone_number"  /></td></tr>

</table>
        </td>
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"    colspan="3">

<table>

<tr><td class="text" > Address: </td><td class="text"   ><input type="text" name="_address_of_health_insurance"  /></td></tr>

</table>
    </td>

    
    </tr>

    <tr>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Subscriber ID #</td><td class="text"   ><input type="text" name="_subscriber_id_number"  /></td></tr>

</table>
    </td>

   <td class="text"    style="border:solid 1px #000000"   >

<table>

<tr><td class="text" > Group #</td><td class="text"   ><input type="text" name="_group_number"  /></td></tr>

</table>
    </td>
    </tr>

    

    </table>
    </td>
    </tr>

    

    </table>

<table></table><input type="submit" name="submit form" value="submit form" /> <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a>

</form>
<?php
formFooter();
?>
