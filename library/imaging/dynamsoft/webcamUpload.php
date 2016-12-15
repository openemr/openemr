<?php
require_once (dirname(__FILE__) . '/../../../interface/globals.php');
?>

<html>
<head>
	<style>
		body {
			background: #f1f1f1;
		}
        #main{

        }
		#content {
			width: 705px;
			/*height: 710px;*/
			padding: 50px 40px 40px;
			margin: 0 25px;
			border: solid 1px #ccc;
			background: #fff;
            position: absolute;
		}

		#group1 {
			height: 40px;
			margin-bottom: 45px;
		}

		#group2 {
			height: 40px;
            margin: 20px;
            text-align: center;
		}

		#source {
			height: 40px;
			width: 310px;
			outline: none;
			border-radius: 3px;
			font-size: 18px;
			padding-left: 5px;
		}

		input.btn {
			width: 60px;
			height: 40px;
			margin-left: 18px;
			background: #f8f8f8;
			border: solid 1px #ccc;
			border-radius: 3px;
			outline: none;
			cursor: pointer;
		}

		input.upload {
			width: 80px;
		}

		#group2 label {
			margin: 10px 25px 0 0;
		}

		#group2 input[type='radio'] {
			height: 18px;
			width: 18px;
			vertical-align: sub;
		}
		.controlContainer {

			border: solid 1px #ccc;
			border-radius: 3px;
			margin-top: 20px;
			outline: none;
			padding: 3px 0 0 5px;
			font-size: 16px;
		}
		.fl {
			float: left;
		}

		.fr {
			float: right;
		}
	</style>

    <title>Use Dynamic Web TWAIN to Upload</title>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-1-9-1/index.js"></script>
    <script type="text/javascript" src="Resources/dynamsoft.webtwain.initiate.js"></script>
    <script type="text/javascript" src="Resources/dynamsoft.webtwain.config.js"> </script>
    <script type="text/javascript" src="Resources/addon/dynamsoft.webtwain.addon.webcam.js"> </script>
