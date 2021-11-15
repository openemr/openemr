<?php

declare(strict_types=1);

namespace Webimpress\SafeWriter\Exception;

use RuntimeException as PhpRuntimeException;
use Throwable;

use function sprintf;

final class RenameException extends PhpRuntimeException implements ExceptionInterface
{
    /**
     * @param string $message
     * @param int $code
     */
    private function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @internal
     */
    public static function unableToMoveFile(string $source, string $target) : self
    {
        return new self(sprintf(
            'Could not move file "%s" to location "%s": '
            . 'either the source file is not readable, or the destination is not writable',
            $source,
            $target
        ));
    }
}
