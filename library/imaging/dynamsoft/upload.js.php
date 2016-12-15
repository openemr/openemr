
function UploadImage() {
    if (DWObject) {
        // If no image in buffer, return the function
        /*DWObject.IfShowCancelDialogWhenImageTransfer = !document.getElementById('quietScan').checked;
         if (DWObject.HowManyImagesInBuffer == 0)
         return;*/

        var saveDocumentUrl = CurrentPath + "SaveToFile.php?";

        allParams.forEach(function(param){
            saveDocumentUrl += param + "&";
        });
        saveDocumentUrl =saveDocumentUrl.substring(0, saveDocumentUrl.length - 1);
        var strActionPage = saveDocumentUrl;
        // var strActionPage = CurrentPath + "SaveToFile.php";
        DWObject.IfSSL = false; // Set whether SSL is used
        DWObject.HTTPPort = location.port == "" ? 80 : location.port;

        var Digital = new Date();
        var uploadfilename = Digital.getMilliseconds(); // Uses milliseconds according to local time as the file name

        // Upload the image(s) to the server asynchronously
        if (document.getElementById("imgTypejpeg").checked == true) {
            DWObject.HTTPUploadThroughPost(strHTTPServer, DWObject.CurrentImageIndexInBuffer, strActionPage, uploadfilename + ".jpg", OnHttpUploadSuccess, OnHttpServerReturnedSomething);
        }
        //remove tiff type
        /*else if (document.getElementById("imgTypetiff").checked == true) {
         DWObject.HTTPUploadAllThroughPostAsMultiPageTIFF(strHTTPServer, strActionPage, uploadfilename + ".tif", OnHttpUploadSuccess, OnHttpServerReturnedSomething);
         }*/
        else if (document.getElementById("imgTypepdf").checked == true) {
            DWObject.HTTPUploadAllThroughPostAsPDF(strHTTPServer, strActionPage, uploadfilename + ".pdf", OnHttpUploadSuccess, OnHttpServerReturnedSomething);
        }
    }
}

function OnHttpUploadSuccess(sHttpResponse) {
    console.log(sHttpResponse);
    window.opener.closeAndRefresh();
}

function OnHttpServerReturnedSomething(errorCode, errorString, sHttpResponse) {
    var textFromServer = sHttpResponse;
    if(textFromServer.indexOf('DWTBarcodeUploadSuccess') != -1)
    {
        var url = 'http://' + location.hostname + ':' + location.port + CurrentPath + 'UploadedImages/' + textFromServer.substr(24);
        document.getElementById('uploadedFile').innerHTML = "Uploaded File: <a href='" + url + "' target='_blank'>" + textFromServer.substr(24) + "</a>";
    }
}