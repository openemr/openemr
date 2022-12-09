function togglePanelState(divid,imgid1,imgid2,barid)
{
  if(document.getElementById(divid).style.display == 'none') {
    document.getElementById(divid).style.display = 'block';
    document.getElementById(imgid1).src = '../../../library/wmt-v2/fill-090.png';
    document.getElementById(imgid2).src = '../../../library/wmt-v2/fill-090.png';
    document.getElementById(barid).style.borderBottom = 'solid 1px black';
  } else {
    document.getElementById(divid).style.display = 'none';
    document.getElementById(imgid1).src = '../../../library/wmt-v2/fill-270.png';
    document.getElementById(imgid2).src = '../../../library/wmt-v2/fill-270.png';
    document.getElementById(barid).style.borderBottom = 'none';
  }
}

function togglePCPanelState(divid,imgid1,imgid2,barid)
{
  if(document.getElementById(divid).style.display == 'none') {
    document.getElementById(divid).style.display = 'block';
    document.getElementById(imgid1).src = '../../../library/wmt-v2/fill-090.png';
    document.getElementById(imgid2).src = '../../../library/wmt-v2/fill-090.png';
    document.getElementById(barid).style.borderBottom = 'solid 1px black';
  } else {
    document.getElementById(divid).style.display = 'none';
    document.getElementById(imgid1).src = '../../../library/wmt-v2/fill-270.png';
    document.getElementById(imgid2).src = '../../../library/wmt-v2/fill-270.png';
    document.getElementById(barid).style.borderBottom = 'none';
  }
}

function ToggleText(divid)
{
  if(document.getElementById(divid).style.display == 'none') {
    document.getElementById(divid).style.display = 'block';
  } else {
    document.getElementById(divid).style.display = 'none';
  }
}

function VerifyYesChecks(YesBox, NoBox)
{
  if(document.getElementById(YesBox).checked == true) {
    document.getElementById(NoBox).value = '0';
    document.getElementById(NoBox).checked = false;
  }
}

function VerifyNoChecks(YesBox, NoBox)
{
  if(document.getElementById(NoBox).checked == true) {
    document.getElementById(YesBox).value = '0';
    document.getElementById(YesBox).checked = false;
  }
}
 
function clearM1Values()
{
  document.getElementById('ee_M_od_sph_pl').selectedIndex = '1';
  document.getElementById('ee_M_od_sph_od').selectedIndex = '-1';
  document.getElementById('ee_M_od_cyl_pl').selectedIndex = '1';
  document.getElementById('ee_M_od_cyl_od').selectedIndex = '-1';
  document.getElementById('ee_M_od_axis').selectedIndex = '-1';
  document.getElementById('ee_M_od_prism').selectedIndex = '-1';
  document.getElementById('ee_M_od_prism_text').selectedIndex = '-1';
  document.getElementById('ee_M_od_vd').selectedIndex = '-1';
  document.getElementById('ee_M_od_va').selectedIndex = '-1';
  document.getElementById('ee_M_od_va_add').selectedIndex = '-1';
  document.getElementById('ee_M_os_sph_pl').selectedIndex = '1';
  document.getElementById('ee_M_os_sph_os').selectedIndex = '-1';
  document.getElementById('ee_M_os_cyl_pl').selectedIndex = '1';
  document.getElementById('ee_M_os_cyl_od').selectedIndex = '-1';
  document.getElementById('ee_M_os_axis').selectedIndex = '-1';
  document.getElementById('ee_M_os_prism').selectedIndex = '-1';
  document.getElementById('ee_M_os_prism_text').selectedIndex = '-1';
  document.getElementById('ee_M_os_vd').selectedIndex = '-1';
  document.getElementById('ee_M_os_va').selectedIndex = '-1';
  document.getElementById('ee_M_os_va_add').selectedIndex = '-1';
  document.getElementById('ee_M_read_od_pl').selectedIndex = '1';
  document.getElementById('ee_M_read_od').selectedIndex = '-1';
  document.getElementById('ee_M_read_os_pl').selectedIndex = '1';
  document.getElementById('ee_M_read_os').selectedIndex = '-1';
  document.getElementById('ee_M_tri').selectedIndex = '1';
  document.getElementById('ee_M_tri_od_pl').selectedIndex = '1';
  document.getElementById('ee_M_tri_od_add').selectedIndex = '-1';
  document.getElementById('ee_M_match').selectedIndex = '2';
  document.getElementById('ee_M_tri_os_pl').selectedIndex = '1';
  document.getElementById('ee_M_tri_os_add').selectedIndex = '-1';
  document.getElementById('ee_M_comm').value = '';
}
 
