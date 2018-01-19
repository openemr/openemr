 /* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Remesh Babu S <remesh@zhservices.com>
* +------------------------------------------------------------------------------+
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


