<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\EtherFax;

use ReflectionClass;

/**
 * FaxResult values.
 */
class FaxResult
{
    // private data
    private static $_constants = null;
    // FaxResult constants
    const Success = 0;
    const Error = 1;
    const InProgress = 2;
    const LineBusy = 3;
    const LineDead = 4;
    const LineFailure = 5;
    const NoDialTone = 6;
    const NoAnswer = 7;
    const InvalidOrMissingNumber = 8;
    const InvalidOrMissingFile = 9;
    const InvalidChannel = 10;
    const UnexpectedDisconnect = 11;
    const NoChannelsAvailable = 12;
    const ChannelUnavailable = 13;
    const NothingToCancel = 14;
    const DeviceTimeout = 15;
    const DeviceBusy = 16;
    const NotFaxMachine = 17;
    const IncompatibleFaxMachine = 18;
    const FileError = 19;
    const FileNotFound = 20;
    const FileUnsupported = 21;
    const CallCollision = 22;
    const Cancelled = 23;
    const CallBlocked = 24;
    const DestinationBlackListed = 25;
    const Unauthorized = 100;
    const InvalidParameter = 101;
    const NotImplemented = 102;
    const ItemNotFound = 103;

    /**
     * Returns the constant name for the value provided.
     * @param $result
     * @return int|string|null
     */
    public static function getFaxResult($result)
    {
        if (self::$_constants == null) {
            $c = new ReflectionClass('OpenEMR\Modules\FaxSMS\EtherFax\FaxResult');
            self::$_constants = $c->getConstants();
        }

        foreach (self::$_constants as $name => $value) {
            if ($value == $result) {
                return $name;
            }
        }

        return null;
    }
}
