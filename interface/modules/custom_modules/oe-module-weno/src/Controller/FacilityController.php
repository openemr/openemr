<?php
/**
 * FacilityController.php
 * 
 * This file is responsible for providing necessary elements
 * to the facility.html.twig
 */

namespace OpenEMR\Modules\WenoModule\Controlllers;

use Twig\Environment;

class FacilityController  {

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderFacilities(){
        $data = [1];
        return $this->twig->render('templates/facilities.html.twig', $data);
    }
}
?>