</head>
<body>
    <div id="wrapper">
        <div class="container">
            <div id="main">
                <div id="content">
                    <form>
                        <div id="group1">

                            <select id="source" class="fl"></select>
                            <label><input type="checkbox" id="ShowUI"><?php echo xlt('Show Video Stream')?></label>
                            <input type="button" value="<?php echo xlt('Capture')?>" onclick="CaptureImage();" />
                        </div>
                        <br />
                        <div id="group2">
                            <input class="btn upload fr" type="button" value="<?php echo xlt('Upload')?>" onclick="UploadImage();" />
                            <label class="fr uploadType" for="imgTypejpeg">
                                <input type="radio" value="jpg" name="ImageType" id="imgTypejpeg" checked="checked" />JPEG
                            </label>
                            <label class="fr uploadType" for="imgTypepdf">
                                <input type="radio" value="pdf" name="ImageType" id="imgTypepdf" />PDF
                                <small>(<?php echo xlt('Support multiple scans')?>)</small>
                            </label>
							<div class="fr" style="width:200px;" id="uploadedFile"></div>
                        </div>
						<!-- dwtcontrolContainer is the default div id for Dynamic Web TWAIN control.
                        If you need to rename the id, you should also change the id in the dynamsoft.webtwain.config.js accordingly. -->
                        <div id="dwtcontrolContainer" class="controlContainer" style="float:left;"></div>
                        <div id="dwtcontrolContainerLargeViewer" class="controlContainer" style="float:left;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        Dynamsoft.WebTwainEnv.RegisterEvent('OnWebTwainReady', Dynamsoft_OnReady); // Register OnWebTwainReady event. This event fires as soon as Dynamic Web TWAIN is initialized and ready to be used

        var DWObject, CurrentPath, strHTTPServer;

        var splitUrl = document.URL.split('?');
        var allParams = splitUrl[1].split('&');
        /*var basePath = allParams.pop();
        basePath = basePath.split('=');
        basePath = basePath[1];*/

       // var saveDocumentUrl = basePath + '/openemr/controller.php?';

        function Dynamsoft_OnReady() {
			strHTTPServer = location.hostname;
			var CurrentPathName = unescape(location.pathname);
			CurrentPath = CurrentPathName.substring(0, CurrentPathName.lastIndexOf("/") + 1);
            DWObject = Dynamsoft.WebTwainEnv.GetWebTwain('dwtcontrolContainer'); // Get the Dynamic Web TWAIN object that is embeded in the div with id 'dwtcontrolContainer'
            DWObjectLargeViewer = Dynamsoft.WebTwainEnv.GetWebTwain('dwtcontrolContainerLargeViewer');

            if (DWObject) {

                DWObject.Height = 553;
                DWObject.Width = 693;
                DWObject.SetViewMode(1, 1);
                DWObject.MaxImagesInBuffer = 1;
                $('#dwtcontrolContainerLargeViewer').hide();

				DWObject.RegisterEvent('OnInternetTransferPercentage', function (sPercentage) {
					console.log(sPercentage);
				});
                var count = DWObject.SourceCount; // Populate how many sources are installed in the system
                var arySource = DWObject.Addon.Webcam.GetSourceList();
                for (var i = 0; i < arySource.length; i++)
                    document.getElementById("source").options.add(new Option(arySource[i])); // Get Webcam Source names and put them in a drop-down box

                document.getElementById("imgTypejpeg").checked = true;

                DWObject.RegisterEvent("OnMouseClick", Dynamsoft_OnMouseClick);
                function Dynamsoft_OnMouseClick(index) {
                    /* Copy the image you just clicked on to the clipboard */
                    DWObject.CopyToClipboard(index);
                    /* Load the same image from clipboard into the large viewer */
                    DWObjectLargeViewer.LoadDibFromClipboard();
                }

                $('.uploadType').on("change", function(){
                    if(document.getElementById('imgTypejpeg').checked){
                      DWObject.Height = 553;
                      DWObject.Width = 693;
                      DWObject.SetViewMode(1, 1);
                      DWObject.MaxImagesInBuffer = 1;
                      $('#dwtcontrolContainerLargeViewer').hide();
                    }
                    if(document.getElementById('imgTypepdf').checked){
                        $('#dwtcontrolContainerLargeViewer').show();
                        DWObject.SetViewMode(1, 4);
                        /* This is actually the default setting */
                        DWObjectLargeViewer.SetViewMode(1, 1);
                        //set height and width
                        DWObjectLargeViewer.Width = 500;
                        DWObjectLargeViewer.Height = 700;
                        DWObject.Height = 700;
                        DWObject.Width = 193;
                        /* Set it to hold one image only */
                        DWObjectLargeViewer.MaxImagesInBuffer = 1;
                        DWObject.MaxImagesInBuffer = 4;
                    }
                })
            }
        }

        function CaptureImage() {
            if (DWObject) {

                DWObject.Addon.Webcam.SelectSource(document.getElementById("source").options[document.getElementById("source").selectedIndex].text);

                var showUI = document.getElementById("ShowUI").checked;

                // optional
                var OnCaptureStart = function () {
                }
                // optional
                var OnCaptureSuccess = function () {
                }
                // optional
                var OnCaptureError = function (error, errorstr) {
                    alert(errorstr);
                }
                // optional
                var OnCaptureEnd = function () {
                    DWObject.Addon.Webcam.CloseSource();
                }

                DWObject.Addon.Webcam.CaptureImage(showUI, OnCaptureStart, OnCaptureSuccess, OnCaptureError, OnCaptureEnd);
            }
        }

        //Callback functions for async APIs
        function OnSuccess() {

            if(document.getElementById('imgTypepdf').checked){
                console.log( DWObject.MaxImagesInBuffer)
                DWObject.MaxImagesInBuffer ++
                DWObject.SetViewMode(1, DWObject.MaxImagesInBuffer);
            }

        }

        function OnFailure(errorCode, errorString) {
            alert(errorString);
        }

        function LoadImage() {
            if (DWObject) {
                DWObject.IfShowFileDialog = true; // Open the system's file dialog to load image
                DWObject.LoadImageEx("", EnumDWT_ImageType.IT_ALL, OnSuccess, OnFailure); // Load images in all supported formats (.bmp, .jpg, .tif, .png, .pdf). sFun or fFun will be called after the operation
            }
        }

        <?php require 'upload.js.php';?>
    </script>
</body>
</html>
