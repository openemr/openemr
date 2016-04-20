<?php
/**
 * This class enable to load JS and CSS files to all the system pages.
 * This is good feature for different group that want to make changes in the system views without touching system code.
 * @author Amiel <amielel@matrix.co.il>
 */

class Load_assets{

    private static $assets_instance = null;

    private $assets_folder = null;
    private $relative_path = null;

    private $css_files = array();
    private $js_files = array();

    private $assets_html = null;

    /**
     * Returns the *Singleton* instance of this Load assets class.
     * @return Singleton The *Singleton* instance.
     */
    public static function get_assets_instance($folder)
    {
        if (is_null(self::$assets_instance)) {
            self::$assets_instance = new Load_assets($folder);
        }
        return self::$assets_instance;
    }


    /**
     * constructor - set full path to folder in $this->assets_folder property.
     * Write error if folder does'nt open.
     * @param (string) relative path to folder
     */
    public function __construct($folder)
    {
        if(is_null($this->assets_folder)) {
            $this->assets_folder = $GLOBALS['fileroot'] .'/' . $folder;
            $this->relative_path = $folder;

            if (!is_dir($this->assets_folder)) {
                //Write message in error log if folder doesn't exist
                error_log("Load_assets: Unable to open folder " . text( $folder ) . " for reading");
                //set empty string to disable errors
                $this->assets_html = "<!-- Load_assets error: Unable to open folder " . text( $folder ) . " for reading -->";
            }
        }
    }


    /**
     * load all js and css files from specific folder.
     * @param (string) full path to folder
     * @return $this
     */
    public function load_folder($folder = null)
    {
        // return if folder was loaded before
        if (!is_null($this->assets_html)) {
            return $this;
        }

        $directory = new RecursiveDirectoryIterator($this->assets_folder);
        $Iterator_directory = new RecursiveIteratorIterator($directory);
        //select only css or js files
        $Iterator_directory = new RegexIterator($Iterator_directory, '/^.+\.js|^.+\.css$/i', RecursiveRegexIterator::GET_MATCH);

        $Iterator_directory->rewind();
        while($Iterator_directory->valid()) {

            if (!$Iterator_directory->isDot()) {
                if (is_file($Iterator_directory->key())) {
                    $this->load_file($GLOBALS['webroot'] . '/' . $this->relative_path . '/' . $Iterator_directory->getSubPathName());
                }
            }
            $Iterator_directory->next();
        }
        return $this;
    }


    /**
     * Check type of file and add to files list
     * @param (string) full path to file
     * @return {void}
     * */
    public function load_file($file)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if ($ext === "css") {
            $this->css_files[] = $file;
        } elseif($ext === 'js') {
            $this->js_files[] = $file;
        }
    }

    /**
     * Return block of html script for head's page
     * @return (string) HTML for html file head section
     *
     */
    public function get_html()
    {

        // return html if folder was loaded before
        if (!is_null($this->assets_html)) {
            return $this->assets_html;
        }

        sort($this->css_files);
        sort($this->js_files);

        $head_script = "";
        //add comment before css files
        if ( count($this->css_files) > 0) {
            $head_script .= "<!-- Custom assets - css files -->\n";
        }
        foreach ($this->css_files as $key => $css_file) {
            //add css file
            $head_script .= "<link rel='stylesheet' type='text/css' href=' ". $css_file ."' />\n";
            if($key == count($this->css_files) -1)$head_script .= "\n";
        }
        //add comment before js files
        if (count($this->js_files) > 0) {
            $head_script .= "<!-- Custom assets - js files -->\n";
        }

        foreach ($this->js_files as $key => $js_file) {
            //add js file
            $head_script .= "<script type='text/javascript'' src='" . $js_file . "'></script>\n";
            if ($key == count($this->js_files) -1) {
                $head_script .= "\n";
            }
        }

        if ($head_script != "") {
            $head_script .= "\n";
        }

        //save html block in property
        $this->assets_html = $head_script;
        //remove files array
        unset($this->css_files, $this->js_files);

        return $head_script;
    }
}




