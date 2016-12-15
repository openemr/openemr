/*!
* Dynamsoft JavaScript Library
/*!
* Dynamsoft WebTwain Addon Webcam JavaScript Intellisense
* Product: Dynamsoft Web Twain Webcam
* Web Site: http://www.dynamsoft.com
*
* Copyright 2016, Dynamsoft Corporation 
* Author: Dynamsoft Support Team
* Version: 11.3
*/

/// -2400 to -2499 is webcam error code
var EnumDWT_ErrorCode = {
    /// All error from directshow sdk
    WCERR_SYSTEM : -2400,
    /// Create ICreateDevEnum interface failed.
    WCERR_FAIL_ICREATEDEVENUM: -2401,
    /// Create IEnumMoniker interface failed.
    WCERR_FAIL_IENUMMONIKER: -2402,
    /// The camera doesn't support IAMVideoProcAmp interface.
    WCERR_NOT_IAMVIDEOPROPERTY: -2403,
    /// The camera doesn't support IAMCameraControl interface.
    WCERR_NOT_IAMCAMERACONTROL: -2404,
    /// The property doesn't support auto capability.
    WCERR_NOT_AUTOPROPERTY: -2405,
    /// No webcam device is found.
    WCERR_NO_DEVICE: -2406,
    /// Could not get video window interface
    WCERR_FAIL_VIDEOWINDOW: -2407,
    /// Could not create filter graph.
    WCERR_FAIL_FILTERGRAPH: -2408,
    /// Could not create SampleGrabber (isqedit.all registered?).
    WCERR_FAIL_SAMPLEGRABBER: -2409,
    /// Unable to make NULL renderer
    WCERR_NULLRENDER: -2410,
    /// Can't add the filter to graph
    WCERR_FAIL_ADDFILTER: -2411,
    /// Can't build the graph
    WCERR_FAIL_BUILDGRAPH: -2412,
    /// Failed to register filter graph with ROT.
    WCERR_FAIL_REGFILTERGRAPH: -2413,
    /// Time out
    WCERR_GRAB_TIMEOUT : -2414
};

/// Specifies the video rotate mode on a video capture device.
var EnumDWT_VideoRotateMode = {
	/// Don't rotate
	VRM_NONE					: 0,
	/// 90 deg Clockwise
	VRM_90_DEGREES_CLOCKWISE	: 1,
	/// 180 deg Clockwise
	VRM_180_DEGREES_CLOCKWISE	: 2,
	/// 270 deg Clockwise
	VRM_270_DEGREES_CLOCKWISE	: 3,
	/// Flip
	VRM_FLIP_VERTICAL			: 4,
	/// Mirror
	VRM_FLIP_HORIZONTAL			: 5
};

/// Specifies video properties on a video capture device.
var EnumDWT_VideoProperty = {
    /// Specifies the brightness, also called the black level. For NTSC, the value is expressed in IRE units * 100. 
    /// For non-NTSC sources, the units are arbitrary, with zero representing blanking and 10,000 representing pure white. 
    /// Values range from -10,000 to 10,000.
    VP_BRIGHTNESS : 0,
    /// Specifies the contrast, expressed as gain factor * 100. Values range from zero to 10,000.
    VP_CONTRAST : 1,
    /// Specifies the hue, in degrees * 100. Values range from -180,000 to 180,000 (-180 to +180 degrees).
    VP_HUE : 2,
    /// Specifies the saturation. Values range from 0 to 10,000.
    VP_SATURATION : 3,
    /// Specifies the sharpness. Values range from 0 to 100.
    VP_SHARPNESS : 4,
    /// Specifies the gamma, as gamma * 100. Values range from 1 to 500.
    VP_GAMMA : 5,
    /// Specifies the color enable setting. The possible values are 0 (off) and 1 (on).
    VP_COLORENABLE : 6,
    /// Specifies the white balance, as a color temperature in degrees Kelvin. The range of values depends on the device.
    VP_WHITEBALANCE : 7,
    /// Specifies the backlight compensation setting. Possible values are 0 (off) and 1 (on).
    VP_BACKLIGHTCOMPENSATION : 8,
    /// Specifies the gain adjustment. Zero is normal. Positive values are brighter and negative values are darker. 
    /// The range of values depends on the device.
    VP_GAIN : 9
};

