<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\OpenemrAudio2Note\Form;

use OpenEMR\Modules\BaseModuleConfigForm;
use OpenEMR\Modules\OpenemrAudio2Note\ModuleConfig; // This class is expected to define module settings.

class ModuleconfigForm extends BaseModuleConfigForm
{
    public function init()
    {
        // Initialize form elements based on the ModuleConfig class.
        // This class should provide the structure for the module's configuration settings.
        $moduleConfig = new ModuleConfig();
        $settings = $moduleConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $this->add([
                'name' => $key,
                'type' => $config['type'], // e.g., 'text', 'select'
                'options' => [
                    'label' => $config['title'], // User-friendly label for the setting
                    'description' => $config['description'], // Help text for the setting
                ],
                'attributes' => [
                    'value' => $config['default'], // Default value for the setting
                    'required' => $config['required'] ?? false, // Whether the setting is mandatory
                ],
            ]);
        }
    }
}