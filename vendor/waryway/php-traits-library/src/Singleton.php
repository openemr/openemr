<?php
namespace Waryway\PhpTraitsLibrary;
 
trait Singleton
{
    /*
     * Hold an instance of the class
     */ 
    private static $instances = array();
   
    /**
     * A private constructor; prevents direct creation of object
     */
    final private function __construct()
    {
        self::initialize();
    }
    
    /**
     * Potential override to allow instances variables / initialization settings.
     */
    protected function initialize() {
        // If it is necessary to initialize a singleton with some specific values - this method can be overridden to that end.
    }

    /**
     * Prevent overrides of the clone, and cloning to begin with.
     */
    final public function __clone()
    {
        trigger_error( 'Cloning is forbidden.', E_USER_ERROR );
    }
   
    /**
     * Grab an instance of the singleton.
     * 
     * @return self
     */
    final public static function instance() {
        $c = get_called_class();
       
        if( ! isset( self::$instances[$c] ) )
        {
            self::$instances[$c] = new $c;
        }
       
        return self::$instances[$c];
    }
}
?>