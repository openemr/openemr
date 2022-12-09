
function SubmitFavorite(base,wrap)
{
  document.forms[0].action=base+'&mode=fav&wrap='+wrap;
	document.forms[0].submit();
}

function ShowAllFavorites(base,wrap,formID)
{
  document.forms[0].action=base+'&all=all';
	document.forms[0].submit();
}

function ShowCodeFavorites(base,wrap,formID,code)
{
  document.forms[0].action=base+'&code='+code;
	document.forms[0].submit();
}

function UpdateFavorite(base,wrap,itemID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Favorites List ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('fav_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Favorites list entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'&mode=updatefav&wrap='+wrap+'&itemID='+itemID;
	document.forms[0].submit();
}

function DeleteFavorite(base,wrap,itemID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Favorites List ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('fav_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Favorites list entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'&mode=delfav&wrap='+wrap+'&itemID='+itemID;
	document.forms[0].submit();
}