function clearM2Values()
{
  document.getElementById('ee_M2_od_sph_pl').selectedIndex = '1';
  document.getElementById('ee_M2_od_sph_od').selectedIndex = '-1';
  document.getElementById('ee_M2_od_cyl_pl').selectedIndex = '1';
  document.getElementById('ee_M2_od_cyl_od').selectedIndex = '-1';
  document.getElementById('ee_M2_od_axis').selectedIndex = '-1';
  document.getElementById('ee_M2_od_prism').selectedIndex = '-1';
  document.getElementById('ee_M2_od_prism_text').selectedIndex = '-1';
  document.getElementById('ee_M2_od_vd').selectedIndex = '-1';
  document.getElementById('ee_M2_od_va').selectedIndex = '-1';
  document.getElementById('ee_M2_od_va_add').selectedIndex = '-1';
  document.getElementById('ee_M2_os_sph_pl').selectedIndex = '1';
  document.getElementById('ee_M2_os_sph_os').selectedIndex = '-1';
  document.getElementById('ee_M2_os_cyl_pl').selectedIndex = '1';
  document.getElementById('ee_M2_os_cyl_od').selectedIndex = '-1';
  document.getElementById('ee_M2_os_axis').selectedIndex = '-1';
  document.getElementById('ee_M2_os_prism').selectedIndex = '-1';
  document.getElementById('ee_M2_os_prism_text').selectedIndex = '-1';
  document.getElementById('ee_M2_os_vd').selectedIndex = '-1';
  document.getElementById('ee_M2_os_va').selectedIndex = '-1';
  document.getElementById('ee_M2_os_va_add').selectedIndex = '-1';
  document.getElementById('ee_M2_read_od_pl').selectedIndex = '1';
  document.getElementById('ee_M2_read_od').selectedIndex = '-1';
  document.getElementById('ee_M2_read_os_pl').selectedIndex = '1';
  document.getElementById('ee_M2_read_os').selectedIndex = '-1';
  document.getElementById('ee_M2_tri').selectedIndex = '1';
  document.getElementById('ee_M2_tri_od_pl').selectedIndex = '1';
  document.getElementById('ee_M2_tri_od_add').selectedIndex = '-1';
  document.getElementById('ee_M2_match').selectedIndex = '2';
  document.getElementById('ee_M2_tri_os_pl').selectedIndex = '1';
  document.getElementById('ee_M2_tri_os_add').selectedIndex = '-1';
  document.getElementById('ee_M2_comm').value = '';
}

