<?php
/**
 * Intermediary bridge to Amendment data
 *
 * This class acts as a pseudo-controller for amendment data. Although the
 * Amendment Service could be accessed directly, it is better to interact with
 * it here. This has the benefit of fully separating business logic from data
 * manipulation (For example getList() does lots of manipulation that is not
 * appropriate for business-level logic). Another example is the usage of twig
 * and the HttpFoundation Request object.
 *
 * @package OpenEMR
 * @subpackage Amendment
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License 3
 */

namespace OpenEMR\Amendment;


use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Extension_Debug;
use OpenEMR\Amendment\Service\Amendment as Service;


class Amendment
{

    protected $twig;

    /** @var Service  */
    protected $service;

    public function __construct()
    {
        $viewFolder = dirname(__FILE__) . "/View";
        $loader = new Twig_Loader_Filesystem($viewFolder);
        $this->twig = new Twig_Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->service = new Service();
    }

    public function getItem($id)
    {
        var_dump($this->service->get($id));
    }

    public function getList($pid)
    {
        $rawList = $this->service->all($pid);
        $list = [];

        if (count($list) == 0) {
            // Display a different template here
        }

        // Preprocess elements and push to better key-named array
        foreach ($rawList as $amendmentItem) {
            $tmpList = [];
            foreach ($amendmentItem as $item => $value) {
                switch ($item) {
                    case 'amendment_id':
                        $tmpList['id'] = attr($value);
                        break;
                    case 'amendment_date':
                        $tmpList['date'] = oeFormatShortDate($value);
                        break;
                    case 'amendment_desc':
                        $tmpList['description'] = text($value);
                        break;
                    case 'amendment_by':
                        $tmpList['by'] = generate_display_field(['data_type' => 1, 'list_id' => 'amendment_from'], $value);
                        break;
                    case 'amendment_status':
                        $tmpList['status'] = generate_display_field(
                            ['data_type' => 1,
                                'list_id' => 'amendment_status'],
                            $value);
                        break;
                    default:
                        $tmpList[$item] = $value;
                        break;
                }
            }
            $list[] = $tmpList;
        }

        $viewArgs = [
            'assets_dir' => $GLOBALS['assets_static_relative'],
            'print' => xl('Print Amendments'),
            'add' => xl('Add'),
            'date' => xl('Date'),
            'title' => xl('List'),
            'description' => xlt('Request Description'),
            'by' => xlt('Requested By'),
            'status' => xlt('Request Status'),
            'list' => $list,
        ];

        echo $this->twig->render('list.html', $viewArgs);
    }
}
