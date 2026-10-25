<?php
/**
 * Header template for dermatology PDF reports
 */

// Variables expected to be available:
// $facilityName - The facility name
// $visitDescription - The visit description
// $noteDate - The note date
// $providerName - The provider name
// $patientName - The patient name
// $dob - The patient date of birth
// $age - The patient age
// $gender - The patient gender
// $pid - The patient ID
?>
<table style='width: 100%'>
    <tbody>
        <tr>
            <td colspan='2' style='padding-bottom: 2rem'>
              <table style='width: 100%'>
              <tr>
                <td>
                  <img src='<?php echo $GLOBALS['webroot']; ?>/interface/forms/general_dermatology/images/logocomplete.svg' alt='Logo' width='300' height='100'>
                  <p><?php echo $visitDescription; ?></p>
                  <p style='font-size: small'>Service Date: - <?php echo $noteDate; ?></p>
                  <p style='font-size: small'>Provider: Dr. <?php echo $providerName; ?> </p>
                </td>
              <td>
                  <div style='float: right;'>
                      <table style='width: 100%;'>
                          <tbody>
                          <tr>
                                <td style='text-align: right'><h2><?php echo $patientName; ?></h2></td>
                          </tr>
                          <tr>
                            <td style='text-align: right'> <?php echo $dob; ?></td>
                          </tr>
                          <tr>
                              <td style='text-align: right'>Age: <?php echo $age . " | Sex: " . $gender . " | MRN: " . $pid; ?></td>
                          </tr>
                          </tbody>
                      </table>
                  </div>
                </td>
                </tr>
              </table>
            </td>
        </tr>
    </tbody>
</table>
