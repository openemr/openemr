<?php

/**
 * Pin docker/production/docker-compose.yml's openemr image from
 * `latest@sha256:...` to `<version>@sha256:...`. The published image's
 * digest is supplied via --image-digest by the conductor workflow after
 * the release image has been built and pushed; if absent the existing
 * digest is preserved and only the tag is swapped.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class DockerComposeProductionMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'docker/production/docker-compose.yml';

    public function name(): string
    {
        return 'docker/production/docker-compose.yml (pin openemr image)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $version = $context->versionString();
        // Accept the digest with or without the `sha256:` prefix; we always
        // emit `@sha256:<hex>` regardless.
        $newDigestHex = $context->imageDigest === null
            ? null
            : preg_replace('/^sha256:/', '', $context->imageDigest);

        // Pattern matches `image: openemr/openemr:<tag>@sha256:<digest>`
        // where <tag> may be `latest`, the target version, or another
        // version (idempotence + handles re-runs after a digest update).
        $pattern = '/(image:\s*openemr\/openemr:)([^@\s]+)(@sha256:)([0-9a-f]{64})/';
        $updated = preg_replace_callback(
            $pattern,
            static function (array $match) use ($version, $newDigestHex): string {
                $digest = $newDigestHex ?? $match[4];
                return $match[1] . $version . $match[3] . $digest;
            },
            $contents,
            1,
            $count,
        );
        if ($updated === null) {
            throw new \RuntimeException('preg_replace_callback failed for docker-compose.yml');
        }
        if ($count === 0) {
            throw new \RuntimeException(
                'Expected an `image: openemr/openemr:<tag>@sha256:<digest>` line in docker-compose.yml',
            );
        }
        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        // Belt-and-suspenders: regex substitution preserves formatting
        // but doesn't know YAML syntax. Parse the result before writing
        // so a pathological input that produces invalid YAML fails the
        // mutator rather than ships a broken file.
        try {
            Yaml::parse($updated);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                'docker-compose.yml: regex substitution produced invalid YAML',
                0,
                $e,
            );
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        $messages = $newDigestHex === null
            ? ['docker-compose.yml: tag pinned to ' . $version . '; digest preserved (no --image-digest provided)']
            : [];
        return new MutatorResult([self::RELATIVE_PATH], $messages);
    }
}
