<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Routing\Fixture;

use Laminas\View\Model\ModelInterface;

/**
 * Builds JsonModel instances whose variables container is a Traversable or a
 * bare ArrayAccess, exercising the two non-array branches of
 * ZendModelResponder::modelVariables().
 *
 * The model's own setVariables() rejects a non-Traversable ArrayAccess, so the
 * bare-ArrayAccess state is only reachable by setting the container directly via
 * reflection. The JsonModel itself comes from the real canary controller's
 * indexAction() (which returns new JsonModel([])) rather than being named here,
 * so the deprecated class never appears in the test source — the same reason the
 * other routing fixtures load the real controller instead of naming JsonModel.
 */
final class JsonModelStateFactory
{
    public static function withTraversableVariables(): ModelInterface
    {
        return self::withVariables(new \ArrayObject(['alpha' => 1, 'beta' => 2]));
    }

    public static function withArrayAccessOnlyVariables(): ModelInterface
    {
        $variables = new class implements \ArrayAccess {
            public function offsetExists(mixed $offset): bool
            {
                return false;
            }

            public function offsetGet(mixed $offset): mixed
            {
                return null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
            }

            public function offsetUnset(mixed $offset): void
            {
            }
        };

        return self::withVariables($variables);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param \ArrayAccess<TKey, TValue> $variables
     */
    private static function withVariables(\ArrayAccess $variables): ModelInterface
    {
        // The canary's indexAction() returns a real JsonModel (a ModelInterface),
        // so the deprecated class is never named here. Its variables container is
        // then swapped via reflection because setVariables() rejects a
        // non-Traversable ArrayAccess.
        $model = CanaryControllerFactory::create()->indexAction();
        (new \ReflectionProperty($model, 'variables'))->setValue($model, $variables);

        return $model;
    }
}