/// Specifies a setting on a camera.
var EnumDWT_CameraControlProperty = { 
    /// Specifies the camera's pan setting, in degrees. Values range from -180 to +180, with the default set to zero.
    /// Positive values are clockwise from the origin (the camera rotates clockwise when viewed from above), 
    /// and negative values are counterclockwise from the origin.
    CCP_PAN : 0,
    /// Specifies the camera's tilt setting, in degrees. Values range from -180 to +180, with the default set to zero.
    /// Positive values point the imaging plane up, and negative values point the imaging plane down.
    CCP_TILT : 1,
    /// Specifies the camera's roll setting, in degrees. Values range from -180 to +180, with the default set to zero. 
    /// Positive values cause a clockwise rotation of the camera along the image-viewing axis, and negative values cause a counterclockwise rotation of the camera.
    CCP_ROLL : 2,
    /// Specifies the camera's zoom setting, in millimeters. Values range from 10 to 600, and the default is specific to the device.
    CCP_ZOOM : 3,
    /// Specifies the exposure setting, in log base 2 seconds. In other words, for values less than zero, the exposure time is 1/2^n seconds, 
    /// and for values zero or above, the exposure time is 2^n seconds. For example:
    /// Value	Seconds
    /// -3	1/8
    /// -2	1/4
    /// -1	1/2
    /// 0	1
    /// 1	2
    /// 2	4
    CCP_EXPOSURE : 4,
    /// Specifies the camera's iris setting, in units of fstop* 10.
    CCP_IRIS : 5,
    /// Specifies the camera's focus setting, as the distance to the optimally focused target, in millimeters. 
    /// The range and default value are specific to the device. 
    CCP_FOCUS : 6
};

var Webcam = {};
WebTwainAddon.Webcam = Webcam;

Webcam.Download = function(remoteFile, optionalAsyncSuccessFunc, optionalAsyncFailureFunc) {
    /// <summary> Download and install webcam add-on on the local system. </summary>
    /// <param name="remoteFile" type="string">specifies the value of which frame to get. </param>
    /// <param name="optionalAsyncSuccessFunc" type="function">optional. The function to call when the download succeeds. Please refer to the function prototype OnSuccess.</param>
    /// <param name="optionalAsyncFailureFunc" type="function">optional. The function to call when the download fails. Please refer to the function prototype OnFailure.</param>
    /// <returns type="bool"></returns>   
};

Webcam.GetSourceList = function() {
    /// <summary> Return supported webcam source names. </summary>
   /// <returns type="string array"></returns>  
};

Webcam.SelectSource = function(strSourceName) {
    /// <summary> Select the source with the specified name. </summary>
    /// <param name="strSourceName" type="string">The source name.</param>
    /// <returns type="bool"></returns>   
};

Webcam.CloseSource = function() {
    /// <summary> Close the selected source and release the webcam. </summary>
    /// <returns type="bool"></returns>   
};

Webcam.CaptureImage = function(bIfShowUI, optionalOnCaptureStartFunc, optionalOnCaptureSuccessFunc, optionalOnCaptureErrorFunc, optionalOnCaptureEndFunc) {
    /// <summary> Capture image from the specified webcam. </summary>
    /// <param name="bIfShowUI" type="bool">Capture image with ui.</param>
    /// <param name="optionalOnCaptureStartFunc" type="function">optional. The function to call when the capture starts. Please refer to the function prototype OnCaptureStart.</param>
    /// <param name="optionalOnCaptureSuccessFunc" type="function">optional. The function to call when the capture succeeds. Please refer to the function prototype OnCaptureSuccess.</param>
    /// <param name="optionalOnCaptureErrorFunc" type="function">optional. The function to call when the capture fails. Please refer to the function prototype OnCaptureError.</param>
    /// <param name="optionalOnCaptureEndFunc" type="function">optional. The function to call when the capture finished. Please refer to the function prototype OnCaptureEnd.</param>
    /// <returns type="void"></returns> 
};

