/**
 * interface/modules/zend_modules/public/js/application/common.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 /**
  * Function js_xl
  * Message Translation xl format
  *
  * @param {string} msg
  * @returns {undefined}
  */
  function js_xl(msg) {
    var resultTranslated = '';
    var path = window.location;
    var arr = path.toString().split("public");
    var count = arr[1].split("/").length-1;
    var newpath = './';
    for(var i = 0; i < count; i++){
      newpath += '../';
    }
    $.ajax({
      type: 'POST',
      url: newpath + "public/application/index/ajaxZxl",
      async: false,
      data:{
				msg: msg
				},
      success: function(result){
        resultTranslated = result;
      }
    });
    return resultTranslated;
  }


