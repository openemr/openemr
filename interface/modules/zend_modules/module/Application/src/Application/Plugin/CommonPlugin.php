<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @author    Chandni Babu <chandnib@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;
use Interop\Container\ContainerInterface;

class CommonPlugin extends AbstractPlugin
{
    protected $application;

    /**
     * Application Table Object
     * Listener Object
     *
     * @param type $container ContainerInterface
     */
    public function __construct(ContainerInterface $container)
    {
        // TODO: this is crazy... why do we grab the service locator so we can load the db adapter?
        // is there some db related state that is being loaded here in a global type of way that we aren't aware of?? Or can we just remove this line?
        $container->get('Laminas\Db\Adapter\Adapter');
        $this->application = new ApplicationTable();
        $this->listenerObject = new Listener();
    }

    /**
     * Function checkACL
     * Plugin functions are easily access from any where in the project
     * Call the ACL Check function zAclCheck from ApplicationTable
     *
     * @param int    $useID
     * @param string $sectionID
     * @return type
     */
    public function checkACL($useID, $sectionID)
    {
        return $this->application->zAclCheck($useID, $sectionID);
    }

    /**
     * Keyword color hightlight (primary keyword and secondary)
     * ? - The question mark used for omit the error.
     * Error occur in second word of the search keyword,
     * if maches any of the letter in the html element
     */
    public function hightlight($str, $keywords = '')
    {

        $keywords = preg_replace('/\s\s+/', ' ', strip_tags(trim($keywords)));
        $style = '???';
        $style_i = 'highlight_i';
        $var = '';
        foreach (explode(' ', $keywords) as $keyword) {
            $replacement = "<?? ?='" . $style . "'>" . trim($keyword) . "</??>";
            $var .= $replacement . " ";
            $str = str_ireplace($keyword, $replacement, $str);
        }

        $str = str_ireplace(rtrim($var), "<?? ?='" . $style_i . "'>" . trim($keywords) . "</??>", $str);
        $str = str_ireplace('???', 'highlight_i', $str);
        $str = str_ireplace('??', 'span', $str);
        $str = str_ireplace('?', 'class', $str);
        return $str;
    }

    public function date_format($date, $output_format, $input_format)
    {
        $this->application = new ApplicationTable();
        $date_formatted = $this->application->fixDate($date, $output_format, $input_format);
        return $date_formatted;
    }

    public static function escapeLimit($val)
    {
        return escape_limit($val);
    }

    /*
  * Insert the imprted data to audit master table
  *
  * @param    var   Array   Details parsed from the CCR xml file
  * @return   audit_master_id   Integer   ID from audit_master table
  */
    public static function insert_ccr_into_audit_data($var, $isQrdaDocument = false, $isUnstructeredDocument = false)
    {
        $appTable = new ApplicationTable();
        $audit_master_id_to_delete = $var['audit_master_id_to_delete'] ?? null;
        $approval_status = $var['approval_status'];
        $type = $var['type'];
        $ip_address = $var['ip_address'];
        $field_name_value_array = $var['field_name_value_array'];
        $entry_identification_array = $var['entry_identification_array'];

        if ($audit_master_id_to_delete) {
            $qry = "DELETE from audit_details WHERE audit_master_id=?";
            $appTable->zQuery($qry, array($audit_master_id_to_delete));

            $qry = "DELETE from audit_master WHERE id=?";
            $appTable->zQuery($qry, array($audit_master_id_to_delete));
        }

        $master_query = "INSERT INTO audit_master SET pid = ?,approval_status = ?,ip_address = ?,type = ?, is_qrda_document = ?, is_unstructured_document = ?";
        $result = $appTable->zQuery($master_query, array(0, $approval_status, $ip_address, $type, $isQrdaDocument, $isUnstructeredDocument));
        $audit_master_id = $result->getGeneratedValue();
        $detail_query = "INSERT INTO `audit_details` (`table_name`, `field_name`, `field_value`, `audit_master_id`, `entry_identification`) VALUES ";
        $detail_query_array = array();
        foreach ($field_name_value_array as $key => $val) {
            foreach ($field_name_value_array[$key] as $cnt => $field_details) {
                foreach ($field_details as $field_name => $field_value) {
                    $detail_query .= "(? ,? ,? ,? ,?),";
                    $detail_query_array[] = $key;
                    $detail_query_array[] = trim($field_name);
                    if (is_array($field_value)) {
                        if (!empty($field_value['status']) || !empty($field_value['enddate'])) {
                            $detail_query_array[] = trim($field_value['value'] ?? '') . "|" . trim($field_value['status'] ?? '') . "|" . trim($field_value['begdate'] ?? '');
                        } elseif (stripos($field_name, 'encounter_diagnosis') !== false) {
                            $detail_query_array[] = trim(implode('|', $field_value));
                        } else {
                            $detail_query_array[] = trim($field_value['value'] ?? '');
                        }
                    } else {
                        $detail_query_array[] = trim($field_value);
                    }

                    $detail_query_array[] = $audit_master_id;
                    $detail_query_array[] = trim($entry_identification_array[$key][$cnt] ?? '');
                }
            }
        }

        $detail_query = substr($detail_query, 0, -1);
        $detail_query = $detail_query . ';';
        $appTable->zQuery($detail_query, $detail_query_array);
        return $audit_master_id;
    }

    public function getList($list_id, $selected = '', $opt = '')
    {
        $appTable = new ApplicationTable();
        $this->listenerObject = new Listener();
        $res = $appTable->zQuery("SELECT * FROM list_options WHERE list_id=? ORDER BY seq, title", array($list_id));
        $i = 0;
        if ($opt == 'search') {
            $rows[$i] = array(
                'value' => 'all',
                'label' => $this->listenerObject->z_xlt('All'),
                'selected' => true,
            );
            $i++;
        } elseif ($opt == '') {
            $rows[$i] = array(
                'value' => '',
                'label' => $this->listenerObject->z_xlt('Unassigned'),
                'disabled' => false
            );
            $i++;
        }

        foreach ($res as $row) {
            $sel = ($row['option_id'] == $selected) ? true : false;
            $rows[$i] = array(
                'value' => htmlspecialchars($row['option_id'], ENT_QUOTES),
                'label' => $this->listenerObject->z_xlt($row['title']),
                'selected' => $sel,
            );
            $i++;
        }

        return $rows;
    }

    /*
    * $this->escapeHtml() cannot be used in any files other than view.
    * This function will enable a user to use escapeHtml in any files like controller model etc.
    */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    public function getListtitle($listId, $listOptionId)
    {
        $appTable = new ApplicationTable();
        $sql = "SELECT title FROM list_options WHERE list_id = ? AND option_id = ? ";
        $result = $appTable->zQuery($sql, array($listId, $listOptionId));
        $row = $result->current();
        $return = xl_list_label($row['title']);
        return $return;
    }
}
