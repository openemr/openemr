<?php

/**
 * interface/therapy_groups/therapy_groups_controllers/base_controller.php contains the base controller for therapy groups.
 *
 * This is the base controller from which all therapy group controllers inherit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;

class BaseController
{
    const VIEW_FOLDER = 'therapy_groups_views';
    const MODEL_FOLDER = 'therapy_groups_models';

    public function __construct(protected readonly Request $request)
    {
    }

    /**
     * @param $template view name
     * @param array $data variables for injection into view
     */
    protected function loadView($template, $data = []): never
    {

        $template = __DIR__ . '/../' . self::VIEW_FOLDER . '/' . $template . '.php';

        extract($data);

        ob_start();
        require($template);
        echo ob_get_clean();
        exit();
    }


    protected function loadModel($name)
    {
        if (!isset($this->$name)) {
            require(__DIR__ . '/../' . self::MODEL_FOLDER . '/' . strtolower((string) $name) . '_model.php');
            $this->$name = new $name();
        }

        return $this->$name;
    }
}
