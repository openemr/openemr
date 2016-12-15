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

/** -2400 to -2499 is webcam error code */
var EnumDWT_ErrorCode = {
    /** All error from directshow sdk */
    WCERR_SYSTEM : -2400,
    /** Create ICreateDevEnum interface failed. */
    WCERR_FAIL_ICREATEDEVENUM: -2401,
    /** Create IEnumMoniker interface failed. */
    WCERR_FAIL_IENUMMONIKER: -2402,
    /** The camera doesn't support IAMVideoProcAmp interface. */
    WCERR_NOT_IAMVIDEOPROPERTY: -2403,
    /** The camera doesn't support IAMCameraControl interface. */
    WCERR_NOT_IAMCAMERACONTROL: -2404,
    /** The property doesn't support auto capability. */
    WCERR_NOT_AUTOPROPERTY: -2405,
    /** No webcam device is found. */
    WCERR_NO_DEVICE: -2406,
    /** Could not get video window interface */
    WCERR_FAIL_VIDEOWINDOW: -2407,
    /** Could not create filter graph. */
    WCERR_FAIL_FILTERGRAPH: -2408,
    /** Could not create SampleGrabber (isqedit.all registered?). */
    WCERR_FAIL_SAMPLEGRABBER: -2409,
    /** Unable to make NULL renderer */
    WCERR_NULLRENDER: -2410,
    /** Can't add the filter to graph */
    WCERR_FAIL_ADDFILTER: -2411,
    /** Can't build the graph */
    WCERR_FAIL_BUILDGRAPH: -2412,
    /** Failed to register filter graph with ROT. */
    WCERR_FAIL_REGFILTERGRAPH: -2413,
    /** Time out */
    WCERR_GRAB_TIMEOUT : -2414
};

/** Specifies the video rotate mode on a video capture device. */
var EnumDWT_VideoRotateMode = {
	/** Don't rotate */
	VRM_NONE					: 0,
	/** 90 deg Clockwise */
	VRM_90_DEGREES_CLOCKWISE	: 1,
	/** 180 deg Clockwise */
	VRM_180_DEGREES_CLOCKWISE	: 2,
	/** 270 deg Clockwise */
	VRM_270_DEGREES_CLOCKWISE	: 3,
	/** Flip */
	VRM_FLIP_VERTICAL			: 4,
	/** Mirror */
	VRM_FLIP_HORIZONTAL			: 5
};

/** Specifies video properties on a video capture device. */
var EnumDWT_VideoProperty = {
    /** Specifies the brightness, also called the black level. For NTSC, the value is expressed in IRE units * 100. 
     *  For non-NTSC sources, the units are arbitrary, with zero representing blanking and 10,000 representing pure white. 
     *  Values range from -10,000 to 10,000.
     */
    VP_BRIGHTNESS : 0,
    /** Specifies the contrast, expressed as gain factor * 100. Values range from zero to 10,000. */
    VP_CONTRAST : 1,
    /** Specifies the hue, in degrees * 100. Values range from -180,000 to 180,000 (-180 to +180 degrees). */
    VP_HUE : 2,
    /** Specifies the saturation. Values range from 0 to 10,000. */
    VP_SATURATION : 3,
    /** Specifies the sharpness. Values range from 0 to 100. */
    VP_SHARPNESS : 4,
    /** Specifies the gamma, as gamma * 100. Values range from 1 to 500. */
    VP_GAMMA : 5,
    /** Specifies the color enable setting. The possible values are 0 (off) and 1 (on). */
    VP_COLORENABLE : 6,
    /** Specifies the white balance, as a color temperature in degrees Kelvin. The range of values depends on the device. */
    VP_WHITEBALANCE : 7,
    /** Specifies the backlight compensation setting. Possible values are 0 (off) and 1 (on). */
    VP_BACKLIGHTCOMPENSATION : 8,
    /** Specifies the gain adjustment. Zero is normal. Positive values are brighter and negative values are darker. 
     *  The range of values depends on the device.
     */
    VP_GAIN : 9
};

