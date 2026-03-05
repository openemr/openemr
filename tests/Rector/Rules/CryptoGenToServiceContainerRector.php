<?php

/**
 * Rector rule to replace new CryptoGen() with ServiceContainer::getCrypto()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rector\Rules;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoGen;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \OpenEMR\Rector\Rules\CryptoGenToServiceContainerRectorTest
 */
class CryptoGenToServiceContainerRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace new CryptoGen() with ServiceContainer::getCrypto()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use OpenEMR\Common\Crypto\CryptoGen;

$cryptoGen = new CryptoGen();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use OpenEMR\BC\ServiceContainer;

$cryptoGen = ServiceContainer::getCrypto();
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->class, CryptoGen::class)) {
            return null;
        }

        // Replace with ServiceContainer::getCrypto()
        return new StaticCall(
            new FullyQualified(ServiceContainer::class),
            new Identifier('getCrypto'),
        );
    }
}
