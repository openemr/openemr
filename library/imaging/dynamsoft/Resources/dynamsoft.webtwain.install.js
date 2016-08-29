
function OnWebTwainNotFoundOnWindowsCallback(ProductName, InstallerUrl, bHTML5, bIE, bSafari, bSSL, strIEVersion) {
	_show_install_dialog(ProductName, InstallerUrl, bHTML5, false, bIE, bSafari, bSSL, strIEVersion);
}

function OnWebTwainNotFoundOnMacCallback(ProductName, InstallerUrl, bHTML5, bIE, bSafari, bSSL, strIEVersion) {
	_show_install_dialog(ProductName, InstallerUrl, bHTML5, true, bIE, bSafari, bSSL, strIEVersion);
}

function _show_install_dialog(ProductName, InstallerUrl, bHTML5, bMac, bIE, bSafari, bSSL, strIEVersion){
	
	var _height = 220, ObjString = [
			'<div class="dwt-box-title">',
			ProductName,
			' is not installed</div>',
			'<div style="margin:20px;text-align:center"><a id="dwt-btn-install" target="_blank" href="',
			InstallerUrl,
			'" onclick="Dynamsoft_OnClickInstallButton()"><div class="dwt-button"></div></a>',
			'<i>* Please manually install it</i></div>'];

	if(bHTML5){

		if(bIE){
			ObjString.push('<div>');
			ObjString.push('If you still see the dialog after installing the scan plugin, please<br />');
			ObjString.push('1. <a href="http://windows.microsoft.com/en-us/windows/security-zones-adding-removing-websites#1TC=windows-7">add the current website to the Trusted Sites list</a>.<br />');
			ObjString.push('2. refresh your browser.');
			ObjString.push('</div>');
			
			_height = 240;
		} else {

			if(bMac && bSafari && bSSL){
				ObjString.push('<div>');
				ObjString.push('After the installation, you also need to refer to <br />');
				ObjString.push('<a href="http://kb.dynamsoft.com/questions/901">this article</a> to enable the scan plugin on the current HTTPS website.');
				ObjString.push('</div>');
				_height = 270;
			}
			
			ObjString.push('<div class="dwt-red" style="padding-top: 10px;">After installation, please REFRESH your browser.</div>');
		}

	} else {
		if(bIE){
			ObjString.push('<div>');
			ObjString.push('After the installation, please<br />');
			ObjString.push('1. refresh the browser<br />');
			ObjString.push('2. allow "DynamicWebTWAIN" add-on to run by right clicking on the Information Bar in the browser.');
			ObjString.push('</div>');
			_height = 240;
		} else {
			ObjString.push('<p class="dwt-red" style="padding-top: 10px;">After installation, please REFRESH your browser.</p>');
		}
	}
	
	Dynamsoft.WebTwainEnv.ShowDialog(392, _height, ObjString.join(''));
}

function OnWebTwainOldPluginNotAllowedCallback(ProductName) {
    var ObjString = [
		'<div class="dwt-box-title">',
		ProductName,
		' plugin is not allowed to run on this site.</div>',
		'<ul>',
		'<li>Please click "<b>Always run on this site</b>" for the prompt "',
		ProductName,
		' Plugin needs your permission to run", then <a href="javascript:void(0);" style="color:blue" class="ClosetblCanNotScan">close</a> this dialog OR refresh/restart the browser and try again.</li>',
		'</ul>'];

	Dynamsoft.WebTwainEnv.ShowDialog(392, 227, ObjString.join(''));
}

function OnWebTwainNeedUpgradeCallback(ProductName, InstallerUrl, bHTML5, bMac, bIE, bSafari, bSSL, strIEVersion){
	var ObjString = ['<div class="dwt-box-title"></div>',
		'<div style="font-size: 15px;">',
		'This page is using a newer version of Dynamic Web TWAIN than your local copy. Please download and upgrade now.',
		'</div>',
		'<a id="dwt-btn-install" target="_blank" href="',
		InstallerUrl,
		'" onclick="Dynamsoft_OnClickInstallButton()"><div class="dwt-button"></div></a>',
		'<div style="text-align:center"><i>* Please manually install it</i></div><p></p>'], _height = 220;

	if(bHTML5){
		ObjString.push('<div class="dwt-red">Please REFRESH your browser after the upgrade.</div>');	
	} else {
		
		if(bIE){
			_height = 240;
			ObjString.push('<div class="dwt-red">');
			ObjString.push('Please EXIT Internet Explorer before you install the new version.');
			ObjString.push('</div>');
		}
		else
		{
		    ObjString.push('<div class="dwt-red">Please RESTART your browser after the upgrade.</div>');	
		}
	}

	Dynamsoft.WebTwainEnv.ShowDialog(392, _height, ObjString.join(''));
}

function OnWebTwainPreExecuteCallback(){
	Dynamsoft.WebTwainEnv.OnWebTwainPreExecute();
}

function OnWebTwainPostExecuteCallback(){
	Dynamsoft.WebTwainEnv.OnWebTwainPostExecute();
}

