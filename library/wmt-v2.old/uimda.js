function toggleU1PanelState(divid,imgid1,imgid2,barid)
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

