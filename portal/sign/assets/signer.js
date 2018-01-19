/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
function placeImg(data, el){
	if( data.responseText == "error"){
		$(el).attr( 'src', "" );
		alert('Error: Patient Id or User Id Missing !');
		return;
	}
	else if( data.responseText == "insert error"){
		$(el).attr( 'src', "" );
		alert('Error: Insert!');
		return;
	}
	else if( data.responseText == "waiting" && $(el).attr( 'type' ) == 'patient-signature'){
		$(el).attr( 'src', "" );
		alert('Patient Signature not on file. Please Sign');
		$("#isAdmin").attr('checked',false);
		$("#openSignModal").modal("show");
		return;
	}
	else if( data.responseText == "waiting" && $(el).attr( 'type' ) == 'admin-signature'){
		$(el).attr( 'src', "" );
		alert('Provider Signature not on file! Please sign.');
		$("#isAdmin").attr('checked',true);
		$("#openSignModal").modal("show");
		return;
	}
	var i = new Image();
	i.onload = function(){
		$(el).attr( 'src', i.src );// display image
	};
	i.src ='data:image/png;base64,'+data.responseText; // load image
}
function clearSig(el){
	$(el).prev().attr( 'src', '' );
}
function focusPad(){
	$('#drawpad').focus();
}
function signDoc(othis) {
	try{
		if( webRoot !== undefined && webRoot !== null)
			var libUrl = webRoot+'/portal/';
	}
	catch(e){	var libUrl = "./";}
	var xmlhttp;
	if($("#isAdmin").is(':checked') == false)
		var params = "pid="+cpid+"&user="+cuser+"&signer="+ptName+"&type=patient-signature"+"&output="+$('#output').val();
	else{
		var params = "pid=0"+"&user="+cuser+"&signer="+cuser+"&type=admin-signature"+"&output="+$('#output').val();
	}
	xmlhttp = new XMLHttpRequest();
    xmlhttp.open("POST", libUrl+"sign/lib/save-signature.php?", true );
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.setRequestHeader("Content-length", params.length);
    xmlhttp.setRequestHeader("Connection", "close");
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
        	$("#loading").toggle();
        	$("#openSignModal").modal("hide");
        	//$("#isAdmin").attr('checked',false);
        }
    };
    xmlhttp.send(params);
    $("#loading").toggle();
	}
function getSignature(othis) {
	var isLink = $(othis).attr( 'src').indexOf('signhere');
	if( $(othis).attr( 'src') != signhere && isLink == -1 ) {
		$(othis).attr( 'src', signhere );
		return;
	}
	try{
		if( webRoot !== undefined && webRoot !== null)
			var libUrl = webRoot+'/portal/';
	}
	catch(e){	var libUrl = "./";}
	var xmlhttp;

    if( $(othis).attr( 'type' ) == 'admin-signature' )
    	var params = "pid="+cpid+"&user="+cuser+"&signer="+cuser+"&type="+$(othis).attr( 'type' );
    else
    	var params = "pid="+cpid+"&user="+cuser+"&signer="+ptName+"&type="+$(othis).attr( 'type' );

    $(othis).attr( 'src', libUrl+"sign/assets/loadingplus.gif" );
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        	placeImg( xmlhttp,othis);
        }
    };
    xmlhttp.open("GET", libUrl+"sign/lib/show-signature.php?"+params );
    xmlhttp.send(null);
}
function placeImgDot(data, el){
	if( data.responseText == "error"){
		alert('Error: No Patient Id!');
		return;
	}
	else if( data.responseText == "insert error"){
		alert('Error: Insert!');
		return;
	}
	else if( data.responseText == "waiting" && $(el).attr( 'type' ) == 'patient-signature'){
		alert('Waiting for Patient Signature\nSelect Patient: '+pid+' on Signature pad and have patient sign \nthen accept terms, afterwards, return and click form.');
		return;
	}
	else if( data.responseText == "waiting" && $(el).attr( 'type' ) == 'admin-signature'){
		alert('Waiting for '+user+' Signature!\nSelect your Login User Name: '+user+' on signature pad and sign.');
		return;
	}
	$(el).height(70);
	var i = new Image();
	i.onload = function(){
	 //alert( i.width+", "+i.height );
		nPos = new Object();
		nPos = $(el).offset();
		if( $(el).attr( 'toporg' )){ // don't offset again	get original offset
			nPos.left = parseInt($(el).attr( 'leftorg' ));
			nPos.top += parseInt($(el).attr( 'toporg' ));
			$(el).offset(nPos);
		}
		var hos = (70.0-(i.height/358 * 70.0));
		if(i.height > 198)
			hos += 6;
		else hos += 8;
		nPos.left = parseInt(nPos.left);
		nPos.top = parseInt(nPos.top) - hos;

		$(el).offset(nPos);

		$(el).attr( 'src', i.src );// display image
		$(el).attr( 'leftorg', nPos.left) ;
		$(el).attr( 'toporg', hos) ;
	};
	i.src ='data:image/png;base64,'+data.responseText; // load image
}
var signhere = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFQAAABUCAYAAAAcaxDBAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEwAACxMBAJqcGAAAAphJREFUeJzt2j1oE2Ecx/FvMdW+biK+1EVxEnESm0Xcxero6Gqto+iqKHQTcXUQHBTqUKiL0KUWFAQp6KQdRKPopA2+VjHn8L/jee54ckm0zeXq7wOBp/dcycOXa3N5EhAREREREREREREREREREZH1sqnoBfSIHcBpoAp8AOqFrqaktgH9wBEsYBQ/vgHHC1xXKe0EXgDzwGcs5BLwOB6vAhOFra5kkpiR95gHBoEKMIOLeqygNZaGH/M98DMe38NiQjrqJ2C0+8ssBz/ma2APcBIXdYZ01Fp8/Gi3F1oGoZiJUNQq9icfAfu7utIS2I6LWSMdM+FHfYB71Z/r0hpLZRgXtI5dfSEncFEjYCH+XcmYBBq4UHVgvMm5E6RfqPTGJ8OPeQNYJD/qGe/8a11aY2lM4q7KJM4I8BAX9bB3vmLmCMVMjGD/HyNgBYuqmDnyYiaGcVfqFxSzqbO0jgl2X/rSO1cxA/4mZg141OL8/1K7MXeRjrkXGFj31ZXMv8SUjCk6j/kGxQzqJOYyiplLMddQFRdzCehrcl42ZmiXSYD7tL5/HEMx23IQi/Qb2+wIRVXMDtzFQt2Jf75IOqofM7szLxn7sCuzARzwjvtRk512xWzDTSzWbOb4AG73SDHbtBu3k34oPjYKXMC+OpPEfIVituU6FqwBnAMuAx9JX5VT9MD78mb3cJ0YBLZkjv0CvsbjIWBzZn4V+B6PK9imry/5fwiwFQs2FHjuZWAauB0/54ZwhfR9YYS9GiduBeanvfnxwPyKN38pMP8MOEUPfohWaX1K4ca88RPgKvZ5eVTMcvKtRdCFwLHn3ngOeJuZX/TG77BIvh/e+Dy2U/QU+1KXiIiIiIiIiIiIiIiIiIiIiIhsfH8AekL6s5feEc0AAAAASUVORK5CYII=";
