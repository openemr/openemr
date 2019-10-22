<?php
/**
 * Financial Report controller class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FinancialReports;   //This will function as my controller event though not named controller

//use OpenEMR\Common\Database\Connector;

use OpenEMR\Core\Controller;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\HeaderExtension;

class FinancialSummaryByInsuranceController extends Controller
{
    /**
     * The user repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * FinancialSummaryByInsuranceController constructor.
     */
    public function __construct()
    {

        /*
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\ArSession');
        */
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function insurancepaid()
    {
        $loader = new FilesystemLoader('../../templates/financialreports/insurance');
        $twig = new Environment($loader, [
            'cache' => 'C:\tempt',
        ]);
        $twig->addExtension(new HeaderExtension());

        return $twig->render('summaryinsurancepaid.html.twig', [
            'name' => 'Fabien Roger'

        ]);
    }
}