/** Specifies a setting on a camera. */
var EnumDWT_CameraControlProperty = { 
    /** Specifies the camera's pan setting, in degrees. Values range from -180 to +180, with the default set to zero.
     *  Positive values are clockwise from the origin (the camera rotates clockwise when viewed from above), 
     *  and negative values are counterclockwise from the origin.
     */
    CCP_PAN : 0,
    /** Specifies the camera's tilt setting, in degrees. Values range from -180 to +180, with the default set to zero.
     *  Positive values point the imaging plane up, and negative values point the imaging plane down.
     */
    CCP_TILT : 1,
    /** Specifies the camera's roll setting, in degrees. Values range from -180 to +180, with the default set to zero. 
     *  Positive values cause a clockwise rotation of the camera along the image-viewing axis, and negative values cause a counterclockwise rotation of the camera.
     */
    CCP_ROLL : 2,
    /** Specifies the camera's zoom setting, in millimeters. Values range from 10 to 600, and the default is specific to the device. */
    CCP_ZOOM : 3,
    /** Specifies the exposure setting, in log base 2 seconds. In other words, for values less than zero, the exposure time is 1/2^n seconds, 
     *  and for values zero or above, the exposure time is 2^n seconds. For example:
     *  Value	Seconds
     *  -3	1/8
     *  -2	1/4
     *  -1	1/2
     *  0	1
     *  1	2
     *  2	4
     */
    CCP_EXPOSURE : 4,
    /** Specifies the camera's iris setting, in units of fstop* 10. */
    CCP_IRIS : 5,
    /** Specifies the camera's focus setting, as the distance to the optimally focused target, in millimeters. 
     *  The range and default value are specific to the device. 
     */
    CCP_FOCUS : 6
};

var Webcam = {};
WebTwainAddon.Webcam = Webcam;

/**
 *  Download and install webcam add-on on the local system. 
 * @method Dynamsoft.WebTwain#Download 
 * @param {string} remoteFile specifies the value of which frame to get. 
 * @param {function} optionalAsyncSuccessFunc optional. The function to call when the download succeeds. Please refer to the function prototype OnSuccess.
 * @param {function} optionalAsyncFailureFunc optional. The function to call when the download fails. Please refer to the function prototype OnFailure.
 * @return {bool}
 */
Webcam.Download = function(remoteFile, optionalAsyncSuccessFunc, optionalAsyncFailureFunc) {
};

/**
 *  Return supported webcam source names. 
 * @method Dynamsoft.WebTwain#GetSourceList 
 * @return {string array}
 */
Webcam.GetSourceList = function() {
};

/**
 *  Select the source with the specified name. 
 * @method Dynamsoft.WebTwain#SelectSource 
 * @param {string} strSourceName The source name.
 * @return {bool}
 */
Webcam.SelectSource = function(strSourceName) {
};

/**
 *  Close the selected source and release the webcam. 
 * @method Dynamsoft.WebTwain#CloseSource 
 * @return {bool}
 */
Webcam.CloseSource = function() {
};

/**
 *  Capture image from the specified webcam. 
 * @method Dynamsoft.WebTwain#CaptureImage 
 * @param {bool} bIfShowUI Capture image with ui.
 * @param {function} optionalOnCaptureStartFunc optional. The function to call when the capture starts. Please refer to the function prototype OnCaptureStart.
 * @param {function} optionalOnCaptureSuccessFunc optional. The function to call when the capture succeeds. Please refer to the function prototype OnCaptureSuccess.
 * @param {function} optionalOnCaptureErrorFunc optional. The function to call when the capture fails. Please refer to the function prototype OnCaptureError.
 * @param {function} optionalOnCaptureEndFunc optional. The function to call when the capture finished. Please refer to the function prototype OnCaptureEnd.
 * @return {void}
 */
