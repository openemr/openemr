<?php

/**
 * Prescription.class.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Below list of terms are deprecated, but we keep this list
//   to keep track of the official openemr drugs terms and
//   corresponding ID's for reference. Official is referring
//   to the default settings after installing OpenEMR.
//
// define('UNIT_BLANK',0);
// define('UNIT_MG',1);
// define('UNIT_MG_1CC',2);
// define('UNIT_MG_2CC',3);
// define('UNIT_MG_3CC',4);
// define('UNIT_MG_4CC',5);
// define('UNIT_MG_5CC',6);
// define('UNIT_MCG',7);
// define('UNIT_GRAMS',8);
//
// define('INTERVAL_BLANK',0);
// define('INTERVAL_BID',1);
// define('INTERVAL_TID',2);
// define('INTERVAL_QID',3);
// define('INTERVAL_Q_3H',4);
// define('INTERVAL_Q_4H',5);
// define('INTERVAL_Q_5H',6);
// define('INTERVAL_Q_6H',7);
// define('INTERVAL_Q_8H',8);
// define('INTERVAL_QD',9);
// define('INTERVAL_AC',10); // added May 2008
// define('INTERVAL_PC',11); // added May 2008
// define('INTERVAL_AM',12); // added May 2008
// define('INTERVAL_PM',13); // added May 2008
// define('INTERVAL_ANTE',14); // added May 2008
// define('INTERVAL_H',15); // added May 2008
// define('INTERVAL_HS',16); // added May 2008
// define('INTERVAL_PRN',17); // added May 2008
// define('INTERVAL_STAT',18); // added May 2008
//
// define('FORM_BLANK',0);
// define('FORM_SUSPENSION',1);
// define('FORM_TABLET',2);
// define('FORM_CAPSULE',3);
// define('FORM_SOLUTION',4);
// define('FORM_TSP',5);
// define('FORM_ML',6);
// define('FORM_UNITS',7);
// define('FORM_INHILATIONS',8);
// define('FORM_GTTS_DROPS',9);
// define('FORM_CR',10);
// define('FORM_OINT',11);
//
// define('ROUTE_BLANK',0);
// define("ROUTE_PER_ORIS", 1);
// define("ROUTE_PER_RECTUM", 2);
// define("ROUTE_TO_SKIN", 3);
// define("ROUTE_TO_AFFECTED_AREA", 4);
// define("ROUTE_SUBLINGUAL", 5);
// define("ROUTE_OS", 6);
// define("ROUTE_OD", 7);
// define("ROUTE_OU", 8);
// define("ROUTE_SQ", 9);
// define("ROUTE_IM", 10);
// define("ROUTE_IV", 11);
// define("ROUTE_PER_NOSTRIL", 12);
// define("ROUTE_B_EAR", 13);
// define("ROUTE_L_EAR", 14);
// define("ROUTE_R_EAR", 15);
//
// define('SUBSTITUTE_YES',1);
// define('SUBSTITUTE_NO',2);
//


require_once(__DIR__ . "/../lists.inc.php");


/**
 * Prescription model
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ORDataObject\Person;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\Enum\FHIRMedicationIntentEnum;
use OpenEMR\Services\ListService;

class Prescription extends ORDataObject
{
    /** @var array<int|string, string> */
    public array $form_array = [];

    /** @var array<int|string, string> */
    public array $unit_array = [];

    /** @var array<int|string, string> */
    public array $route_array = [];

    /** @var array<int|string, string> */
    public array $interval_array = [];

    /** @var array<int|string, string> */
    public array $substitute_array = [];

    /** @var array<int, string> */
    public array $medication_array = [];

    /** @var array<int, string> */
    public array $refills_array = [];

    public ?int $id = null;
    public PrescriptionPatient $patient;
    public Person $pharmacist;
    public string $date_added;
    public ?string $txDate = null;
    public string $date_modified;
    public Pharmacy $pharmacy;
    public string $start_date;
    public ?string $filled_date = null;
    public Provider $provider;
    public string $note = '';
    public string $drug = '';
    public string $rxnorm_drugcode = '';
    public string $form = '';
    public string $dosage = '';
    public string $quantity = '';
    public string $size = '';
    public string $unit = '';
    public string $route = '';
    public string $interval = '';
    public string $substitute = '0';
    public int $refills = 0;
    public int $per_refill = 0;
    public int $medication = 0;

    public int $drug_id = 0;
    public int $active = 1;
    public int $ntx = 0;

    public ?int $encounter = null;

    public ?int $created_by = null;

    public ?int $updated_by = null;


    public ?string $diagnosis;

    public ?string $usage_category;

    public ?string $request_intent;

    public ?string $request_intent_title = null;

    public ?string $usage_category_title = null;


    /**
     * Track original values before persistence to support delayed database operations
     */
    private ?string $original_drug = null;
    private ?int $original_medication = null;
    private bool $needs_medication_list_update = false;
    private bool $needs_drug_update = false;


    /**
     * Narrow a mixed value to string for display purposes.
     *
     * Many related objects (PrescriptionPatient, Provider, Pharmacy) have untyped
     * properties and methods that return mixed. This helper avoids casting mixed to
     * string, which PHPStan forbids at level 10.
     */
    private static function str(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        return '';
    }

    /**
    * Constructor sets all Prescription attributes to their default value
    */

    function __construct($id = "", $_prefix = "")
    {
        $this->route_array = $this->load_drug_attributes('drug_route');
        $this->form_array = $this->load_drug_attributes('drug_form');
        $this->interval_array = $this->load_drug_attributes('drug_interval');
        $this->unit_array = $this->load_drug_attributes('drug_units');

        $this->substitute_array = ["",xl("substitution allowed"),
            xl("do not substitute")];

        $this->medication_array = [0 => xl('No'), 1 => xl('Yes')];

        if (is_numeric($id)) {
            $this->id = (int) $id;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authUserID = $session->get('authUserID');
        $this->refills = 0;
        $this->substitute = '0';
        $this->_prefix = $_prefix;
        $this->_table = "prescriptions";
        $this->pharmacy = new Pharmacy();
        $this->pharmacist = new Person();
        // default provider is the current user
        $this->provider = new Provider($authUserID);
        $this->patient = new PrescriptionPatient();
        $this->start_date = date("Y-m-d");
        $this->date_added = date("Y-m-d H:i:s");
        $this->date_modified = date("Y-m-d H:i:s");
        $this->created_by = $this->updated_by = (is_numeric($authUserID)
            ? (int) $authUserID
            : null);
        $this->per_refill = 0;
        $encounter = $session->get('encounter');
        $this->encounter = is_numeric($encounter) ? (int) $encounter : null;
            $this->note = "";
            $this->request_intent = FHIRMedicationIntentEnum::ORDER->value;
            $this->usage_category = "outpatient";

            $this->drug_id = 0;
            $this->active = 1;

        $this->ntx = 0;
        $this->diagnosis = "";

        for ($i = 0; $i < 21; $i++) {
            $this->refills_array[$i] = sprintf("%02d", $i);
        }

        if ($id != "") {
            $this->populate();
        }
    }



    function persist(): bool
    {
        $this->date_modified = date("Y-m-d H:i:s");
        if ($this->id === null || $this->id === 0) {
            $this->date_added = date("Y-m-d H:i:s");
        }


        // these values are not posted so we need to set them here
        $listService = new ListService();
        // need to populate the usage_category_title and request_intent_title before persisting
        $usageCategory = $listService->getListOption('medication-usage-category', $this->usage_category);
        $requestIntent = $listService->getListOption('medication-request-intent', $this->request_intent);
        $ucTitle = $usageCategory['title'] ?? null;
        $this->usage_category_title = is_string($ucTitle) ? $ucTitle : '';
        $riTitle = $requestIntent['title'] ?? null;
        $this->request_intent_title = is_string($riTitle) ? $riTitle : '';
        // Store original prescription ID before potential creation
        // Perform the main prescription persist operation first
        $result = (bool) parent::persist();

        if ($result) {
            // Handle medication list updates that were moved from set_medication()
            if ($this->needs_medication_list_update) {
                $this->handle_medication_list_updates();
            }

            // Handle drug updates that were moved from set_drug()
            if ($this->needs_drug_update) {
                $this->handle_drug_updates();
            }

            // Reset tracking flags after successful persistence
            $this->reset_change_tracking();
        }

        return $result;
    }

    /**
     * Handle medication list updates moved from set_medication function
     */
    private function handle_medication_list_updates(): void
    {
        global $ISSUE_TYPES;
        /** @var array<string, mixed>|null $ISSUE_TYPES */

        // Avoid making a mess if we are not using the "medication" issue type.
        if (isset($ISSUE_TYPES) && !$ISSUE_TYPES['medication']) {
            return;
        }

        //below statements are bypassing the persist() function and being used directly in database statements, hence need to use the functions in library/formdata.inc.php
        // they have already been run through populate() hence stripped of escapes, so now need to be escaped for database (add_escape_custom() function).


        $dataRow = [];

        // First try to find by prescription_id if we have one (more reliable and direct)
        if ($this->id !== null && $this->id !== 0) {
            $dataRow = QueryUtils::fetchRecords(
                "select l.id from lists l join lists_medication lm on l.id = lm.list_id where "
                . " l.type = 'medication' and l.activity = 1 and (l.enddate is null or cast(now() as date) < l.enddate) and "
                . " lm.prescription_id = ? and l.pid = ? limit 1",
                [$this->id, $this->patient->id]
            );
            $dataRow = $dataRow[0] ?? [];
        }

        // If not found by prescription_id, fall back to title matching (legacy logic)
        if (!isset($dataRow['id'])) {
            $dataRow = QueryUtils::fetchRecords("select id from lists where type = 'medication' and "
            . " activity = 1 and (enddate is null or cast(now() as date) < enddate) and "
             . " upper(trim(title)) = upper(trim(?)) and pid = ? limit 1", [$this->drug, $this->patient->id]);
            $dataRow = $dataRow[0] ?? [];
        }


        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($this->medication && !isset($dataRow['id'])) {

            $inactiveDataRow = [];

            // First try to find inactive medication by prescription_id if we have one
            if ($this->id !== null && $this->id !== 0) {
                $dataRow = QueryUtils::fetchRecords(
                    "select l.id from lists l join lists_medication lm on "
                    . " l.id = lm.list_id where l.type = 'medication' and l.activity = 0 AND (l.enddate is null or cast(now() as date) < l.enddate) "
                    . " and lm.prescription_id = ? and l.pid = ? limit 1",
                    [$this->id, $this->patient->id]
                );
                $inactiveDataRow = $dataRow[0] ?? [];
            }

            // If not found by prescription_id, fall back to title matching for inactive medications
            if (!isset($inactiveDataRow['id'])) {
                $dataRow = QueryUtils::fetchRecords(
                    "select id from lists where type = 'medication' "
                    . "and activity = 0 AND (enddate is null or cast(now() as date) < enddate) and upper(trim(title)) = upper(trim(?)) and pid = ? limit 1",
                    [$this->original_drug, $this->patient->id]
                );
                $inactiveDataRow = $dataRow[0] ?? [];
            }


            if (!isset($inactiveDataRow['id'])) {
                //add the record to the medication list
                $medListId = QueryUtils::sqlInsert(
                    "insert into lists(date,begdate,type,activity,pid,user,groupname,title) "
                    . " values (now(),cast(now() as date),'medication',1,?,?,?,?)",
                    [$this->patient->id, $session->get('authUser'), $session->get('authProvider'), $this->drug]
                );
                $this->gen_lists_medication($medListId);
            } else {
                QueryUtils::sqlStatementThrowException(
                    'update lists set activity = 1,user = ?, groupname = ? where id = ?',
                    [$session->get('authUser'), $session->get('authProvider'), $inactiveDataRow['id']]
                );
                $this->gen_lists_medication($inactiveDataRow["id"]);
            }
        } elseif (!$this->medication && isset($dataRow['id'])) {
            //remove the drug from the medication list if it exists
            QueryUtils::sqlStatementThrowException(
                'update lists set activity = 0,user = ?, groupname = ? where id = ?',
                [$session->get('authUser'), $session->get('authProvider'), $dataRow['id']]
            );
        } elseif ($this->medication && isset($dataRow['id'])) {
            $this->gen_lists_medication($dataRow["id"]);
        }
    }

    /**
     * Handle drug updates moved from set_drug function
     */
    private function handle_drug_updates(): void
    {
        // If the medication already exists in the list and the drug name is being changed, update the title there as well
        if ($this->original_drug !== null && $this->original_drug !== '' && $this->medication && $this->drug !== $this->original_drug) {

            $dataRow = [];

            // First try to find by prescription_id if we have one (more reliable and direct)
            if ($this->id !== null && $this->id !== 0) {
                $dataRow = QueryUtils::fetchRecords(
                    "select l.id from lists l join lists_medication lm on "
                    . " l.id = lm.list_id where l.type = 'medication' and (l.enddate is null or cast(now() as date) < l.enddate) "
                    . " and lm.prescription_id = ? and l.pid = ? limit 1",
                    [
                        $this->id, $this->patient->id
                    ]
                );
                $dataRow = $dataRow[0] ?? [];
            }

            // If not found by prescription_id, fall back to original drug title matching (legacy logic)
            if (!isset($dataRow['id'])) {
                $dataRow = QueryUtils::fetchRecords(
                    "select id from lists where type = 'medication' and "
                    . "(enddate is null or cast(now() as date) < enddate) and upper(trim(title)) = upper(trim(?)) and pid = ? limit 1",
                    [$this->original_drug, $this->patient->id]
                );
                $dataRow = $dataRow[0] ?? [];
            }


            if (isset($dataRow['id'])) {
                $session = SessionWrapperFactory::getInstance()->getActiveSession();
                QueryUtils::sqlStatementThrowException(
                    'update lists set activity = 1'
                    . " ,user = ?, groupname = ?, title = ? where id = ?",
                    [$session->get('authUser'), $session->get('authProvider'), $this->drug, $dataRow['id']]
                );
                $this->gen_lists_medication($dataRow["id"]);
            }
        }
    }

    /**
     * Reset change tracking flags after successful persistence
     */
    private function reset_change_tracking(): void
    {
        $this->needs_medication_list_update = false;
        $this->needs_drug_update = false;
        $this->original_drug = $this->drug;
        $this->original_medication = $this->medication;
    }


    function populate(): void
    {
        parent::populate();

        // for old historical data we are going to populate our created_by and updated_by
        if ($this->created_by === null || $this->created_by === 0) {
            $this->created_by = $this->get_provider_id();
        }
        if ($this->updated_by === null || $this->updated_by === 0) {
            $this->updated_by = $this->get_provider_id();
        }


        // Initialize original values for change tracking
        $this->original_drug = $this->drug;
        $this->original_medication = $this->medication;

    }

    function toString(bool $html = false): string
    {
        $fields = [
            'ID' => $this->id,
            'Patient' => self::str($this->patient->get_name_display()),
            'Patient ID' => self::str($this->patient->id),
            'Pharmacist' => $this->pharmacist->get_display_name(),
            'Pharmacist ID' => $this->pharmacist->get_id(),
            'Date Added' => $this->date_added,
            'Date Modified' => $this->date_modified,
            'Pharmacy' => self::str($this->pharmacy->name),
            'Pharmacy ID' => self::str($this->pharmacy->id),
            'Start Date' => $this->start_date,
            'Filled Date' => $this->filled_date,
            'Provider' => self::str($this->provider->get_name_display()),
            'Provider ID' => self::str($this->provider->id),
            'Note' => $this->note,
            'Drug' => $this->drug,
            'Code' => $this->rxnorm_drugcode,
            'Form' => $this->form_array[$this->form] ?? '',
            'Dosage' => $this->dosage,
            'Qty' => $this->quantity,
            'Size' => $this->size,
            'Unit' => $this->unit_array[$this->unit] ?? '',
            'Route' => $this->route_array[$this->route] ?? '',
            'Interval' => $this->interval_array[$this->interval] ?? '',
            'Substitute' => $this->substitute_array[$this->substitute] ?? '',
            'Refills' => $this->refills,
            'Per Refill' => $this->per_refill,
            'Drug ID' => $this->drug_id,
            'Active' => $this->active,
            'Transmitted' => $this->ntx,
        ];
        $lines = array_map(fn(string $k, mixed $v) => "{$k}: {$v}", array_keys($fields), array_values($fields));
        $string = "\n" . implode("\n", $lines);
        return $html ? nl2br($string) : $string;
    }

    /** @return array<int|string, string> */
    private function load_drug_attributes(string $id): array
    {
        $arr = [];
        $records = QueryUtils::fetchRecords("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq", [$id]);
        /** @var array{option_id: string, title: string} $row */
        foreach ($records as $row) {
            $arr[$row['option_id']] = $row['title'] === '' ? ' ' : xl_list_label($row['title']);
        }

        return $arr;
    }

    function get_encounter(): ?int
    {
        // this originally was the session's 'encounter' which seems really dangerous if a prescription is created when
        // one encounter is open in the session and then the prescription has any updates to the original prescription when another encounter is open in the session
        // so this value is now going to be set when the prescription is created and then remain static for that prescription
        return $this->encounter;
    }

    function get_unit_display(string $display_form = ""): string
    {
        return( ($this->unit_array[$this->unit] ?? '') );
    }

    function get_unit(): string
    {
        return $this->unit;
    }
    function set_unit($unit): void
    {
        if (is_numeric($unit)) {
            $this->unit = (string) $unit;
        }
    }

    function set_id($id): void
    {
        if ($id !== null && $id !== '' && $id !== 0 && is_numeric($id)) {
            $this->id = (int) $id;
        }
    }
    function get_id(): ?int
    {
        return $this->id;
    }

    function get_dosage_display(string $display_form = ""): string
    {
        if ($this->form === '' && $this->interval === '') {
            return( $this->dosage );
        } else {
            return ($this->dosage . " " . xl('in') . " " . ($this->form_array[$this->form] ?? '') . " " . ($this->interval_array[$this->interval] ?? ''));
        }
    }

    function set_dosage($dosage): void
    {
        if (is_string($dosage)) {
            $this->dosage = $dosage;
        }
    }
    function get_dosage(): string
    {
        return $this->dosage;
    }

    function set_form($form): void
    {
        if (is_numeric($form)) {
            $this->form = (string) $form;
        }
    }
    function get_form(): string
    {
        return $this->form;
    }

    function set_refills($refills): void
    {
        if (is_numeric($refills)) {
            $this->refills = (int) $refills;
        }
    }
    function get_refills(): int
    {
        return $this->refills;
    }

    function set_size($size): void
    {
        if (is_string($size) || is_numeric($size)) {
            $this->size = preg_replace("/[^0-9\/\.\-]/", "", (string) $size) ?? '';
        }
    }
    function get_size(): string
    {
        return $this->size;
    }

    function set_quantity($qty): void
    {
        if (is_string($qty) || is_numeric($qty)) {
            $this->quantity = (string) $qty;
        }
    }
    function get_quantity(): string
    {
        return $this->quantity;
    }

    function set_route($route): void
    {
        if (is_string($route)) {
            $this->route = $route;
        }
    }
    function get_route(): string
    {
        return $this->route;
    }

    function set_interval($interval): void
    {
        if (is_numeric($interval)) {
            $this->interval = (string) $interval;
        }
    }
    function get_interval(): string
    {
        return $this->interval;
    }

    function set_substitute($sub): void
    {
        if (is_numeric($sub)) {
            $this->substitute = (string) $sub;
        }
    }
    function get_substitute(): string
    {
        return $this->substitute;
    }
    public function gen_lists_medication($list_id): void
    {
        $instructions = $this->size . ($this->unit_array[$this->unit] ?? '') . "\t\t" . $this->get_dosage_display();
        if ($list_id !== null && $list_id !== '' && $list_id !== 0) {
            $listsMedication = QueryUtils::fetchRecords("select prescription_id,list_id from lists_medication where list_id = ? limit 1", [$list_id]);
            if ($listsMedication !== []) {
                $bind = [$instructions, $this->usage_category ?? '', $this->usage_category_title ?? ''
                    , $this->request_intent ?? '', $this->request_intent_title ?? ''];
                $sql = "update lists_medication set drug_dosage_instructions = ? "
                    . ", usage_category=?, usage_category_title = ?, request_intent=?, request_intent_title =? ";
                // old data may not have prescription_id set so we need to link it here if it has been matched previously
                if (!isset($listsMedication[0]['prescription_id']) || $listsMedication[0]['prescription_id'] === '') {
                    $sql .= ", prescription_id = ? ";
                    $bind[] = $this->id;
                }
                $sql .= " where list_id = ? ";
                $bind[] = $list_id;
                QueryUtils::sqlStatementThrowException($sql, $bind);
            } else {
                QueryUtils::sqlInsert("insert into lists_medication(list_id, drug_dosage_instructions, prescription_id"
                . ",usage_category, usage_category_title, request_intent,  request_intent_title ) values (?, ?, ?, ?, ?, ?, ?)", [$list_id, $instructions, $this->id
                    , $this->usage_category ?? '', $this->usage_category_title ?? ''
                    , $this->request_intent ?? '', $this->request_intent_title ?? '']);
            }
        }
    }

    public function set_medication($med): void
    {
        // Store original value if not already tracked
        if ($this->original_medication === null) {
            $this->original_medication = $this->medication;
        }

        // Update the property value
        if (is_int($med)) {
            $this->medication = $med;
        } elseif (is_numeric($med)) {
            $this->medication = (int) $med;
        }

        // Mark that medication list needs updating during persist
        $this->needs_medication_list_update = true;
    }


    function get_medication(): int
    {
        return $this->medication;
    }

    function set_per_refill($pr): void
    {
        if (is_numeric($pr)) {
            $this->per_refill = (int) $pr;
        }
    }
    function get_per_refill(): int
    {
        return $this->per_refill;
    }

    function set_patient_id($id): void
    {
        if (is_numeric($id)) {
            $this->patient = new PrescriptionPatient($id);
        }
    }
    function get_patient_id(): mixed
    {
        return $this->patient->id;
    }

    function set_provider_id($id): void
    {
        if (is_numeric($id)) {
            $this->provider = new Provider($id);
        }
    }
    function get_provider_id(): ?int
    {
        $id = $this->provider->id ?? null;
        return is_numeric($id) ? (int) $id : null;
    }

    function set_created_by($id): void
    {
        if (is_numeric($id)) {
            $this->created_by = (int) $id;
        }
    }
    function get_created_by(): ?int
    {
        return $this->created_by;
    }

    function set_updated_by($id): void
    {
        if (is_numeric($id)) {
            $this->updated_by = (int) $id;
        }
    }
    function get_updated_by(): ?int
    {
        return $this->updated_by;
    }

    public function get_diagnosis(): ?string
    {
        return $this->diagnosis;
    }

    /** @param list<string>|string $diagnosis */
    public function set_diagnosis(array|string $diagnosis): void
    {
        // codes are concatenated with a ';' if multiple
        if (is_array($diagnosis)) {
            $diagnosis = implode(';', $diagnosis);
        }
        $this->diagnosis = $diagnosis;
    }

    public function set_request_intent(?string $intent): void
    {
        $this->request_intent = $intent;
    }

    public function get_request_intent(): ?string
    {
        return $this->request_intent;
    }

    public function set_usage_category_title(?string $title): void
    {
        $this->usage_category_title = $title;
    }

    public function get_usage_category_title(): ?string
    {
        return $this->usage_category_title;
    }

    public function set_request_intent_title(?string $title): void
    {
        $this->request_intent_title = $title;
    }

    public function get_request_intent_title(): ?string
    {
        return $this->request_intent_title;
    }

    public function get_usage_category(): ?string
    {
        return $this->usage_category;
    }

    public function set_usage_category(?string $category): void
    {
        $this->usage_category = $category;
    }

    function set_provider($pobj): void
    {
        if ($pobj instanceof Provider) {
            $this->provider = $pobj;
        }
    }

    function set_pharmacy_id($id): void
    {
        if (is_numeric($id)) {
            $this->pharmacy = new Pharmacy($id);
        }
    }
    function get_pharmacy_id(): mixed
    {
        return $this->pharmacy->id;
    }

    function set_pharmacist_id($id): void
    {
        if (is_numeric($id)) {
            $this->pharmacist = new Person((int) $id);
        }
    }
    function get_pharmacist(): int
    {
        return $this->pharmacist->get_id();
    }

    function get_start_date_y(): string
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[0];
    }
    function set_start_date_y($year): void
    {
        if (is_numeric($year)) {
            $ymd = explode("-", $this->start_date);
            $ymd[0] = $year;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date_m(): string
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[1];
    }
    function set_start_date_m($month): void
    {
        if (is_numeric($month)) {
            $ymd = explode("-", $this->start_date);
            $ymd[1] = $month;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date_d(): string
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[2];
    }
    function set_start_date_d($day): void
    {
        if (is_numeric($day)) {
            $ymd = explode("-", $this->start_date);
            $ymd[2] = $day;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date(): string
    {
        return $this->start_date;
    }
    function set_start_date($date): void
    {
        if (is_string($date)) {
            $this->start_date = $date;
        }
    }

    // TajEmo work by CB 2012/05/30 01:56:32 PM added encounter for auto ticking of checkboxes
    function set_encounter($enc): void
    {
        if (is_int($enc)) {
            $this->encounter = $enc;
        } elseif (is_numeric($enc)) {
            $this->encounter = (int) $enc;
        } else {
            $this->encounter = null;
        }
    }

    function get_date_added(): string
    {
        return $this->date_added;
    }
    function set_date_added($date): void
    {
        if (is_string($date)) {
            $this->date_added = $date;
        }
    }
    function set_txDate($txdate): void
    {
        $this->txDate = is_string($txdate) ? $txdate : null;
    }
    function get_txDate(): ?string
    {
        return $this->txDate;
    }

    function get_date_modified(): string
    {
        return $this->date_modified;
    }
    function set_date_modified($date): void
    {
        if (is_string($date)) {
            $this->date_modified = $date;
        }
    }

    function get_filled_date(): ?string
    {
        return $this->filled_date;
    }
    function set_filled_date($date): void
    {
        $this->filled_date = is_string($date) ? $date : null;
    }

    function set_note($note): void
    {
        if (is_string($note)) {
            $this->note = $note;
        }
    }

    public function set_drug_dosage_instructions(?string $instructions): void
    {
        $this->note = $instructions ?? '';
    }

    function get_note(): string
    {
        return $this->note;
    }

    public function set_drug($drug): void
    {
        // Store original value if not already tracked
        if ($this->original_drug === null) {
            $this->original_drug = $this->drug;
        }

        // Update the property value
        if (is_string($drug)) {
            $this->drug = $drug;
        }

        // Mark that drug updates are needed during persist
        $this->needs_drug_update = true;
    }
    function get_drug(): string
    {
        return $this->drug;
    }
    function set_ntx($ntx): void
    {
        if (is_int($ntx)) {
            $this->ntx = $ntx;
        } elseif (is_numeric($ntx)) {
            $this->ntx = (int) $ntx;
        }
    }
    function get_ntx(): int
    {
        return $this->ntx;
    }

    function set_rxnorm_drugcode($rxnorm_drugcode): void
    {
        if (is_string($rxnorm_drugcode)) {
            $this->rxnorm_drugcode = $rxnorm_drugcode;
        }
    }
    function get_rxnorm_drugcode(): string
    {
        return $this->rxnorm_drugcode;
    }

    function get_filled_by_id(): int
    {
        return $this->pharmacist->get_id();
    }
    function set_filled_by_id($id): void
    {
        if (is_numeric($id)) {
            $this->pharmacist->set_id($id);
        }
    }

    function set_drug_id($drug_id): void
    {
        if (is_int($drug_id)) {
            $this->drug_id = $drug_id;
        } elseif (is_numeric($drug_id)) {
            $this->drug_id = (int) $drug_id;
        }
    }
    function get_drug_id(): int
    {
        return $this->drug_id;
    }

    function set_active($active): void
    {
        if (is_int($active)) {
            $this->active = $active;
        } elseif (is_numeric($active)) {
            $this->active = (int) $active;
        }
    }
    function get_active(): int
    {
        return $this->active;
    }
    function get_prescription_display(): string
    {
        $oerConfig = OEGlobalsBag::getInstance()->get('oer_config');
        /** @var array{prescriptions: array{format: string}} $oerConfig */
        $pconfig = $oerConfig['prescriptions'];

        switch ($pconfig['format']) {
            case "FL":
                return $this->get_prescription_florida_display();
            default:
                break;
        }

        $string = '';
        $records = QueryUtils::fetchRecords(
            "SELECT * FROM users JOIN facility AS f ON f.name = users.facility WHERE users.id = ?",
            [$this->provider->id]
        );
        if ($records !== []) {
            $row = $records[0];
            $string = self::str($row['name']) . "\n"
                    . self::str($row['street']) . "\n"
                    . self::str($row['city']) . ", " . self::str($row['state']) . " " . self::str($row['postal_code']) . "\n"
                    . self::str($row['phone']) . "\n\n";
        }

        $string .= ""
                . "Prescription For:" . "\t" . self::str($this->patient->get_name_display()) . "\n"
                . "DOB:" . "\t" . self::str($this->patient->get_dob()) . "\n"
                . "Start Date: " . "\t\t" . $this->start_date . "\n"
                . "Provider: " . "\t\t" . self::str($this->provider->get_name_display()) . "\n"
                . "Provider DEA No.: " . "\t\t" . self::str($this->provider->federal_drug_id) . "\n"
                . "Drug: " . "\t\t\t" . $this->drug . "\n"
                . "Dosage: " . "\t\t" . $this->dosage . " in " . ($this->form_array[$this->form] ?? '') . " form " . ($this->interval_array[$this->interval] ?? '') . "\n"
                . "Qty: " . "\t\t\t" . $this->quantity . "\n"
                . "Medication Unit: " . "\t" . $this->size  . " " . ($this->unit_array[$this->unit] ?? '') . "\n"
                . "Substitute: " . "\t\t" . $this->substitute_array[$this->substitute] . "\n";
        if ($this->refills > 0) {
            $string .= "Refills: " . "\t\t" . $this->refills . ", of quantity: " . $this->per_refill . "\n";
        }

        $string .= "\n" . "Notes: \n" . $this->note . "\n";
        return $string;
    }

    function get_prescription_florida_display(): string
    {
        $ntt = new NumberToText($this->quantity);
        $ntt2 = new NumberToText($this->per_refill);
        $ntt3 = new NumberToText($this->refills);

        $string = "";

        $gnd = self::str($this->provider->get_name_display());

        while (strlen($gnd) < 31) {
            $gnd .= " ";
        }

        $string .= $gnd . self::str($this->provider->federal_drug_id) . "\n";

        $records = QueryUtils::fetchRecords(
            "SELECT * FROM users JOIN facility AS f ON f.name = users.facility WHERE users.id = ?",
            [$this->provider->id]
        );

        if ($records !== []) {
            $row = $records[0];
            $rfn = self::str($row['name']);

            while (strlen($rfn) < 31) {
                $rfn .= " ";
            }

            $string .= $rfn . self::str($this->provider->get_provider_number_default()) . "\n"
                    . self::str($row['street']) . "\n"
                    . self::str($row['city']) . ", " . self::str($row['state']) . " " . self::str($row['postal_code']) . "\n"
                    . self::str($row['phone']) . "\n";
        }

        $string .= "\n";
        $string .= strtoupper(self::str($this->patient->lname)) . ", " . ucfirst(self::str($this->patient->fname)) . " " . self::str($this->patient->mname) . "\n";
        $string .= "DOB " .  self::str($this->patient->date_of_birth) . "\n";
        $string .= "\n";
        $startTimestamp = strtotime($this->start_date);
        $string .= date("F j, Y", $startTimestamp !== false ? $startTimestamp : 0) . "\n";
        $string .= "\n";
        $string .= strtoupper($this->drug) . " " . $this->size  . " " . ($this->unit_array[$this->unit] ?? '') . "\n";
        if (strlen($this->note) > 0) {
            $string .= "Notes: \n" . $this->note . "\n";
        }

        if ($this->dosage !== '') {
            $string .= $this->dosage;
            if ($this->form !== '') {
                $string .= " " . $this->form_array[$this->form];
            }

            if ($this->interval !== '') {
                $string .= " " . $this->interval_array[$this->interval];
            }

            if ($this->route !== '') {
                $string .= " " . $this->route_array[$this->route] . "\n";
            }
        }

        if ($this->quantity !== '') {
            $string .= "Disp: " . $this->quantity . " (" . trim(strtoupper($ntt->convert())) . ")" . "\n";
        }

        $string .= "\n";
        $string .= "Refills: " . $this->refills . " (" . trim(strtoupper($ntt3->convert())) . "), Per Refill Disp: " . $this->per_refill . " (" . trim(strtoupper($ntt2->convert())) . ")" . "\n";
        $string .= $this->substitute_array[$this->substitute] . "\n";
        $string .= "\n";

        return $string;
    }

    private const SORTABLE_COLUMNS = [
        'active',
        'created_by',
        'date_added',
        'date_modified',
        'datetime',
        'diagnosis',
        'dosage',
        'drug',
        'drug_dosage_instructions',
        'drug_id',
        'encounter',
        'end_date',
        'erx_source',
        'erx_uploaded',
        'external_id',
        'filled_by_id',
        'filled_date',
        'form',
        'id',
        'indication',
        'interval',
        'medication',
        'note',
        'ntx',
        'patient_id',
        'per_refill',
        'pharmacy_id',
        'prescriptionguid',
        'prn',
        'provider_id',
        'quantity',
        'refills',
        'request_intent',
        'request_intent_title',
        'route',
        'rtx',
        'rxnorm_drugcode',
        'site',
        'size',
        'start_date',
        'substitute',
        'txDate',
        'unit',
        'updated_by',
        'usage_category',
        'usage_category_title',
        'user',
    ];

    /** @return list<Prescription> */
    static function prescriptions_factory(
        $patient_id,
        $order_by = "active DESC, date_modified DESC, date_added DESC"
    ): array {
        if (!is_numeric($patient_id)) {
            return [];
        }
        $patient_id = (int) $patient_id;
        $default_order = 'active DESC, date_modified DESC, date_added DESC';
        if (!is_string($order_by)) {
            $order_by = $default_order;
        }

        // Validate each comma-separated ORDER BY part against the allowlist.
        $sanitized_parts = [];
        foreach (explode(',', $order_by) as $part) {
            $tokens = preg_split('/\s+/', trim($part), -1, PREG_SPLIT_NO_EMPTY);
            if ($tokens === false || $tokens === []) {
                continue;
            }
            $column = strtolower($tokens[0]);
            if (!in_array($column, self::SORTABLE_COLUMNS, true)) {
                continue;
            }
            $direction = 'DESC';
            if (isset($tokens[1]) && strtoupper($tokens[1]) === 'ASC') {
                $direction = 'ASC';
            }
            $sanitized_parts[] = $column . ' ' . $direction;
        }

        $sanitized_order = $sanitized_parts !== [] ? implode(', ', $sanitized_parts) : $default_order;

        $prescriptions = [];
        $p = new Prescription();
        $table = is_string($p->_table) ? $p->_table : 'prescriptions';
        $sql = "SELECT id FROM " . escape_table_name($table) . " WHERE patient_id = ? " .
                "ORDER BY " . $sanitized_order;
        $records = QueryUtils::fetchRecords($sql, [$patient_id]);
        foreach ($records as $row) {
            $prescriptions[] = new Prescription($row['id']);
        }

        return $prescriptions;
    }

    function get_dispensation_count(): int
    {
        if ($this->id === null || $this->id === 0) {
            return 0;
        }

        $records = QueryUtils::fetchRecords("SELECT count(*) AS count FROM drug_sales " .
                    "WHERE prescription_id = ? AND quantity > 0", [$this->id]);
        if ($records === []) {
            return 0;
        }
        $count = $records[0]['count'] ?? 0;
        return is_numeric($count) ? (int) $count : 0;
    }
}
