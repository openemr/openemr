<?php

/**
 * CodeImportEvent is dispatched when a user uploads a code set on the Install Code Set page.
 *
 * Listeners inspect {@see self::getCodeType()} and, if they recognise it, import the file at
 * {@see self::getFilePath()} and claim the event by calling {@see self::setHandled()}. Claiming an
 * event stops propagation, so at most one listener imports any given upload.
 *
 * OpenEMR core registers its own importers at {@see self::PRIORITY_CORE_FALLBACK}; see that
 * constant for how a module overrides a code type core also supports.
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

use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\Event;

class CodeImportEvent extends Event
{
    public const EVENT_NAME = 'code_types.import';

    public const MESSAGE_TYPE_SUCCESS = 'success';
    public const MESSAGE_TYPE_ERROR = 'error';

    /**
     * Priority at which OpenEMR core registers its own code type importers.
     *
     * Symfony sorts listeners in descending priority order, so any listener registered above this
     * value -- the default priority of 0 is enough -- runs first and may claim the import by
     * calling setHandled(true). That stops propagation before core's handler ever runs, which is
     * how a module replaces core's handling of a code type core also supports.
     */
    public const PRIORITY_CORE_FALLBACK = -100;

    private bool $isHandled = false;

    /** @var array<string, list<string>> */
    private array $messages = [];

    /**
     * @param string $codeType  The ct_key of the code type being imported, eg. 'RXCUI'.
     * @param string $filePath  Absolute path to the uploaded file on disk.
     * @param bool   $isReplace When true the entire existing code set is replaced.
     */
    public function __construct(
        private readonly string $codeType,
        private readonly string $filePath,
        private readonly bool $isReplace
    ) {
    }

    public function getCodeType(): string
    {
        return $this->codeType;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function isReplace(): bool
    {
        return $this->isReplace;
    }

    /**
     * Claim (or un-claim) the import. Claiming stops propagation so no other listener runs.
     */
    public function setHandled(bool $handled): void
    {
        $this->isHandled = $handled;
        if ($handled) {
            $this->stopPropagation();
        }
    }

    public function isHandled(): bool
    {
        return $this->isHandled;
    }

    /**
     * @param string $type One of the self::MESSAGE_TYPE_* constants.
     *
     * @throws InvalidArgumentException when $type is not a known message type.
     */
    public function addMessage(string $type, string $message): void
    {
        if (!in_array($type, [self::MESSAGE_TYPE_SUCCESS, self::MESSAGE_TYPE_ERROR], true)) {
            throw new InvalidArgumentException('Invalid message type');
        }
        $this->messages[$type][] = $message;
    }

    /**
     * @param string|null $type Pass a self::MESSAGE_TYPE_* constant for just that type's messages,
     *                          or '' / null for every message keyed by type.
     *
     * @return ($type is non-empty-string ? list<string> : array<string, list<string>>)
     */
    public function getMessages(?string $type = ''): array
    {
        if ($type === '' || $type === null) {
            return $this->messages;
        }
        return $this->messages[$type] ?? [];
    }
}
