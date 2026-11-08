<?php

/**
 * ParsedRule - a schematron `<sch:rule>` in structured form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ParsedRule
{
    /**
     * @param list<ParsedAssertion|ParsedExtension> $items assertions and extends, in document order
     * @param array<string, string> $variables rule-scoped `<sch:let>`: name => defining XPath
     *                                         expression, evaluated against the rule's context
     *                                         node
     * @param array<string, string> $documentVariables schema- and pattern-scoped `<sch:let>`,
     *                                         merged with the innermost declaration winning.
     *                                         ISO/IEC 19757-3: a let that is not a child of a
     *                                         rule "is calculated with the context of the
     *                                         instance document root", so these resolve against
     *                                         the document, not the rule context. A rule-scoped
     *                                         name shadows a document-scoped one.
     */
    public function __construct(
        public bool $abstract,
        public ?string $context,
        public array $items,
        public array $variables = [],
        public array $documentVariables = [],
    ) {
    }
}
