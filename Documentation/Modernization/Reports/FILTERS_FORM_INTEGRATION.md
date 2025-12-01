# Collections Report Filters Form - Integration Guide

## Overview
The filters form has been converted from inline PHP/HTML to a Twig template (`templates/reports/collections/_filters_form.twig`).

## Integration Steps

### 1. Prepare Data in PHP Controller

In `interface/reports/collections_report.php`, prepare the data to pass to the Twig template:

```php
// ... existing code ...

// Prepare filter data for Twig
$filterData = [
    'form_page_y' => $form_page_y,
    'form_offset_y' => $form_offset_y,
    'form_y' => $form_y,
    'form_cb_ssn' => $form_cb_ssn,
    'form_cb_dob' => $form_cb_dob,
    'form_cb_pubpid' => $form_cb_pubpid,
    'form_cb_policy' => $form_cb_policy,
    'form_cb_phone' => $form_cb_phone,
    'form_cb_city' => $form_cb_city,
    'form_cb_ins1' => $form_cb_ins1,
    'form_cb_referrer' => $form_cb_referrer,
    'form_cb_adate' => $form_cb_adate,
    'form_cb_idays' => $form_cb_idays,
    'form_cb_err' => $form_cb_err,
    'form_cb_group_number' => $form_cb_group_number,
    'form_date' => oeFormatShortDate($form_date),
    'form_to_date' => oeFormatShortDate($form_to_date),
    'form_category' => $form_category,
    'form_ageby' => $_POST['form_ageby'] ?? 'Service Date',
    'form_age_cols' => $form_age_cols ?: 3,
    'form_age_inc' => $form_age_inc ?: 30,
    'form_cb_with_debt' => $form_cb_with_debt,
];

// Generate dropdowns
ob_start();
dropdown_facility($form_facility, 'form_facility', false);
$facilityDropdown = ob_get_clean();

ob_start();
insuranceSelect();
$insuranceDropdown = ob_get_clean();

// Generate provider dropdown
ob_start();
$query = "SELECT id, lname, fname FROM users WHERE authorized = 1 ORDER BY lname, fname";
$ures = sqlStatement($query);
echo "<select name='form_provider' id='form_provider' class='form-control'>\n";
echo "<option value=''>-- " . xlt('All') . " --</option>\n";
while ($urow = sqlFetchArray($ures)) {
    $provid = $urow['id'];
    echo "<option value='" . attr($provid) . "'";
    if ($provid == $form_provider) {
        echo " selected";
    }
    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "</option>\n";
}
echo "</select>\n";
$providerDropdown = ob_get_clean();

// Prepare template variables
$templateVars = [
    'csrf_token_form' => CsrfUtils::collectCsrfToken(),
    'filters' => $filterData,
    'facility_dropdown' => $facilityDropdown,
    'insurance_dropdown' => $insuranceDropdown,
    'provider_dropdown' => $providerDropdown,
    'show_results' => !empty($_POST['form_refresh']),
];
```

### 2. Render the Twig Template

Replace the inline HTML form (lines 453-690) with:

```php
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
echo $twig->render('reports/collections/collections_report.twig', $templateVars);
```

### 3. Remove Old HTML

Remove lines 453-690 from the original PHP file (the inline HTML form).

## Current Status

✅ **Completed:**
- Twig template created with all form fields
- Modern Bootstrap 4 styling
- Proper form structure and accessibility
- Documentation of required variables

⏳ **Remaining:**
- Integrate into main PHP controller
- Test form submission and data flow
- Verify all dropdowns render correctly
- Test with different filter combinations

## Testing Checklist

After integration, verify:

- [ ] Form displays correctly on page load
- [ ] All checkboxes have correct default values
- [ ] Date pickers work (datetime-picker library)
- [ ] Facility dropdown populates
- [ ] Insurance/Payor dropdown populates
- [ ] Provider dropdown populates
- [ ] Category dropdown shows correct options
- [ ] Age By dropdown shows correct options
- [ ] Form submission works
- [ ] Form values persist after submission
- [ ] CSRF token validation works
- [ ] Print button appears when results are shown

## Notes

- The template uses Bootstrap 4 classes for modern, responsive layout
- All text is properly escaped using Twig's `|xlt` (translate) and `|attr` filters
- Form maintains backward compatibility with existing form field names
- JavaScript initialization is handled in `collections_report.js`