Webcam.CaptureImage = function(bIfShowUI, optionalOnCaptureStartFunc, optionalOnCaptureSuccessFunc, optionalOnCaptureErrorFunc, optionalOnCaptureEndFunc) {
};

/**
 *  Returns the media type for a camera. 
 * @method Dynamsoft.WebTwain#GetMediaType 
 * @return {class MediaType}
 */
Webcam.GetMediaType = function() {
};

/**
 *  Returns the count in the media type list. 
 * @method Dynamsoft.WebTwain#GetResolution 
 * @return {class Resolution}
 */
Webcam.GetResolution = function() {
};

/**
 *  Returns the frame rate for a camera. 
 * @method Dynamsoft.WebTwain#GetFrameRate 
 * @return {class FrameRate}
 */
Webcam.GetFrameRate = function() {
};

/**
 *  Set the media type of the current selected source by the value. 
 * @method Dynamsoft.WebTwain#SetMediaType 
 * @param {string} value The new media type value.
 * @return {bool}
 */
Webcam.SetMediaType = function(value) {
};

/**
 *  Set the resolution of the current camera source. 
 * @method Dynamsoft.WebTwain#SetResolution 
 * @param {string} value The new resolution value.
 * @return {bool}
 */
Webcam.SetResolution = function(value) {
};

/**
 *  Set current frame rate. 
 * @method Dynamsoft.WebTwain#SetFrameRate 
 * @param {int} value The new frame rate value.
 * @return {bool}
 */
Webcam.SetFrameRate = function(value) {
};

/**
 *  Gets the current setting of a video property. 
 * @method Dynamsoft.WebTwain#GetVideoPropertySetting 
 * @param {EnumDWT_VideoProperty} property The property.
 * @return {class VideoPropertySetting}
 */
Webcam.GetVideoPropertySetting = function(property) {
};

/**
 *  Gets the range and default value of a specified video property. 
 * @method Dynamsoft.WebTwain#GetVideoPropertyMoreSetting 
 * @param {EnumDWT_VideoProperty} property The property.
 * @return {class VideoPropertyMoreSetting}
 */
Webcam.GetVideoPropertyMoreSetting = function(property) {
};

Webcam.GetCameraControlPropertySetting = function (property) {
/**
 *  Gets the current setting of a camera property. 
 * @method Dynamsoft.WebTwain#GetVideoPropertyMoreSetting 
 * @param {EnumDWT_CameraControlProperty} property The property.
 * @return {class CameraControlPropertySetting}
 */
};

Webcam.GetCameraControlPropertyMoreSetting = function (property) {
/**
 *  Gets the range and default value of a specified camera property. 
 * @method Dynamsoft.WebTwain#GetVideoPropertyMoreSetting 
 * @param {EnumDWT_CameraControlProperty} property The property.
 * @return {class CameraControlPropertyMoreSetting}
 */
};

/**
 *  Sets video quality for a specified property. 
 * @method Dynamsoft.WebTwain#SetVideoPropertySetting 
 * @param {EnumDWT_VideoProperty} property The property.
 * @param {int} value The new value of the property.
 * @param {bool} auto The desired control setting, whether the setting is controlled manually or automatically.
 * @return {bool}
 */
Webcam.SetVideoPropertySetting = function(property, value, auto) {
};

/**
 *  Sets video rotate mode.
 * @method Dynamsoft.WebTwain#SetVideoRotateMode 
 * @param {EnumDWT_VideoRotateMode} enumAngle The rotate angle.
 * @return {bool}
 */
Webcam.SetVideoRotateMode = function(enumAngle) {
};

/**
 *  Sets a specified property on the camera. 
 * @method Dynamsoft.WebTwain#SetCameraControlPropertySetting 
 * @param {EnumDWT_CameraControlProperty} property The property.
 * @param {int} value The new value of the property.
 * @param {bool} auto The desired control setting, whether the setting is controlled manually or automatically.
 * @return {bool}
 */
Webcam.SetCameraControlPropertySetting = function(property, value, auto) {
};


