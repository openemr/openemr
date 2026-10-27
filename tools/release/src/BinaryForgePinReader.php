<?php

/**
 * Read the currently pinned forge release out of docker/binary/Dockerfile.
 *
 * The Dockerfile ARG defaults are the single source of truth for what the
 * binary image builds today, so the bump tooling reads them rather than
 * carrying a second copy in an updatecli manifest that could drift.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class BinaryForgePinReader
{
    public const DEFAULT_DOCKERFILE = 'docker/binary/Dockerfile';

    public function __construct(
        private string $dockerfile = self::DEFAULT_DOCKERFILE,
    ) {
    }

    public function read(): BinaryForgePin
    {
        $contents = @file_get_contents($this->dockerfile);
        if ($contents === false) {
            throw new \RuntimeException("Cannot read Dockerfile: {$this->dockerfile}");
        }

        return new BinaryForgePin(
            $this->arg($contents, 'OPENEMR_VERSION'),
            $this->arg($contents, 'BINARY_RELEASE_DATE'),
            $this->arg($contents, 'PHP_VERSION'),
        );
    }

    /**
     * Value of a top-level `ARG NAME=value` default.
     *
     * Deliberately narrow: it matches only the single-argument form with
     * an unquoted, whitespace-free default, which is what all four pins in
     * docker/binary/Dockerfile use. A multi-argument or quoted ARG would
     * not match and would surface as an explicit failure rather than a
     * silently wrong pin.
     */
    private function arg(string $contents, string $name): string
    {
        $pattern = '/^ARG\h+' . preg_quote($name, '/') . '=(\S+)\h*$/m';
        if (preg_match($pattern, $contents, $matches) !== 1) {
            throw new \RuntimeException("No `ARG {$name}=` default in {$this->dockerfile}");
        }

        return $matches[1];
    }
}
