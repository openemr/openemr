<?php

/**
 * Pin docker/production/docker-compose.yml's openemr image from
 * `latest[@sha256:...]` to `<version>` (tag only, no digest).
 *
 * At release-prep time, the release-tagged image does not yet exist in
 * Docker Hub — it's produced downstream only AFTER the release-prep PR
 * merges (which triggers the tag creation which triggers the image
 * build). So there's no valid digest to pin at this stage. The old
 * `--image-digest` pathway assumed an out-of-band digest lookup that
 * doesn't fit the actual sequencing; drop it and just swap the tag.
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
        return 'docker/production/docker-compose.yml (pin openemr image tag)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $version = $context->versionString();

        // Pattern matches `image: openemr/openemr:<tag>` with an optional
        // `@sha256:<digest>` suffix. The tag may be `latest`, the target
        // version, or another version (idempotence + handles re-runs).
        // The digest is optional because rel-* branches that were cut
        // before the digest-pinning automation existed start with a bare
        // tag (e.g. `openemr/openemr:8.1.0`). Whatever's after `:`, we
        // replace it with the target version and strip the digest entirely.
        $pattern = '/(image:\s*openemr\/openemr:)([^@\s]+)(?:@sha256:[0-9a-f]{64})?/';
        $updated = preg_replace_callback(
            $pattern,
            static fn (array $match): string => $match[1] . $version,
            $contents,
            1,
            $count,
        );
        if ($updated === null) {
            throw new \RuntimeException('preg_replace_callback failed for docker-compose.yml');
        }
        if ($count === 0) {
            throw new \RuntimeException(
                'Expected an `image: openemr/openemr:<tag>` line in docker-compose.yml',
            );
        }
        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        // Validate beyond well-formedness: the regex preserves formatting
        // but doesn't know YAML structure. Parse the result and assert
        // that services.openemr.image is exactly the value we intended
        // to write. This catches both invalid YAML and the case where
        // the regex matched something that looked right but wasn't
        // actually the openemr image entry.
        try {
            $parsed = Yaml::parse($updated);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                'docker-compose.yml: regex substitution produced invalid YAML',
                0,
                $e,
            );
        }
        $expectedImage = sprintf('openemr/openemr:%s', $version);
        $actualImage = is_array($parsed)
            && is_array($parsed['services'] ?? null)
            && is_array($parsed['services']['openemr'] ?? null)
            ? ($parsed['services']['openemr']['image'] ?? null)
            : null;
        if ($actualImage !== $expectedImage) {
            throw new \RuntimeException(sprintf(
                'docker-compose.yml: post-mutation services.openemr.image was %s; expected %s',
                var_export($actualImage, true),
                $expectedImage,
            ));
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult(
            [self::RELATIVE_PATH],
            ['docker-compose.yml: tag pinned to ' . $version . ' (no digest — release image is not yet built at release-prep time)'],
        );
    }
}
