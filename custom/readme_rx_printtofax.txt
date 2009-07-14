MI2 Print-to-Fax Option with signature and addendum/footer options

Changes:
- Added Print to Fax for Prescription Drug (Rx) forms
- Added configurable Prescription Drug form addendum

* This is to allow clinic that use local print to FAX (versus server based) to have a Rx Form that will include the
provider signature and other information that would normally be handled by hand.  The inherent in-security of this
feature must be explained and managed at the clinic location and at the clinic's risk.

* The follow text is printed on all print-to-faxes as addes security
	"Please do not accept this prescription unless it was received via facimile."
 
Manual steps:
- edit includes/config.php and make sure the following are set:

$GLOBALS['oer_config']['prescriptions']['sig_pic'] = "dr_signature.png";
$GLOBALS['oer_config']['prescriptions']['use_signature'] = true;
$GLOBALS['oer_config']['prescriptions']['addendum_file'] = dirname(__FILE__) .
  "/../custom/rx_addendum.txt";

"dr_signature.png" must exist in ./interface/pic/ to have the contents of rx_addendum.txt appear. Note that the pre-existing ability to
add {userid} somewhere in the signature image filename still works. 

You may edit custom/rx_addendum.txt to be any additional addendum text. 