function copyM1Values()
{
  document.getElementById('ee_M2_od_sph_pl').selectedIndex =
          document.getElementById('ee_M_od_sph_pl').selectedIndex;
  document.getElementById('ee_M2_od_sph_od').selectedIndex =
          document.getElementById('ee_M_od_sph_od').selectedIndex;
  document.getElementById('ee_M2_od_cyl_pl').selectedIndex =
          document.getElementById('ee_M_od_cyl_pl').selectedIndex;
  document.getElementById('ee_M2_od_cyl_od').selectedIndex =
          document.getElementById('ee_M_od_cyl_od').selectedIndex;
  document.getElementById('ee_M2_od_axis').selectedIndex =
          document.getElementById('ee_M_od_axis').selectedIndex;
  document.getElementById('ee_M2_od_prism').selectedIndex =
          document.getElementById('ee_M_od_prism').selectedIndex;
  document.getElementById('ee_M2_od_prism_text').selectedIndex =
          document.getElementById('ee_M_od_prism_text').selectedIndex;
  document.getElementById('ee_M2_od_vd').selectedIndex =
          document.getElementById('ee_M_od_vd').selectedIndex;
  document.getElementById('ee_M2_od_va').selectedIndex =
          document.getElementById('ee_M_od_va').selectedIndex;
  document.getElementById('ee_M2_od_va_add').selectedIndex =
          document.getElementById('ee_M_od_va_add').selectedIndex;
  document.getElementById('ee_M2_os_sph_pl').selectedIndex =
          document.getElementById('ee_M_os_sph_pl').selectedIndex;
  document.getElementById('ee_M2_os_sph_os').selectedIndex =
          document.getElementById('ee_M_os_sph_os').selectedIndex;
  document.getElementById('ee_M2_os_cyl_pl').selectedIndex =
          document.getElementById('ee_M_os_cyl_pl').selectedIndex;
  document.getElementById('ee_M2_os_cyl_od').selectedIndex =
          document.getElementById('ee_M_os_cyl_od').selectedIndex;
  document.getElementById('ee_M2_os_axis').selectedIndex =
          document.getElementById('ee_M_os_axis').selectedIndex;
  document.getElementById('ee_M2_os_prism').selectedIndex =
          document.getElementById('ee_M_os_prism').selectedIndex;
  document.getElementById('ee_M2_os_prism_text').selectedIndex =
          document.getElementById('ee_M_os_prism_text').selectedIndex;
  document.getElementById('ee_M2_os_vd').selectedIndex =
          document.getElementById('ee_M_os_vd').selectedIndex;
  document.getElementById('ee_M2_os_va').selectedIndex =
          document.getElementById('ee_M_os_va').selectedIndex;
  document.getElementById('ee_M2_os_va_add').selectedIndex =
          document.getElementById('ee_M_os_va_add').selectedIndex;
  document.getElementById('ee_M2_read_od_pl').selectedIndex =
          document.getElementById('ee_M_read_od_pl').selectedIndex;
  document.getElementById('ee_M2_read_od').selectedIndex =
          document.getElementById('ee_M_read_od').selectedIndex;
  document.getElementById('ee_M2_read_os_pl').selectedIndex =
          document.getElementById('ee_M_read_os_pl').selectedIndex;
  document.getElementById('ee_M2_read_os').selectedIndex =
          document.getElementById('ee_M_read_os').selectedIndex;
  document.getElementById('ee_M2_tri').selectedIndex =
          document.getElementById('ee_M_tri').selectedIndex;
  document.getElementById('ee_M2_tri_od_pl').selectedIndex =
          document.getElementById('ee_M_tri_od_pl').selectedIndex;
  document.getElementById('ee_M2_tri_od_add').selectedIndex =
          document.getElementById('ee_M_tri_od_add').selectedIndex;
  document.getElementById('ee_M2_match').selectedIndex =
          document.getElementById('ee_M_match').selectedIndex;
  document.getElementById('ee_M2_tri_os_pl').selectedIndex =
          document.getElementById('ee_M_tri_os_pl').selectedIndex;
  document.getElementById('ee_M2_tri_os_add').selectedIndex =
          document.getElementById('ee_M_tri_os_add').selectedIndex;
  document.getElementById('ee_M2_comm').value =
          document.getElementById('ee_M_comm').value;
}

