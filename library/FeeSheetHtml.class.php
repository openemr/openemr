<?php

/**
 * library/FeeSheetHtml.class.php
 *
 * Class for HTML-specific implementations of the Fee Sheet.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/FeeSheet.class.php");
require_once(dirname(__FILE__) . "/api.inc");

class FeeSheetHtml extends FeeSheet
{

  // Dynamically generated JavaScript to maintain justification codes.
    public $justinit = "var f = document.forms[0];\n";

    function __construct($pid = 0, $encounter = 0)
    {
        parent::__construct($pid, $encounter);
    }

  // Build a drop-down list of providers.  This includes users who
  // have the word "provider" anywhere in their "additional info"
  // field, so that we can define providers (for billing purposes)
  // who do not appear in the calendar.
  //
    public static function genProviderOptionList($toptext, $default = 0)
    {
        $s = '';
        // Get user's default facility, or 0 if none.
        $drow = sqlQuery("SELECT facility_id FROM users where username = ?", [$_SESSION['authUser']]);
        $def_facility = 0 + $drow['facility_id'];
        //
        $sqlarr = array($def_facility);
        $query = "SELECT id, lname, fname, facility_id FROM users WHERE " .
        "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
        "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' )";
        // If restricting to providers matching user facility...
        if (!empty($GLOBALS['gbl_restrict_provider_facility'])) {
            $query .= " AND ( facility_id = 0 OR facility_id = ? )";
            $query .= " ORDER BY lname, fname";
        } else { // If not restricting then sort the matching providers first.
            $query .= " ORDER BY (facility_id = ?) DESC, lname, fname";
        }

        $res = sqlStatement($query, $sqlarr);
        $s .= "<option value=''>" . text($toptext) . "</option>";
        while ($row = sqlFetchArray($res)) {
            $provid = $row['id'];
            $s .= "<option value='" . attr($provid) . "'";
            if ($provid == $default) {
                $s .= " selected";
            }

            $s .= ">";
            if (empty($GLOBALS['gbl_restrict_provider_facility']) && $def_facility && ($row['facility_id'] == $def_facility)) {
                // Mark providers in the matching facility with an asterisk.
                $s .= "* ";
            }

            $s .= text($row['lname'] . ", " . $row['fname']) . "</option>";
        }

        return $s;
    }

  // Does the above but including <select> ... </select>.
  //
    public static function genProviderSelect($tagname, $toptext, $default = 0, $disabled = false)
    {
        $s = "   <span class='form-inline'><select class='form-control' name='" . attr($tagname) . "'";
        if ($disabled) {
            $s .= " disabled";
        }

        $s .= ">";
        $s .= self::genProviderOptionList($toptext, $default);
        $s .= "</select></span>\n";
        return $s;
    }

  // Build a drop-down list of warehouses.
  //
    public function genWarehouseSelect($tagname, $toptext, $default = '', $disabled = false, $drug_id = 0, $is_sold = 0)
    {
        $s = '';
        if ($this->got_warehouses) {
            // Normally would use generate_select_list() but it's not flexible enough here.
            $s .= "<span class='form-inline'><select class='form-control' name='" . attr($tagname) . "'";
            if (!$disabled) {
                $s .= " onchange='warehouse_changed(this);'";
            }

            if ($disabled) {
                $s .= " disabled";
            }

            $s .= ">";
            $s .= "<option value=''>" . text($toptext) . "</option>";
            $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = 'warehouse' AND activity = 1 ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres)) {
                  $s .= "<option value='" . attr($lrow['option_id']) . "'";
                if ($disabled) {
                    if ($lrow['option_id'] == $default) {
                        $s .= " selected";
                    }
                } else {
                    $has_inventory = sellDrug($drug_id, 1, 0, 0, 0, 0, '', '', $lrow['option_id'], true);
                    if (
                        ((strlen($default) == 0 && $lrow['is_default']) ||
                        (strlen($default)  > 0 && $lrow['option_id'] == $default)) &&
                        ($is_sold || $has_inventory)
                    ) {
                        $s .= " selected";
                    } else {
                        // Disable this warehouse option if not selected and has no inventory.
                        if (!$has_inventory) {
                            $s .= " disabled";
                        }
                    }
                }

                    $s .= ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
            }

            $s .= "</select></span>";
        }

        return $s;
    }

  // Build a drop-down list of price levels.
  // Includes the specified item's price in the "id" of each option.
  //
    public function genPriceLevelSelect($tagname, $toptext, $pr_id, $pr_selector = '', $default = '', $disabled = false)
    {
        // echo "<!-- pr_id = '$pr_id', pr_selector = '$pr_selector' -->\n"; // debugging
        $s = "<span class='form-inline'><select class='form-control' name='" . attr($tagname) . "'";
        if (!$disabled) {
            $s .= " onchange='pricelevel_changed(this);'";
        }

        if ($disabled) {
            $s .= " disabled";
        }

        $s .= ">";
        $s .= "<option value=''>" . text($toptext) . "</option>";
        $lres = sqlStatement(
            "SELECT lo.*, p.pr_price " .
            "FROM list_options AS lo " .
            "LEFT JOIN prices AS p ON p.pr_id = ? AND p.pr_selector = ? AND p.pr_level = lo.option_id " .
            "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq, lo.title",
            array($pr_id, $pr_selector)
        );
        $standardPrice = 0;
        while ($lrow = sqlFetchArray($lres)) {
            $price = empty($lrow['pr_price']) ? 0 : $lrow['pr_price'];

            // if percent-based pricing is enabled...
            if ($GLOBALS['enable_percent_pricing']) {
                // Set standardPrice as the first price level (sorted by seq)
                if ($standardPrice === 0) {
                    $standardPrice = $price;
                }

                // If price level notes contains a percentage,
                // calculate price as percentage of standard price
                $notes = $lrow['notes'];
                if (!empty($notes) && strpos($notes, '%') > -1) {
                    $percent = intval(str_replace('%', '', $notes));
                    if ($percent > 0) {
                        $price = $standardPrice * ((100 - $percent) / 100);
                    }
                }
            }

            $s .= "<option value='" . attr($lrow['option_id']) . "'";
            $s .= " id='prc_$price'";
            if (
                (strlen($default) == 0 && $lrow['is_default'] && !$disabled) ||
                (strlen($default)  > 0 && $lrow['option_id'] == $default)
            ) {
                $s .= " selected";
            }

            $s .= ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
        }

        $s .= "</select></span>";
        return $s;
    }

  // If Contraception forms can be auto-created by the Fee Sheet we might need
  // to ask about the client's prior contraceptive use.
  //
    public function generateContraceptionSelector($tagname = 'newmauser')
    {
        $s = '';
        if ($GLOBALS['gbl_new_acceptor_policy'] == '1') {
            $csrow = sqlQuery(
                "SELECT COUNT(*) AS count FROM forms AS f WHERE " .
                "f.pid = ? AND f.encounter = ? AND " .
                "f.formdir = 'LBFccicon' AND f.deleted = 0",
                array($this->pid, $this->encounter)
            );
            // Do it only if a contraception form does not already exist for this visit.
            // Otherwise assume that whoever created it knows what they were doing.
            if ($csrow['count'] == 0) {
                  // Determine if this client ever started contraception with the MA.
                  // Even if only a method change, we assume they have.
                  $query = "SELECT f.form_id FROM forms AS f " .
                    "JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
                    "WHERE f.formdir = 'LBFccicon' AND f.deleted = 0 AND f.pid = ? " .
                    "ORDER BY fe.date DESC LIMIT 1";
                  $csrow = sqlQuery($query, array($this->pid));
                if (empty($csrow)) {
                    $s .= "<span class='form-inline'><select class='form-control' name='$tagname'>\n";
                    $s .= " <option value='2'>" . xlt('First Modern Contraceptive Use (Lifetime)') . "</option>\n";
                    $s .= " <option value='1'>" . xlt('First Modern Contraception at this Clinic (with Prior Contraceptive Use)') . "</option>\n";
                    $s .= " <option value='0'>" . xlt('Method Change at this Clinic') . "</option>\n";
                    $s .= "</select></span>\n";
                }
            }
        }

        return $s;
    }

  // Generate a price level drop-down defaulting to the patient's current price level.
  //
    public function generatePriceLevelSelector($tagname = 'pricelevel', $disabled = false)
    {
        $s = "<span class='form-inline'><select class='form-control' name='" . attr($tagname) . "'";
        if ($disabled) {
            $s .= " disabled";
        }

        $s .= ">";
        $pricelevel = $this->getPriceLevel();
        $plres = sqlStatement("SELECT option_id, title FROM list_options " .
        "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq");
        while ($plrow = sqlFetchArray($plres)) {
            $key = $plrow['option_id'];
            $val = $plrow['title'];
            $s .= "<option value='" . attr($key) . "'";
            if ($key == $pricelevel) {
                $s .= ' selected';
            }

            $s .= ">" . text(xl_list_label($val)) . "</option>";
        }

        $s .= "</select></span>";
        return $s;
    }

  // Return Javascript that defines a function to validate the line items.
  // Most of this is currently IPPF-specific, but NDC codes are also validated.
  // This also computes and sets the form's ippfconmeth value if appropriate.
  // This does not validate form fields not related to or derived from line items.
  // Do not call this javascript function if you are just refreshing the form.
  // The arguments are the names of the form arrays for services and products.
  //
    public function jsLineItemValidation($bill = 'bill', $prod = 'prod')
    {
        $s = "
function jsLineItemValidation(f) {
 var max_contra_cyp = 0;
 var max_contra_code = '';
 var required_code_count = 0;
 // Loop thru the services.
 for (var lino = 0; f['{$bill}['+lino+'][code_type]']; ++lino) {
  var pfx = '{$bill}[' + lino + ']';
  if (f[pfx + '[del]'] && f[pfx + '[del]'].checked) continue;
  if (f[pfx + '[ndcnum]'] && f[pfx + '[ndcnum]'].value) {
   // Check NDC number format.
   var ndcok = true;
   var ndc = f[pfx + '[ndcnum]'].value;
   var a = ndc.split('-');
   if (a.length != 3) {
    ndcok = false;
   }
   else if (a[0].length < 1 || a[1].length < 1 || a[2].length < 1 ||
    a[0].length > 5 || a[1].length > 4 || a[2].length > 2) {
    ndcok = false;
   }
   else {
    for (var i = 0; i < 3; ++i) {
     for (var j = 0; j < a[i].length; ++j) {
      var c = a[i].charAt(j);
      if (c < '0' || c > '9') ndcok = false;
     }
    }
   }
   if (!ndcok) {
    alert('" . xls('Format incorrect for NDC') . "\"' + ndc +
     '\", " . xls('should be like nnnnn-nnnn-nn') . "');
    if (f[pfx+'[ndcnum]'].focus) f[pfx+'[ndcnum]'].focus();
    return false;
   }
   // Check for valid quantity.
   var qty = f[pfx+'[ndcqty]'].value - 0;
   if (isNaN(qty) || qty <= 0) {
    alert('" . xls('Quantity for NDC') . " \"' + ndc +
     '\" " . xls('is not valid (decimal fractions are OK).') . "');
    if (f[pfx+'[ndcqty]'].focus) f[pfx+'[ndcqty]'].focus();
    return false;
   }
  }
  if (f[pfx+'[method]'] && f[pfx+'[method]'].value) {
   // The following applies to contraception for family planning clinics.
   var tmp_cyp = parseFloat(f[pfx+'[cyp]'].value);
   var tmp_meth = f[pfx+'[method]'].value;
   var tmp_methtype = parseInt(f[pfx+'[methtype]'].value);
   if (tmp_cyp > max_contra_cyp && tmp_methtype == 2) {
    // max_contra_* tracks max cyp for initial consults only.
    max_contra_cyp = tmp_cyp;
    max_contra_code = tmp_meth;
   }
";
        if ($this->patient_male) {
            $s .= "
   var male_compatible_method = (
    // TBD: Fix hard coded dependency on IPPFCM codes here.
    tmp_meth == '4450' || // male condoms
    tmp_meth == '4570');  // male vasectomy
   if (!male_compatible_method) {
    if (!confirm('" . xls('Warning: Contraceptive method is not compatible with a male patient.') . "'))
     return false;
   }
";
        } // end if male patient
        if ($this->patient_age < 10 || $this->patient_age > 50) {
            $s .= "
   if (!confirm('" . xls('Warning: Contraception for a patient under 10 or over 50.') . "'))
    return false;
";
        } // end if improper age
        if ($this->match_services_to_products) {
            $s .= "
   // Nonsurgical methods should normally include a corresponding product.
   // This takes advantage of the fact that only nonsurgical methods have CYP
   // less than 10, in both the old and new frameworks.
   if (tmp_cyp < 10.0) {
   // Was: if (tmp_meth.substring(0, 2) != '12') {
    var got_prod = false;
    for (var plino = 0; f['{$prod}['+plino+'][drug_id]']; ++plino) {
     var ppfx = '{$prod}[' + plino + ']';
     if (f[ppfx+'[del]'] && f[ppfx+'[del]'].checked) continue;
     if (f[ppfx+'[method]'] && f[ppfx+'[method]'].value) {
      if (f[ppfx+'[method]'].value == tmp_meth) got_prod = true;
     }
    }
    if (!got_prod) {
     if (!confirm('" . xls('Warning: There is no product matching the contraceptive service.') . "'))
      return false;
    }
   }
";
        } // end match services to products
        $s .= "
  }
  ++required_code_count;
 }
";
        if ($this->match_services_to_products) {
            $s .= "
 // The following applies to contraception for family planning clinics.
 // Loop thru the products.
 for (var lino = 0; f['{$prod}['+lino+'][drug_id]']; ++lino) {
  var pfx = '{$prod}[' + lino + ']';
  if (f[pfx + '[del]'] && f[pfx + '[del]'].checked) continue;
  if (f[pfx + '[method]'] && f[pfx + '[method]'].value) {
   var tmp_meth = f[pfx + '[method]'].value;
   // Contraceptive products should normally include a corresponding method.
   var got_svc = false;
   for (var slino = 0; f['{$bill}[' + slino + '][code_type]']; ++slino) {
    var spfx = '{$bill}[' + slino + ']';
    if (f[spfx + '[del]'] && f[spfx + '[del]'].checked) continue;
    if (f[spfx + '[method]'] && f[spfx + '[method]'].value) {
     if (f[spfx + '[method]'].value == tmp_meth) got_svc = true;
    }
   }
   if (!got_svc) {
    if (!confirm('" . xls('Warning: There is no service matching the contraceptive product.') . "'))
     return false;
   }
  }
  ++required_code_count;
 }
";
        } // end match services to products
        if (isset($GLOBALS['code_types']['MA'])) {
            $s .= "
 if (required_code_count == 0) {
  if (!confirm('" . xls('You have not entered any clinical services or products. Click Cancel to add them. Or click OK if you want to save as-is.') . "')) {
   return false;
  }
 }
";
        }

        $s .= "
 // End contraception validation.
 if (f.ippfconmeth) {
  // Save the primary contraceptive method to its hidden form field.
  f.ippfconmeth.value = max_contra_code;
 }
 return true;
}
";
        return $s;
    }
}
