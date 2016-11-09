<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:09
 */

class BaseController{

    const VIEW_FOLDER = 'therapy_groups_views';
    const MODEL_FOLDER = 'therapy_groups_models';


    /**
     * @param $template view name
     * @param array $data variables for injection into view
     */
    protected function loadView($template, $data = array()){

        $template = dirname(__FILE__) .'/../' . self::VIEW_FOLDER .'/'. $template .'.php';

        extract($data);

        ob_start();
        require($template);
        echo ob_get_clean();
        exit();
    }


    protected function loadModel($name)
    {
        if(!isset($this->$name)){
            require(dirname(__FILE__) .'/../' . self::MODEL_FOLDER .'/'. strtolower($name) .'_model.php');
            $this->$name = new $name;
        }

        return $this->$name;
    }
}