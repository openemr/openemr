<?php
/**
 * OpenEMR (http://open-emr.org)
 *
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License, version 3
 */

namespace OpenEMR\Amendment;

require_once dirname(__FILE__) . "/../../interface/globals.php";

use Twig_Environment;
use Twig_Loader_Filesystem;
use OpenEMR\Amendment\Service\Amendment as Service;
use Symfony\Component\HttpFoundation\Request;

/**
 * Intermediary bridge to Amendment data.
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
 */
class Amendment
{

    /** @var Twig_Environment */
    protected $twig;

    /** @var Service  */
    protected $service;

    protected $table = 'amendments';

    /**
     * Amendment constructor.
     * @param Twig_Loader_Filesystem $loader
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Loader_Filesystem $loader, Twig_Environment $twig)
    {
        $viewFolder = dirname(__FILE__) . "/Resources/views";
        $loader->addPath($viewFolder);
        $this->twig = $twig;
        $this->service = new Service();
    }

    public function save(Request $request)
    {
        global $pid;
        $user = $_SESSION['authUserID'];
        $time = date('Y-m-d H:i');
        $parameters = [
            "amendment_date" => DateToYYYYMMDD($request->get('date', "")),
            "amendment_by" => $request->get('by', ""),
            "amendment_status" => $request->get('status', ""),
            "pid" => $pid,
            "amendment_desc" => $request->get('desc', ""),
            "created_by" => $user,
            "created_time" => $time,
        ];
        $this->putRecord('INSERT', $parameters);
    }

    public function getItem($id)
    {
        var_dump($this->service->get($id));
    }

    /**
     * Render an twig template of the list of amendments for this patient.
     *
     * Create easier twig variables here.
     * @param $pid integer
     */
    public function getList($pid)
    {
        $rawList = $this->service->all($pid);
        $list = [];

        // Preprocess elements and push to better key-named array
        foreach ($rawList as $amendmentItem) {
            $list[] = $this->parseAmendment($amendmentItem);
        }

        $requested_by_select = generate_select_list("by",
            "amendment_from",
            "",
            "Amendment Request By",
            "", "form-control", "", "", "");

        $status = generate_select_list(
            "status",
            "amendment_status",
            "",
            'Amendment Status',
            ' ','','','','');


        $viewArgs = [
            'list' => $list,
            'add' => [
                'requested_by_selectbox' => $requested_by_select,
                'status_selectbox' => $status,
            ]
        ];

        echo $this->twig->render('amendment.html.twig', $viewArgs);
    }

    /**
     * Parse amendment data for easier twig variables.
     * @param array $amendment
     * @return array Parsed amendment
     */
    private function parseAmendment(array $amendment)
    {
        $parsed = [];
        foreach ($amendment as $item => $value) {
            switch ($item) {
                case 'amendment_id':
                    $parsed['id'] = attr($value);
                    break;
                case 'amendment_date':
                    $parsed['date'] = oeFormatShortDate($value);
                    break;
                case 'amendment_desc':
                    $parsed['description'] = text($value);
                    break;
                case 'amendment_by':
                    $displayArray = ['data_type' => 1, 'list_id' => 'amendment_from'];
                    $parsed['by'] = generate_display_field($displayArray, $value);
                    break;
                case 'amendment_status':
                    $displayArray = ['data_type' => 1, 'list_id' => 'amendment_status'];
                    $parsed['status'] = generate_display_field($displayArray, $value);
                    break;
                default:
                    $parsed[$item] = $value;
                    break;
            }
        }
        return $parsed;
    }

    private function getTable()
    {
        return $this->table;
    }

    private function putRecord($type, $params)
    {
        $allowedTypes = ['INSERT', 'UPDATE'];
        $type = strtoupper($type);
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        $type = ($type == "INSERT") ? "INSERT INTO" : $type;

        $setStr = "";
        $bindings = [];
        foreach ($params as $col => $val) {
            if ($type == "UPDATE" && $col == "amendment_id") {
                continue;
            }
            $setStr .= "{$col} = ?, ";
            $bindings[] = $val;
        }
        $setStr = substr($setStr, 0, (strlen($setStr) - 2));

        $sql = "{$type} {$this->getTable()} SET {$setStr}";

        if ($type == "UPDATE") {
            $bindings[] = $params['amendment_id'];
            $sql .= "WHERE amendment_id = ?";
        }

        return sqlInsert($sql, $bindings);
    }
}
