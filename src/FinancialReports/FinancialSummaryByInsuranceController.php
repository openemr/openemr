<?php
/**
 * Financial Report controller class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FinancialReports;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Core\Controller;
use OpenEMR\Entities\ArSession;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\HeaderExtension;


/**
 * Class FinancialSummaryByInsuranceController
 * @package OpenEMR\FinancialReports
 */
class FinancialSummaryByInsuranceController extends Controller
{
    private $entityManager;

    private $repository;

    /**
     * FinancialSummaryByInsuranceController constructor.
     *
     */
    public function __construct()
    {
        $database = Connector::Instance();
        $entityManger = $database->entityManager;
        $this->repository = $entityManger->getRepository(ArSession::class);
    }


    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function insurancepaid()
    {
        $payer_id = 2;
        $list = self::getpaidata($payer_id);
        $loader = new FilesystemLoader('../../templates/financialreports/insurance');
        $twig = new Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension( new HeaderExtension());
        $twig->addExtension(new DebugExtension());

        return $twig->render('summaryinsurancepaid.html.twig', [
            'payments' => $list,
        ]);
    }


    /**
     * database query return multidimensional array
     * @param $payer_id
     * @return mixed
     */
    public function getpaidata($payer_id)
    {
        $row = $this->repository;
        $responses =  $row->getInsurerPaid($payer_id);
        $result = [];
        foreach ($responses as $response) {
            $result[] = $response;
        }
        return $result;

    }

}