function CopyCurrentLensToNew() {
  document.getElementById('e2_cl_new1_od').value =
          document.getElementById('e2_cl_curr1_od').value;
  document.getElementById('e2_cl_new1_od_bc').value =
          document.getElementById('e2_cl_curr1_od_bc').value;
  document.getElementById('e2_cl_new1_od_diam').value =
          document.getElementById('e2_cl_curr1_od_diam').value;
  document.getElementById('e2_cl_new1_od_sph').value =
          document.getElementById('e2_cl_curr1_od_sph').value;
  document.getElementById('e2_cl_new1_od_cyl').value =
          document.getElementById('e2_cl_curr1_od_cyl').value;
  document.getElementById('e2_cl_new1_od_axis').value =
          document.getElementById('e2_cl_curr1_od_axis').value;
  document.getElementById('e2_cl_new1_od_add').value =
          document.getElementById('e2_cl_curr1_od_add').value;
  document.getElementById('e2_cl_new1_od_tint').value =
          document.getElementById('e2_cl_curr1_od_tint').value;

  document.getElementById('e2_cl_new1_os').value =
          document.getElementById('e2_cl_curr1_os').value;
  document.getElementById('e2_cl_new1_os_bc').value =
          document.getElementById('e2_cl_curr1_os_bc').value;
  document.getElementById('e2_cl_new1_os_diam').value =
          document.getElementById('e2_cl_curr1_os_diam').value;
  document.getElementById('e2_cl_new1_os_sph').value =
          document.getElementById('e2_cl_curr1_os_sph').value;
  document.getElementById('e2_cl_new1_os_cyl').value =
          document.getElementById('e2_cl_curr1_os_cyl').value;
  document.getElementById('e2_cl_new1_os_axis').value =
          document.getElementById('e2_cl_curr1_os_axis').value;
  document.getElementById('e2_cl_new1_os_add').value =
          document.getElementById('e2_cl_curr1_os_add').value;
  document.getElementById('e2_cl_new1_os_tint').value =
          document.getElementById('e2_cl_curr1_os_tint').value;
}

function CopyCurrentLensToNew2() {
  document.getElementById('e2_cl_new2_od').value =
          document.getElementById('e2_cl_curr1_od').value;
  document.getElementById('e2_cl_new2_od_bc').value =
          document.getElementById('e2_cl_curr1_od_bc').value;
  document.getElementById('e2_cl_new2_od_diam').value =
          document.getElementById('e2_cl_curr1_od_diam').value;
  document.getElementById('e2_cl_new2_od_sph').value =
          document.getElementById('e2_cl_curr1_od_sph').value;
  document.getElementById('e2_cl_new2_od_cyl').value =
          document.getElementById('e2_cl_curr1_od_cyl').value;
  document.getElementById('e2_cl_new2_od_axis').value =
          document.getElementById('e2_cl_curr1_od_axis').value;
  document.getElementById('e2_cl_new2_od_add').value =
          document.getElementById('e2_cl_curr1_od_add').value;
  document.getElementById('e2_cl_new2_od_tint').value =
          document.getElementById('e2_cl_curr1_od_tint').value;

  document.getElementById('e2_cl_new2_os').value =
          document.getElementById('e2_cl_curr1_os').value;
  document.getElementById('e2_cl_new2_os_bc').value =
          document.getElementById('e2_cl_curr1_os_bc').value;
  document.getElementById('e2_cl_new2_os_diam').value =
          document.getElementById('e2_cl_curr1_os_diam').value;
  document.getElementById('e2_cl_new2_os_sph').value =
          document.getElementById('e2_cl_curr1_os_sph').value;
  document.getElementById('e2_cl_new2_os_cyl').value =
          document.getElementById('e2_cl_curr1_os_cyl').value;
  document.getElementById('e2_cl_new2_os_axis').value =
          document.getElementById('e2_cl_curr1_os_axis').value;
  document.getElementById('e2_cl_new2_os_add').value =
          document.getElementById('e2_cl_curr1_os_add').value;
  document.getElementById('e2_cl_new2_os_tint').value =
          document.getElementById('e2_cl_curr1_os_tint').value;
}

