<?php

/**
 * CodeImportSupportedTypeFilterEvent is dispatched by the Install Code Set page to build the list
 * of code types offered in its drop-down.
 *
 * A module that listens for {@see CodeImportEvent} should also listen for this event and call
 * {@see self::addSupportedCodeType()} for each code type it can import, otherwise the type is
 * never offered to the user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Codes;

use Symfony\Contracts\EventDispatcher\Event;

class CodeImportSupportedTypeFilterEvent extends Event
{
    public const EVENT_NAME = 'code_import_types.filter';

    /**
     * @param list<string> $supportedCodes
     */
    public function __construct(private array $supportedCodes = [])
    {
    }

    /**
     * @return list<string>
     */
    public function getSupportedCodeTypes(): array
    {
        return $this->supportedCodes;
    }

    public function addSupportedCodeType(string $codeType): void
    {
        if (!in_array($codeType, $this->supportedCodes, true)) {
            $this->supportedCodes[] = $codeType;
        }
    }

    public function removeSupportedCodeType(string $codeType): void
    {
        $this->supportedCodes = array_values(
            array_filter(
                $this->supportedCodes,
                fn(string $type): bool => $type !== $codeType
            )
        );
    }
}
