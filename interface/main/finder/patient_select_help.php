<?php

require_once("../../globals.php");

use OpenEMR\Core\Header;

?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title>
Help Searching for Patients
</title>
</head>
<body>
<h1>
Help Searching for Patients
</h1>

<h2>
Explanation of columns
</h2>
<p>
The phone column displays the home phone number.  To view additional phone numbers, use your mouse to place the pointer over the phone number and leave it there.  A tooltip will appear showing additional phone numbers if they are in the database.  The columns for counting encounters and dates related to the last encounter are calculated using encounters with billing events (cpt4 entered for the encounter).  This is because some practices may use encounters for unbilled events, such as phone calls.  If a practice does not enter cpt4 codes for encounters or if they are entered on a different date than the actual encounter, these calculations may not be accurate.
</p>

<h2>
How to search for a name.
</h2>
<p>
If you type some text (the search string) in the search box and press enter, last names containing the search string will be returned.  If you begin your search string with a lower case letter, names containing the search string anywhere in the name will be returned.  If you begin the string with a capital letter, names which begin with the search string will be returned.  To search for a string in a first name, use the rules described so far, but place a comma before the search string.  You can also use these same rules to search first and last names by placing a search string before and after the comma.
</p>

<h2>
Advanced - X days from last encounter
</h2>
<p>
If you want to change the number used to calculate a date a particular number of days from the last encounter, place the number in the search box before the search string.  For example, if you want to search for patients with the last name containing 'Smith' and calculate in the last column the date 21 days from their last visit, type 21Smith in the search box.
</p>

<h2>
</h2>
<p>

</p>
</body>
</html>
