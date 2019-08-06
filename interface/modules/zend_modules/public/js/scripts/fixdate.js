/**
 * interface/modules/zend_modules/public/js/scripts/fixdate.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Hima Kumar <himak@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function getDateFormat(date,id) {
  var seperator = date.match(/[^1234567890]/);
  var input_date_arr = date.split(seperator[0]);
  var dateobj = new Date();
  currentyear = dateobj.getFullYear();
  if (input_date_arr[0] > 99) {
    input_date_arr = input_date_arr[0] + seperator[0] + input_date_arr[1] + seperator[0] + input_date_arr[2];
  }
  else {
    if (input_date_arr[0] != 0 || input_date_arr[1] != 0 || input_date_arr[2] != 0) {
      if (input_date_arr[2] < 1000)
        input_date_arr[2] = parseInt(input_date_arr[2]) + parseInt(1900);
      if (input_date_arr[2] < (currentyear - 96)) //Entered 2 digit year greater than current year+3 will be preceded by 20, otherwise 19 is used
        input_date_arr[2] = parseInt(input_date_arr[2]) + parseInt(100);

      input_date_arr = input_date_arr[2] + seperator[0] + input_date_arr[0] + seperator[0] + input_date_arr[1];
    }
  }
  $("#" + id).val(input_date_arr);
}