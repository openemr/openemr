<?php

/**
 * address class for smarty templates.  Follows the Active Record Design Pattern
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    duhlman
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) duhlman
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ORDataObject;

class Address extends ORDataObject implements \JsonSerializable
{
    var $id;
    var $foreign_id;
    var $line1;
    var $line2;
    var $city;
    var $state;
    var $zip;
    var $plus_four;
    var $country;

    /**
     * @var string The county or district of the address
     */
    private $district;

    /**
     * Constructor sets all Address attributes to their default value
     */
    function __construct($id = "", $foreign_id = "")
    {
        parent::__construct("addresses");

        $this->id = $id;
        $this->foreign_id = $foreign_id;
        $this->line1 = "";
        $this->line2 = "";
        $this->city = "";
        $this->state = "";
        $this->zip = "";
        $this->plus_four = "";
        $this->district = "";
        $this->country = "USA";
        if ($id != "") {
            $this->populate();
        }
    }
    static function factory_address($foreign_id = "")
    {
        $sqlArray = array();

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $a = new Address();
        $sql = "SELECT id FROM  " . escape_table_name($a->_table) . " WHERE foreign_id " . $foreign_id_sql;
        //echo $sql . "<bR />";
        $results = sqlQ($sql, $sqlArray);
        //echo "sql: $sql";
        $row = sqlFetchArray($results);

        if (!empty($row)) {
            $a = new Address($row['id']);
        }

        return $a;
    }

    function toString($html = false)
    {
        $string = "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . $this->line1 . "\n"
        . $this->line2 . "\n"
        . $this->city . ", " . strtoupper($this->state) . " " . $this->zip . "-" . $this->plus_four . "\n"
        . $this->country . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    function set_id($id)
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }
    function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
    }
    function get_foreign_id()
    {
        return $this->foreign_id;
    }
    function set_line1($line1)
    {
        $this->line1 = $line1;
    }
    function get_line1()
    {
        return $this->line1;
    }
    function set_line2($line2)
    {
        $this->line2 = $line2;
    }
    function get_line2()
    {
        return $this->line2;
    }
    function get_lines_display()
    {
        $string = $this->get_line1();
        $string .= " " . $this->get_line2();
        return $string;
    }
    function set_city($city)
    {
        $this->city = $city;
    }
    function get_city()
    {
        return $this->city;
    }
    function set_state($state)
    {
        $this->state = strtoupper($state);
    }
    function get_state()
    {
        return $this->state;
    }
    function set_zip($zip)
    {
        $this->zip = $zip;
    }
    function get_zip()
    {
        return $this->zip;
    }
    function set_plus_four($plus_four)
    {
        $this->plus_four = $plus_four;
    }
    function get_plus_four()
    {
        return $this->plus_four;
    }
    function set_country($country)
    {
        $this->country = $country;
    }

    /**
     * Most users should use set_postalcode to handle regional differences
     * @param $postalcode The postal code for the address
     */
    function set_postalcode($postalcode)
    {
        $this->zip = $postalcode;

        // change things up for the USA
        if ($this->country == "USA") {
            // we will parse our inner elements based on our postal codes
            if (strpos($postalcode, "-") !== false) { // yes I know this is lazy...
                $parts = explode("-", $postalcode);
                $this->zip = $parts[0] ?? "";
                $this->plus_four = $parts[1] ?? "";
            }
        }
    }

    function get_postalcode(): ?string
    {
        // we handle plus four here in the USA
        if ($this->country == "USA") {
            if (!empty($this->plus_four)) {
                return ($this->zip ?? "") . "-" . ($this->plus_four ?? "");
            }
        }
        return $this->zip;
    }
    function get_country()
    {
        return $this->country;
    }
    function persist($fid = "")
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        parent::persist();
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @return string
     */
    public function get_district(): string
    {
        return $this->district;
    }

    /**
     * @param string $district
     * @return Address
     */
    public function set_district(string $district): Address
    {
        $this->district = $district;
        return $this;
    }



    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "id" => $this->get_id(),
            "foreign_id" => $this->get_foreign_id(),
            "line1" => $this->get_line1(),
            "line2" => $this->get_line2(),
            "city" => $this->get_city(),
            "district" => $this->get_district(),
            "state" => $this->get_state(),
            "zip" => $this->get_zip(),
            "plus_four" => $this->get_plus_four(),
            "postalcode" => $this->get_postalcode(),
            "country" => $this->get_country()
        ];
    }
// end of Address
}
