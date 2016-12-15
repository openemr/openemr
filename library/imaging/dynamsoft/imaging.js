//get the patient and category id frpm url
var splitUrl = document.URL.split('?');
var params = splitUrl[1];

//get the bash path from php
var BASE_PATH = getBasePath();
//open scanner screen in new page
document.getElementById('show-scan').addEventListener("click", function(){
    newWindow = window.open(BASE_PATH + "/library/imaging/dynamsoft/scanerUpload.php?"+params + "&BASE_PATH=" + BASE_PATH, 'scanerPage', "width=870,height=800,left=150,top=40,menubar=0,titlebar=0,toolbar=0");
});
//open webcam screen in new page
document.getElementById('show-webcam').addEventListener("click", function(){
    newWindow = window.open(BASE_PATH + "/library/imaging/dynamsoft/webcamUpload.php?"+params + "&BASE_PATH=" + BASE_PATH, 'webcamPage', "width=870,height=800,left=150,top=40,menubar=0,titlebar=0,toolbar=0");
});

//close the window on upload and refresh
function closeAndRefresh(){
    newWindow.close();
    location.reload();
}
//return the base path from php
function getBasePath() {
    var scripts = document.getElementsByTagName('script');
    var lastScript = scripts[scripts.length-1];
    var scriptName = lastScript;
    return scriptName.getAttribute('data-basePath');
}