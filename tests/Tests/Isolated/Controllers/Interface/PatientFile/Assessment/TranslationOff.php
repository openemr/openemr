<?php

/**
 * Turns translation off for a test, so xl() returns the source string without the
 * translation tables and assertions can name the English text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\Core\OEGlobalsBag;

trait TranslationOff
{
    /** @var array{present: bool, value: bool} */
    private array $translationSetting = ['present' => false, 'value' => false];

    private function turnTranslationOff(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->translationSetting = [
            'present' => $globals->has('disable_translation'),
            'value' => $globals->getBoolean('disable_translation'),
        ];
        $globals->set('disable_translation', true);
    }

    /**
     * Put disable_translation back the way the suite had it.
     */
    private function restoreTranslation(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->translationSetting['present']) {
            $globals->set('disable_translation', $this->translationSetting['value']);
            return;
        }
        $globals->remove('disable_translation');
    }
}
