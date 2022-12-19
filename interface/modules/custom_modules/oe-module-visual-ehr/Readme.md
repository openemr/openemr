# OpenEMR Custom Module Skeleton Starter Project
This is a sample module project that developers can clone and use to create their own custom modules inside 
the OpenEMR codebase.  These modules leverage the oe-module-install-plugin which installs the custom module
into the OpenEMR custom module installation folder.

The project has sample code that demostrates adding your module to the menu system, creating global settings,
and adding a rest api endpoint.

There are a limited number of events currently in the OpenEMR codebase, as we continue to add support for 
module writers we will add more events to the codebase.  If there is a place in the core codebase you would 
like your custom module to connect to please file an issue at [https://github.com/openemr/openemr](https://github.com/openemr/openemr)

## Getting Started
You can start by cloning the project.  When developing modules the best initial location would be to clone the directory
inside the OpenEMR custom modules location.  This is at *<openemr_installation_directory>//interface/modules/custom_modules/*
```git
git clone https://github.com/adunsulag/oe-module-custom-skeleton <your-project-name>
```

Update the composer.json file properties for your own project.

Look at src/Bootstrap.php to see how to add menu items, subscribe to system events, insert global settings, or adjust the OpenEMR api.


### Installing Module Via Composer
There are two ways to install your module via composer.  
#### Public Module
We highly encourage you to share your created modules with the OpenEMR community.  To ensure that other developers / users can install
your packages please register your module on [https://packagist.org/](https://packagist.org/).  Once your module has been registered
users can install your package by doing a `composer require "<namespace>/<your-package-name>`
#### Private Module
If your module is a private module you can still tell composer where to find your module by setting it up to use a private repository.
You can do it with the following command:
```
composer config repositories.repo-name vcs https://github.com/<organization or user name>/<repository name>
```
For example to install this skeleton as a module you can run the following
```
composer config repositories.repo-name vcs https://github.com/adunsulag/oe-module-custom-skeleton
```

At that point you can run the install command
```
composer require adunsulag/oe-module-custom-skeleton
```

### Installing Module via filesystem
If you copy your module into the installation directory you will need to copy your module's composer.json "psr-4" property into your OpenEMR's psr-4 settings.
You will also need to run a ```composer dump-autoload``` wherever your openemr composer.json file is located in order to get your namespace properties setup properly
to include your module.

### Activating Your Module
Install your module using either composer (recommended) or by placing your module in the *<openemr_installation_directory>//interface/modules/custom_modules/*.

Once your module is installed in OpenEMR custom_modules folder you can activate your module in OpenEMR by doing the following.

  1. Login to your OpenEMR installation as an administrator
  2. Go to your menu and select Modules -> Manage Modules
  3. Click on the Unregistered tab in your modules list
  4. Find your module and click the *Register* button.  This will reload the page and put your module in the Registered list tab of your modules
  5. Now click the *Install* button next your module name.
  6. Finally click the *Enable* button for your module.

## Contributing
If you would like to help in improving the skeleton library just post an issue on Github or send a pull request.