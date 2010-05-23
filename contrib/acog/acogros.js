// (c) Aire :) 2005

function InitSection(){
  var i; // elements iterator
  var funct;
  var l = document.my_form.elements.length;
  for (i=0;i<l;i++){
    if ( document.my_form.elements[i].type == "checkbox" ){
      if (( document.my_form.elements[i].name.indexOf("negativ") != -1 ) && ( document.my_form.elements[i].name.indexOf("ros_") != -1 ))
      {
        eval ("document.my_form.elements[i].onclick = function() { ToggleSection('" 
            + document.my_form.elements[i].name.substring(0,document.my_form.elements[i].name.indexOf('_negati'))
            +"', document.my_form."+document.my_form.elements[i].name+".checked); }");
      } 
    }
  }
  for (i=0;i<l;i++){
    if ( document.my_form.elements[i].type == "checkbox" ){
      if (( document.my_form.elements[i].name.indexOf("negativ") != -1 ) && ( document.my_form.elements[i].name.indexOf("ros_") != -1 ))
      {
        if (document.my_form.elements[i].checked == true){
          ToggleSection(document.my_form.elements[i].name.substring(0,document.my_form.elements[i].name.indexOf('_negati')), true);
        }
      } 
    }
  }
  return 0;
}


function ToggleSection(section,flag){
  var i;
  var mf = eval('document.my_form');
  var l = mf.elements.length;
  for (i=0;i<l;i++){
    
    if ( mf.elements[i].type == "checkbox" ){
      if (( mf.elements[i].name.indexOf(section) == 0 ) && ( mf.elements[i].name.indexOf("negativ") == -1 ))
      {
        //mf.elements[i].checked = false;
        mf.elements[i].disabled = flag;
      }
    }
  }
  return 1;
}