function CopyFitting2() {
  document.getElementById('e2_cl_new2_od_move').value =
          document.getElementById('e2_cl_new1_od_move').value;
  document.getElementById('e2_cl_new2_od_deposit').value =
          document.getElementById('e2_cl_new1_od_deposit').value;
  document.getElementById('e2_cl_new2_od_tears').value =
          document.getElementById('e2_cl_new1_od_tears').value;
  document.getElementById('e2_cl_new2_od_rgp').value =
          document.getElementById('e2_cl_new1_od_rgp').value;
  document.getElementById('e2_cl_new2_od_lid').value =
          document.getElementById('e2_cl_new1_od_lid').value;
  document.getElementById('e2_cl_new2_od_rotate').value =
          document.getElementById('e2_cl_new1_od_rotate').value;
  document.getElementById('e2_cl_new2_od_degrees').value =
          document.getElementById('e2_cl_new1_od_degrees').value;

  document.getElementById('e2_cl_new2_os_move').value =
          document.getElementById('e2_cl_new1_os_move').value;
  document.getElementById('e2_cl_new2_os_deposit').value =
          document.getElementById('e2_cl_new1_os_deposit').value;
  document.getElementById('e2_cl_new2_os_tears').value =
          document.getElementById('e2_cl_new1_os_tears').value;
  document.getElementById('e2_cl_new2_os_rgp').value =
          document.getElementById('e2_cl_new1_os_rgp').value;
  document.getElementById('e2_cl_new2_os_lid').value =
          document.getElementById('e2_cl_new1_os_lid').value;
  document.getElementById('e2_cl_new2_os_rotate').value =
          document.getElementById('e2_cl_new1_os_rotate').value;
  document.getElementById('e2_cl_new2_os_degrees').value =
          document.getElementById('e2_cl_new1_os_degrees').value;
}

function ClearNewLens() {
  document.getElementById('e2_cl_new1_od').value = "";
  document.getElementById('e2_cl_new1_od_bc').value = "";
  document.getElementById('e2_cl_new1_od_diam').value = "";
  document.getElementById('e2_cl_new1_od_sph').value = "";
  document.getElementById('e2_cl_new1_od_cyl').value = "";
  document.getElementById('e2_cl_new1_od_axis').value = "";
  document.getElementById('e2_cl_new1_od_add').value = "";
  document.getElementById('e2_cl_new1_od_tint').value = "";

  document.getElementById('e2_cl_new1_os').value = "";
  document.getElementById('e2_cl_new1_os_bc').value = "";
  document.getElementById('e2_cl_new1_os_diam').value = "";
  document.getElementById('e2_cl_new1_os_sph').value = "";
  document.getElementById('e2_cl_new1_os_cyl').value = "";
  document.getElementById('e2_cl_new1_os_axis').value = "";
  document.getElementById('e2_cl_new1_os_add').value = "";
  document.getElementById('e2_cl_new1_os_tint').value = "";
}

function ClearNewLens2() {
  document.getElementById('e2_cl_new2_od').value = "";
  document.getElementById('e2_cl_new2_od_bc').value = "";
  document.getElementById('e2_cl_new2_od_diam').value = "";
  document.getElementById('e2_cl_new2_od_sph').value = "";
  document.getElementById('e2_cl_new2_od_cyl').value = "";
  document.getElementById('e2_cl_new2_od_axis').value = "";
  document.getElementById('e2_cl_new2_od_add').value = "";
  document.getElementById('e2_cl_new2_od_tint').value = "";

  document.getElementById('e2_cl_new2_os').value = "";
  document.getElementById('e2_cl_new2_os_bc').value = "";
  document.getElementById('e2_cl_new2_os_diam').value = "";
  document.getElementById('e2_cl_new2_os_sph').value = "";
  document.getElementById('e2_cl_new2_os_cyl').value = "";
  document.getElementById('e2_cl_new2_os_axis').value = "";
  document.getElementById('e2_cl_new2_os_add').value = "";
  document.getElementById('e2_cl_new2_os_tint').value = "";
}

function TimeStamp()
{
  var currentTime=new Date();
  var myStamp= currentTime.getFullYear();
  var myMonth= "00" + (currentTime.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + currentTime.getDate();
  myDays= myDays.slice(-2);
  var myHours= "00" + currentTime.getHours();
  myHours= myHours.slice(-2);
  var myMinutes= "00" + currentTime.getMinutes();
  myMinutes= myMinutes.slice(-2);
  var mySeconds= "00" + currentTime.getSeconds();
  mySeconds= mySeconds.slice(-2);
  myStamp= myStamp + "-" + myMonth + "-" + myDays + " " + myHours + ":" +
           myMinutes + ":" + mySeconds;

  document.getElementById('e2_T_time').value = myStamp;
}

