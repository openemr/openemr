/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
*    @author  Hima Kumar <himak@zhservices.com>
* +------------------------------------------------------------------------------+
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