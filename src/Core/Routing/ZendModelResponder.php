<?php

/**
 * Maps a legacy Laminas action result into a Symfony HTTP response.
 *
 * Legacy zend_module actions return one of:
 *   - JsonModel  -> JSON response (the model's own variables, serialized)
 *   - ViewModel  -> rendered HTML (delegated to an injected renderer)
 *   - Response   -> passed through unchanged
 *   - string     -> plain HTML response
 *
 * This isolates the 55 ViewModel/JsonModel return sites behind one converter so
 * the resolver shim does not grow Laminas-view coupling.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Routing;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class ZendModelResponder
{
    /**
     * @param \Closure(ViewModel): string $viewRenderer renders a ViewModel to
     *        HTML. Injected so the heavy Laminas view stack stays out of unit
     *        tests and the seam can later swap in Twig.
     */
    public function __construct(
        private \Closure $viewRenderer,
    ) {
    }

    public function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        // JsonModel must be checked before ViewModel: JsonModel extends
        // ViewModel, so the order determines that JSON results serialize as JSON
        // rather than falling into the HTML renderer.
        if ($result instanceof JsonModel) {
            return new JsonResponse($this->modelVariables($result));
        }

        if ($result instanceof ViewModel) {
            return new Response(($this->viewRenderer)($result));
        }

        if (is_string($result)) {
            return new Response($result);
        }

        throw new \RuntimeException('Unsupported zend module action result type');
    }

    /**
     * @return array<array-key, mixed>
     */
    private function modelVariables(ModelInterface $model): array
    {
        $variables = $model->getVariables();
        if (is_array($variables)) {
            return $variables;
        }
        if ($variables instanceof \Traversable) {
            return iterator_to_array($variables);
        }

        // ArrayAccess without Traversable carries no enumerable keys.
        return [];
    }
}
