<?php

/**
 * Bump the version on the `#[OA\Info(...)]` attribute in
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

use OpenEMR\Common\Command\ReleasePrep\AstSourceEditor;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar;
use PhpParser\NodeFinder;

final readonly class OpenApiVersionMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'src/RestControllers/OpenApi/OpenApiDefinitions.php';

    public function name(): string
    {
        return self::RELATIVE_PATH . ' (OA\\Info version)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $version = $context->versionString();
        $editor = new AstSourceEditor();
        $updated = $editor->edit($contents, function (array $ast) use ($version): array {
            $finder = new NodeFinder();
            $infoAttribute = $finder->findFirst($ast, fn(Node $node): bool => $node instanceof Node\Attribute
                && $node->name->toString() === 'OA\\Info');
            if (!$infoAttribute instanceof Node\Attribute) {
                throw new \RuntimeException(
                    "Did not find #[OA\\Info(...)] attribute in OpenApiDefinitions.php",
                );
            }
            foreach ($infoAttribute->args as $arg) {
                if (
                    $arg->name instanceof Identifier
                    && $arg->name->name === 'version'
                    && $arg->value instanceof Scalar\String_
                ) {
                    return [[
                        $arg->value->getStartFilePos(),
                        $arg->value->getEndFilePos(),
                        "'" . $version . "'",
                    ]];
                }
            }
            throw new \RuntimeException(
                "OA\\Info attribute has no `version:` argument",
            );
        });

        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
