<?php

/**
 * interface/main/holidays/Holidays_Controller.php implementation of holidays logic.
 *
 * This class contains the implementation of all the logic
 * included in the holidays calendar story.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    sharonco <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2016 Sharon Cohen <sharonco@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "Holidays_Csv.php";
require_once "Holidays_Storage.php";
class Holidays_Controller
{
    const UPLOAD_DIR = "documents/holidays_storage";
    const FILE_NAME = "holidays_to_import.csv";

    public $storage;
    public $target_file;
    private $last_error = "";

    function __construct()
    {
        $this->set_target_file();
        $this->storage = new Holidays_Storage();
    }

    public function set_target_file()
    {
        $this->target_file =
            $GLOBALS["OE_SITE_DIR"] .
            "/" .
            self::UPLOAD_DIR .
            "/" .
            self::FILE_NAME;
    }
    public function get_target_file(): string
    {
        return $this->target_file;
    }

    /**
     * This function uploads the csv file
     * @param $files
     * @return bool
     */
    public function upload_csv($files)
    {
        $this->last_error = "";
        if (empty($files["form_file"])) {
            $this->last_error = xl("No file uploaded");
            return false;
        }

        if (!file_exists($GLOBALS["OE_SITE_DIR"] . "/" . self::UPLOAD_DIR)) {
            if (
                !mkdir(
                    $GLOBALS["OE_SITE_DIR"] . "/" . self::UPLOAD_DIR . "/",
                    0700,
                )
            ) {
                $this->last_error = xl("Unable to create upload directory");
                return false;
            }
        }

        if (!$this->is_valid_csv_upload($files["form_file"])) {
            return false;
        }

        if (
            !move_uploaded_file(
                $files["form_file"]["tmp_name"],
                $this->target_file,
            )
        ) {
            $this->last_error = xl("Unable to save uploaded file");
            return false;
        }

        return true;
    }

    /**
     * Tries to reach the file (csv) and sends the rows to the storage to import the holidays to the calendar external table
     * @return bool
     */
    public function import_holidays_from_csv()
    {
        $this->last_error = "";
        $file = $this->get_file_csv_data();
        if (empty($file)) {
            $this->last_error = xl("CSV file not found");
            return false;
        }

        if (!$this->storage->import_holidays($this->target_file)) {
            $this->last_error = xl("CSV import failed");
            return false;
        }

        return true;
    }

    /**
     * Checks if the file exists and returns the last modification date or empty array if the file doesn't exists
     * @return array
     */
    public function get_file_csv_data(): array
    {
        $file = [];
        if (file_exists($this->target_file)) {
            $file["date"] = date("d/m/Y H:i:s", filemtime($this->target_file));
        }

        return $file;
    }

    public function get_last_error(): string
    {
        return $this->last_error;
    }

    private function is_valid_csv_upload(array $file): bool
    {
        if (!empty($file["error"])) {
            $this->last_error = xl("Upload failed");
            return false;
        }

        if (empty($file["tmp_name"]) || !is_uploaded_file($file["tmp_name"])) {
            $this->last_error = xl("Invalid upload");
            return false;
        }

        $name = $file["name"] ?? "";
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension !== "csv") {
            $this->last_error = xl("File must be a CSV");
            return false;
        }

        return $this->is_valid_csv_content($file["tmp_name"]);
    }

    private function is_valid_csv_content(string $path): bool
    {
        $handle = fopen($path, "r");
        if ($handle === false) {
            $this->last_error = xl("Unable to read uploaded file");
            return false;
        }

        try {
            $row_number = 0;
            while (($row = Holidays_Csv::read_next_data_row($handle)) !== null) {
                $row_number++;

                if (count($row) < 2) {
                    $this->last_error = sprintf(
                        xl('Row %1$d: CSV row must have date and description'),
                        $row_number
                    );
                    return false;
                }

                $date = trim((string) $row[0]);
                if (!$this->is_valid_holiday_date($date)) {
                    $this->last_error = sprintf("Row %d: Invalid date format in CSV", [$row_number]);
                    return false;
                }
            }

            if ($row_number === 0) {
                $this->last_error = xl("CSV file is empty");
                return false;
            }

            return true;
        } finally {
            fclose($handle);
        }
    }

    private function is_valid_holiday_date(string $date): bool
    {
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            $dt = DateTime::createFromFormat("Y/m/d", $date);
            return $dt && $dt->format("Y/m/d") === $date;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dt = DateTime::createFromFormat("Y-m-d", $date);
            return $dt && $dt->format("Y-m-d") === $date;
        }

        return false;
    }

    /**
     * Gets all the holidays and send the result to create the events for the calendar
     */
    public function create_holiday_event(): bool
    {
        $holidays = $this->storage->get_holidays();
        $events = $this->storage->create_events($holidays);
        return true;
    }

    /**
     * Returns an array of the holiday that are in the calendar_external table
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_holidays_by_date_range(string $start_date, string $end_date): array
    {
        $holidays = Holidays_Storage::get_holidays_by_dates(
            $start_date,
            $end_date
        );
        return $holidays;
    }

    /**
     * Return true if the date is a holiday/closed
     * @param string $date Date in YYYY-MM-DD or YYYY/MM/DD format
     */
    public static function is_holiday(string $date): bool
    {
        $holidays = [];
        $holidays = Holidays_Storage::get_holidays_by_dates($date, $date);
        if (in_array($date, $holidays)) {
            return true;
        }

        return false;
    }
}
