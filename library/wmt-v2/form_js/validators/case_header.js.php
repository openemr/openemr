// THIS SECTION WILL BE INCLUDED WITHIN A FUNCTION, JUST RETURN FALSE
// ON VALIDATION FAILURE
  /*
  var i;
  for (i=0; i<l; i++) {
  }
  */

  var sel = document.getElementById('<?php echo $field_prefix; ?>ins_data_id1');
  var csh = document.getElementById('<?php echo $field_prefix; ?>cash');
  if(!sel.selectedIndex && !csh.checked) {
    alert('Cash should be selected if no insurance is chosen');
    return false;
  }
