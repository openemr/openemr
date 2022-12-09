function UpdateConcept(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'prev_id_', 'Concept')) return false;
	document.forms[0].action=base+'&mode=updateconcept&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
		document.forms[0].action=base+'&mode=updateconcept&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteConcept(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'prev_id_', 'Concept')) return false;
  document.forms[0].action=base+'&mode=delconcept&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'&mode=delconcept&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
	return false;
}

