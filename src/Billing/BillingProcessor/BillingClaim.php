<?php

/**
 * This class represents an individual claim, as submit by the
 * user through the Billing Manager and carries the claim's
 * state through the processing process.
 *
 * If a developer needs to pass additional claim data for an individual
 * claim to processing tasks, this is a place to add it.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor;

class BillingClaim implements \JsonSerializable
{
    public const STATUS_LEAVE_UNCHANGED = -1;
    public const STATUS_LEAVE_UNBILLED = 1;
    public const STATUS_MARK_AS_BILLED = 2;

    public const BILL_PROCESS_LEAVE_UNCHANGED = -1;
    public const BILL_PROCESS_OPEN = 0;
    public const BILL_PROCESS_IN_PROGRESS = 1;
    public const BILL_PROCESS_BILLED = 2;

    /**
     * NOT the database id in billing table,
     * but representation of claim using the format
     * pid-encounter
     *
     * @var
     */
    protected $id;

    /**
     * Encounter ID
     * @var mixed|string
     */
    protected $encounter;

    /**
     * Patient's pid
     *
     * @var mixed|string
     */
    protected $pid;

    /**
     * x-12 partner ID
     *
     * @var
     */
    protected $partner;

    /**
     * Insurance company ID
     * @var false|string
     */
    protected $payor_id;

    /**
     * Primary, Secondary or Tertiary insurance
     *
     * @var
     */
    protected $payor_type;

    /**
     * Options for $payor_type
     */
    public const PRIMARY = 1;
    public const SECONDARY = 2;
    public const TERTIARY = 3;
    public const UNKNOWN = 0;

    /**
     * Indicator for which processing format was selected for x-12
     * partner. Doesn't appear to have any affect on output format
     * other than to indicate what was selected and store with the claim
     * in billing table.
     *
     * @var mixed|string
     */
    protected $target;

    /**
     * If this is the last claim in the processing queue, then this
     * is true otherwise it's false. It is set by the BillingProcessor
     * object while preparing claims.
     *
     * @bool
     */
    protected $is_last;

    public function __construct($claimId, $partner_and_payor)
    {
        // Assume this is not the last claim in the "loop" unless explicitly set.
        $this->is_last = false;

        // The encounter and PID are in the claimId separated by '-' so parse them out
        $ta = explode("-", $claimId);
        $this->id = $claimId;
        $this->pid = $ta[0];
        $this->encounter = $ta[1];

        $this->partner = $partner_and_payor['partner'];

        // The payor ID is in the 'payer' part, the first character is the payer type
        $this->payor_id = substr($partner_and_payor['payer'], 1);

        // The payor type comes in on the payor ID part as a single character prefix
        $payor_type_char = substr(strtoupper($partner_and_payor['payer']), 0, 1);
        if ($payor_type_char == 'P') {
            $this->payor_type = self::PRIMARY;
        } elseif ($payor_type_char == 'S') {
            $this->payor_type = self::SECONDARY;
        } elseif ($payor_type_char == 'T') {
            $this->payor_type = self::TERTIARY;
        } else {
            $this->payor_type = self::UNKNOWN;
        }

        // Fetch the "target" which is essentially just an indicator for which x-12 partner was used
        $sql = "SELECT x.processing_format from x12_partners as x where x.id =?";
        $result = sqlQuery($sql, [$this->getPartner()]);
        if (!empty($result['processing_format'])) {
            $target = $result['processing_format'];
        }
        $this->target = $target ?? '';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed|string
     */
    public function getEncounter(): string
    {
        return $this->encounter;
    }

    /**
     * @return mixed|string
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @return false|string
     */
    public function getPayorId()
    {
        return $this->payor_id;
    }

    /**
     * @return mixed
     */
    public function getPayorType()
    {
        return $this->payor_type;
    }

    /**
     * @return mixed|string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param mixed|string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }


    /**
     * @return mixed
     */
    public function getIsLast()
    {
        return $this->is_last;
    }

    /**
     * @param mixed $is_last
     */
    public function setIsLast($is_last): void
    {
        $this->is_last = $is_last;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}
