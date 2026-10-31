<?php

/**
 * Isolated tests for the Send To view helper (Care Coordination, Immunization
 * and Syndromic Surveillance pages).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Application\Helper;

use Application\Controller\SendtoController;
use Application\Helper\SendToHieHelper;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class SendToHieHelperTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $application = __DIR__ . '/../../../../../interface/modules/zend_modules/module/Application/src/Application';
        require_once $application . '/Controller/SendtoController.php';
        require_once $application . '/Helper/SendToHieHelper.php';
    }

    /**
     * The layout each page passes, from its own view.
     *
     * @return array<string, array{string}>
     */
    public static function callerLayouts(): array
    {
        return [
            'Care Coordination' => ['carecoordination/layout/encountermanager'],
            'Immunization' => ['immunization/layout/layout'],
            'Syndromic Surveillance' => ['syndromicsurveillance/layout/layout'],
        ];
    }

    #[DataProvider('callerLayouts')]
    public function testTheWidgetKeepsTheCallersLayout(string $layout): void
    {
        $controller = $this->createMock(SendtoController::class);
        $controller->method('sendAction')->willReturn(new ViewModel(['required_butons' => null]));
        $view = new class implements RendererInterface {
            /** @var list<string> */
            public array $layouts = [];
            /** @var list<array{string, array<string, mixed>}> */
            public array $partials = [];

            public function layout(string $template): void
            {
                $this->layouts[] = $template;
            }

            /** @param array<string, mixed> $values */
            public function partial(string $name, array $values): string
            {
                $this->partials[] = [$name, $values];
                return '<div>send to</div>';
            }

            public function getEngine(): self
            {
                return $this;
            }

            public function setResolver(ResolverInterface $resolver): self
            {
                return $this;
            }

            /**
             * @param mixed $nameOrModel
             * @param mixed $values
             */
            public function render($nameOrModel, $values = null): string
            {
                return '';
            }
        };
        // The view as Laminas would inject it, without the deprecated setter.
        $helper = new class ($controller, $view) extends SendToHieHelper {
            public function __construct(SendtoController $sendController, private readonly RendererInterface $renderer)
            {
                parent::__construct($sendController);
            }

            public function getView(): RendererInterface
            {
                return $this->renderer;
            }
        };

        ob_start();
        $returned = $helper($layout, ['download'], 'hie', ['hl7_format']);
        $echoed = ob_get_clean();

        $this->assertSame([$layout], $view->layouts);
        $this->assertCount(1, $view->partials);
        [$partial, $values] = $view->partials[0];
        $this->assertSame('application/sendto/send', $partial);
        $this->assertSame(['download'], $values['required_butons']);
        $this->assertSame('hie', $values['send_via']);
        $this->assertSame(['hl7_format'], $values['download_format']);
        $this->assertSame('<div>send to</div>', $echoed);
        $this->assertSame('', $returned);
    }
}
