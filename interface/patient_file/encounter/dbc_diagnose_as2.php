<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="../../../library/js/jquery.js"></script>

<script type="text/javascript">
function win() {
  window.opener.location.reload(true);
    self.close();
}
    
</script>

<script type="text/javascript">
$(document).ready(function(){
  $('#box2').hide(); $('#box3').hide(); $('#box4').hide();
    $('#box1').bind('change', function(){
      $('#box2').html(
        $.ajax({
          type: 'POST',
          url: 'as.php',
          data: 'z=' + $('#box1').val() + '&lvl=2',
          async: false
        }).responseText
      )
      });
      $('#box1').bind('focus', function(){
        $('#box2').show(); $('#box3').hide(); $('#box4').hide(); $('#box5').hide();
      });
                  
      
      $('#box2').bind('change', function(){
      $('#box3').html(
        $.ajax({
          type: 'POST',
          url: 'as.php',
          data: 'z=' + $('#box2').val() + '&lvl=2',
          async: false
        }).responseText
      )
      }); 
      // if empty box, prevent the next to show
      $('#box2').bind('click', function(){
        if ( $('#box2').val() != 0 ) {
          $('#box3').show(); }
        else {
          $('#box3').hide();}
          $('#box4').hide(); $('#box5').hide();
      });
                                                            
      
      
      $('#box3').bind('change', function(){
      $('#box4').html(
        $.ajax({
          type: 'POST',
          url: 'as.php',
          data: 'z=' + $('#box3').val() + '&lvl=2',
          async: false
        }).responseText
      )
      });
  // if empty box, prevent the next to show
     $('#box3').bind('click', function(){
       if ( $('#box3').val() != 0 ) {
         $('#box4').show(); }
       else {
         $('#box4').hide(); $('#box5').hide();
       }
     });

     $('#box4').bind('change', function(){
         $('#box5').html(
             $.ajax({
                type: 'POST',
                url: 'as.php',
                data: 'z=' + $('#box4').val() + '&lvl2',
                async: false
             }).responseText
         )
     });
     // if empty box, prevent the next to show
     $('#box4').bind('click', function(){
       if ( $('#box4').val() != 0 ) {
         $('#box5').show(); }
       else {
         $('#box5').hide();
       }
     });
     
      
});
</script>
                                                                                
<?php
include_once("../../../interface/globals.php");

if ( isset($_POST['saveas']) ) { 
 // same logic as in javascript validation
   if ( $_POST['box5'] )    $as = $_POST['box5'];
   elseif ( $_POST['box4']) $as = $_POST['box4'];
   elseif ( $_POST['box3']) $as = $_POST['box3'];
   elseif ( $_POST['box2']) $as = $_POST['box2'];
   elseif ( $_POST['box1']) $as = $_POST['box1'];
   
   $trekken = $_POST['partial'];
   $arr_as = array('code' => $as, 'trekken' => $trekken);
   
   if ( verify_code($as, 2)) {
     $_SESSION['as2'][] = $arr_as;
   } else {
    echo '<script>alert("You must select again!")</script>';  
   }
   
   // verify the limit for diagnoses
   if ( count($_SESSION['as2'])  == 3 )
     echo '<script>window.close();</script>';
}
?>

</head>

<body onunload="win();" bgcolor="#A4FF8B">
  <p>Choose your AS II diagnose</p>

 <form method="post" target="_self">
  <table border=0 cellspacing=0 cellpadding=0 >
  <tr>
   <td width='1%' nowrap>
 <select name="box1" id="box1">
  <?php
  $rlvone = records_level1('as2');
  foreach ($rlvone as $rlv) {
    echo '<option value=\'' .$rlv['cl_diagnose_code']. '\'>' .substr($rlv['cl_diagnose_element'], 0, 70). '</option>';
  } ?>
</select>
       </td></tr>
   <tr><td>
<select id="box2" name="box2"></select>
       </td></tr>
     <tr><td>
<select id="box3" name="box3"></select>
    </td></tr>
  </tr>
  <tr><td>
<select id="box4" name="box4"></select>
    </td></tr>
  <tr><td>
<select id="box5" name="box5"></select>
    </td></tr>
<tr>
  <td>Trekken van<input type="checkbox" name="partial" id="partial"/></td>
</tr>

 </table>
 <input type="submit" value="Choose" name="saveas"/>
 <input type="button" value="Close" onclick="window.close();" />
 </form>
                                                          
  
</body>
</html>
