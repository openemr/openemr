<?php

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+
?>
<script>
    function checkLength(eleName, eleVal, len) {
        eleName = eleName.replace('form_', '');
        var m = '';
        if (eleVal.length > len)
            m += '<?php echo xl("Invalid length for") . " "; ?>' + eleName.toUpperCase() + '.<?php echo " " . xl("The length should not exceed the following number of characters") . " : "; ?>' + len + "\n";
        return m;
    }

    function checkSpecialCharacter(eleName, eleVal) {
        var regE = /[^a-zA-Z'\s,.\-]/;
        var m = '';
        eleName = eleName.replace('form_', '');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " "?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkFacilityName(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9 '().,#:\/\-@_%]/;
        var m = '';
        eleName = eleName.replace('form_', '');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " "?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkPhone(eleName, eleVal) {
        var regE = /[^0-9']/;
        var m = '';
        eleVal_temp = eleVal.replace(/[^0-9]/ig, '');
        eleVal = eleVal.replace(/[-)(\s]/ig, '');
        eleName = eleName.replace('form_', '');
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid non-numeric character in") . " "?>' + eleName.toUpperCase() + "\n";
        else if (eleVal_temp.length > 10)
            m += eleName.toUpperCase() + '<?php echo " " . xl("should contain only 10 digits") ?>' + "\n";
        else if (eleVal_temp.length < 10)
            m += eleName.toUpperCase() + '<?php echo " " . xl("should contain 10 digits") ?>' + "\n";
        return m;
    }

    function checkTaxNpiDea(eleName, eleVal) {
        var regE = /[^0-9]/;
        var m = '';
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkFederalEin(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9 '().,#:\/\-@_%]/;
        var m = '';
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkStateLicenseNumber(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9 '.,()#:\/\-@_%\r\n]/;
        var m = '';
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkUsername(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9 '().,#:\/\-@_%]/;
        var m = '';
        eleName = eleName.replace('form_', '');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkAlphaNumeric(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9\s]/;
        var m = '';
        eleName = eleName.replace('form_', '');
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

    function checkAlphaNumericExtended(eleName, eleVal) {
        var regE = /[^a-zA-Z0-9 '().,#:\/\-@_%]/;
        var m = '';
        eleName = eleName.replace('form_', '');
        eleName = eleName.replace('_', ' ');
        if (regE.test(eleVal) == true)
            m += '<?php echo xl("Invalid character in") . " " ?>' + eleName.toUpperCase() + "\n";
        return m;
    }

</script>
