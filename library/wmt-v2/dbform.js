function toggleDBPanel(divid,imgid1,imgid2,barid)
{
  if(document.getElementById(divid).style.display == 'none') {
    document.getElementById(divid).style.display = 'block';
    document.getElementById(imgid1).src = '../../../library/wmt/fill-090.png';
    document.getElementById(imgid2).src = '../../../library/wmt/fill-090.png';
    document.getElementById(barid).style.borderBottom = 'solid 1px black';
  } else {
    document.getElementById(divid).style.display = 'none';
    document.getElementById(imgid1).src = '../../../library/wmt/fill-270.png';
    document.getElementById(imgid2).src = '../../../library/wmt/fill-270.png';
    document.getElementById(barid).style.borderBottom = 'none';
  }
}

function VitalConfirm(v_flag, v_id, v_timestamp)
{
  alert("In the Confirm "+v_flag+" "+v_id+" "+v_timestamp);
  var flag_value = document.getElementById(v_flag).value;
  alert("1");
  var vid = document.getElementById(v_id).value;
  alert("2");
  var ts = document.getElementById(v_timestamp).value;
  alert("The VID is: "+vid);
  if(flag_value == 'new' || flag_value == 'update') {
    return true;
  }
  if(!vid || vid == '0') {
    return true;
  }
  var message = "Update vitals taken "+ts+" ?";
  var mode = confirm(message);
  alert("Mode: "+mode);
  if(mode) {
    document.getElementById(v_flag).value = 'update';
  } else {
    document.getElementById(v_flag).value = 'new';
  }
  alert("The new mode is: "+document.getElementById(v_flag).value);
}