Webcam.GetMediaType = function() {
    /// <summary> Returns the media type for a camera. </summary>
    /// <returns type="class MediaType"></returns>   
};

Webcam.GetResolution = function() {
    /// <summary> Returns the count in the media type list. </summary>
    /// <returns type="class Resolution"></returns>   
};

Webcam.GetFrameRate = function() {
    /// <summary> Returns the frame rate for a camera. </summary>
    /// <returns type="class FrameRate"></returns>   
};

Webcam.SetMediaType = function(value) {
    /// <summary> Set the media type of the current selected source by the value. </summary>
    /// <param name="value" type="string">The new media type value.</param>
    /// <returns type="bool"></returns>   
};

Webcam.SetResolution = function(value) {
    /// <summary> Set the resolution of the current camera source. </summary>
    /// <param name="value" type="string">The new resolution value.</param>
    /// <returns type="bool"></returns>   
};

Webcam.SetFrameRate = function(value) {
    /// <summary> Set current frame rate. </summary>
    /// <param name="value" type="int">The new frame rate value.</param>
    /// <returns type="bool"></returns>   
};

Webcam.GetVideoPropertySetting = function(property) {
    /// <summary> Gets the current setting of a video property. </summary>
    /// <param name="property" type="EnumDWT_VideoProperty">The property.</param>
    /// <returns type="class VideoPropertySetting"></returns>   
};

Webcam.GetVideoPropertyMoreSetting = function(property) {
    /// <summary> Gets the range and default value of a specified video property. </summary>
    /// <param name="property" type="EnumDWT_VideoProperty">The property.</param>
    /// <returns type="class VideoPropertyMoreSetting"></returns>   
};

Webcam.GetCameraControlPropertySetting = function (property) {
    /// <summary> Gets the current setting of a camera property. </summary>
    /// <param name="property" type="EnumDWT_CameraControlProperty">The property.</param>
    /// <returns type="class CameraControlPropertySetting"></returns>   
};

Webcam.GetCameraControlPropertyMoreSetting = function (property) {
    /// <summary> Gets the range and default value of a specified camera property. </summary>
    /// <param name="property" type="EnumDWT_CameraControlProperty">The property.</param>
    /// <returns type="class CameraControlPropertyMoreSetting"></returns>   
};

Webcam.SetVideoPropertySetting = function(property, value, auto) {
    /// <summary> Sets video quality for a specified property. </summary>
    /// <param name="property" type="EnumDWT_VideoProperty">The property.</param>
    /// <param name="value" type="int">The new value of the property.</param>
    /// <param name="auto" type="bool">The desired control setting, whether the setting is controlled manually or automatically.</param>
    /// <returns type="bool"></returns>   
};

Webcam.SetVideoRotateMode = function(enumAngle) {
    /// <summary> Sets video rotate mode. </summary>
    /// <param name="enumAngle" type="EnumDWT_VideoRotateMode">The rotate angle.</param>
    /// <returns type="bool"></returns>   
};

Webcam.SetCameraControlPropertySetting = function(property, value, auto) {
    /// <summary> Sets a specified property on the camera. </summary>
    /// <param name="property" type="EnumDWT_CameraControlProperty">The property.</param>
    /// <param name="value" type="int">The new value of the property.</param>
    /// <param name="auto" type="bool">The desired control setting, whether the setting is controlled manually or automatically.</param>
    /// <returns type="bool"></returns>   
};

