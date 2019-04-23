<?php
/**
 * PatientExtendedRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yuriy Gershem <yuriyge@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\PatientService;
use OpenEMR\RestControllers\RestControllerHelper;

include_once ("../../openemr/library/patient.inc");

class PatientExtendedRestController
{
    private $patientService;

    public function __construct($pid)
    {
        $this->patientService = new PatientService();
        $this->patientService->setPid($pid);
    }

    public function post($data)
    {
        $validationResult = $this->patientService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->patientService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $serviceResult), 201);
    }

    public function put($pid, $data)
    {
        $validationResult = $this->patientService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->patientService->update($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array("pid" => $pid), 200);
    }

    public function setGlobalPatientId($pid)
    {
        // Escape $new_pid by forcing it to an integer to protect from sql injection
        $new_pid_int = intval($this->patientService->getPid());
        // Be careful not to clear the encounter unless the pid is really changing.
        if (!isset($_SESSION['pid']) || $pid != $new_pid_int || $pid != $_SESSION['pid']) {
            $_SESSION['encounter'] = $encounter = 0;
        }
        // unset therapy_group session when set session for patient
        if ($_SESSION['pid'] != 0 && isset($_SESSION['therapy_group'])) {
            unset($_SESSION['therapy_group']);
        }

        // Set pid to the escaped pid
        $_SESSION['pid'] = $new_pid_int;

    }

    public function getOne()
    {
        $serviceResult = getPatientData($this->patientService->getPid(),"*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
        $date_of_death = is_patient_deceased($this->patientService->getPid())['date_deceased'];
        $serviceResult['str_dob'] = '';
        if (empty($date_of_death)) {
            $serviceResult['str_dob'] = " " . xl('DOB') . ": " . oeFormatShortDate($serviceResult['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($serviceResult['DOB_YMD']);
        } else {
            $serviceResult['str_dob'] = " " . xl('DOB') . ": " . oeFormatShortDate($serviceResult['DOB_YMD']) . " " . xl('Age at death') . ": " . oeFormatAge($serviceResult['DOB_YMD'], $date_of_death);
        }
        $serviceResult['str_dob'] = stripslashes($serviceResult['str_dob']);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll($search)
    {
        $serviceResult = $this->patientService->getAll(array(
            'fname' => $search['fname'],
            'lname' => $search['lname'],
            'dob' => $search['dob']
        ));

        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
