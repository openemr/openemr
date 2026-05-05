<?php

/**
 * Bump the `OA\Info(title:, version:)` attribute in
 * src/RestControllers/OpenApi/OpenApiDefinitions.php so the generated
 * swagger/openemr-api.yaml advertises the new release version. The
 * SwaggerRegenMutator then re-emits the YAML from this constant.
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

final readonly class OpenApiVersionMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'src/RestControllers/OpenApi/OpenApiDefinitions.php';

    public function name(): string
    {
        return self::RELATIVE_PATH . ' (OA\Info version)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $version = $context->versionString();
        $pattern = "/(#\[OA\\\\Info\\(title:\\s*'OpenEMR API',\\s*version:\\s*)'\\d+\\.\\d+\\.\\d+'/";
        $updated = preg_replace($pattern, "$1'" . $version . "'", $contents, 1, $count);
        if ($updated === null) {
            throw new \RuntimeException('preg_replace failed for OpenApiDefinitions.php');
        }
        if ($count === 0) {
            throw new \RuntimeException(
                "Expected #[OA\\Info(title: 'OpenEMR API', version: '...')] in OpenApiDefinitions.php",
            );
        }
        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
