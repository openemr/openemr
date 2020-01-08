#Multiple Databases Module

This module allows you to have multiple databases that your zend modules can read / write to.  This can be used for clustering, single master, multi-read strategy, sharding, or aggregating multiple databases.  

Each database
that you want to read to / write from must be associated with a unique namespace name.  For example if you were clustering
your application with a write master and a read slave you could create two entries.

*Entry One*
Namespace: OpenEMR\Modules\Db\WriteAdapter
Username: writeuser
Database name: openemr
Password: openemr
Host: write.db.someexample.com
Port: 3306

*Entry Two*
Namespace: OpenEMR\Modules\Db\ReadAdapter
Username: readuser
Database name: openemr
Password: openemr
Host: read.db.someexample.com
Port: 3306

Then you can access these named adapters in your code by injecting them in your service factories:
```
// inside of module.config.php
 'service_manager' => [
        'factories' => array(
            SomeModuleClass::class =>  function(ContainerInterface $container, $requestedName) {
                // note we don't use the ::class modifier here as the class may not actually exist but is a placeholder.
                $dbReader = $container->get('OpenEMR\Modules\Db\ReadAdapter'); 
                $dbWriter = $container->get('OpenEMR\Modules\Db\WriteAdapter'); 
                /**
                 * the constructor for SomeModuleClass would have the signature of
                 * public function __construct(Laminas\Db\Adapter\Adapter $dbReader, Laminas\Db\Adapter\Adapter $dbWriter) {}
                 */
                return new SomeModuleClass($dbReader, $dbWriter);
            }
        ),
]
```

Note that if you need your module code to be able to deal with dynamic names that are resolved at runtime you will need to inject
the ContainerInterface into your class instead of the database adapters.  This limits the testability of your service/class but gives you runtime resolution if that's required.

You must enable the Multiple databases flag in the OpenEMR globals settings in order to use this feature.
You can do this by editing Administration > Globals > Security > Allow multiple databases.

This module leverages the named adapter's functionality of the Laminas-DB module.  You can learn more about it here: https://docs.zendframework.com/tutorials/db-adapter/

The injection of the database adapters happens in the oemr_install/interface/modules/zend_modules/config/autoload/global.php file.  This is the last step before the zend application / modules are executed.  It will override all prior service factory configuration so if a Namespace name is added that conflicts with an existing module Namespace class it WILL replace and overwrite it.  This could lead to unexpected or fatal system behavior if the database adapter namespaces are not named carefully.