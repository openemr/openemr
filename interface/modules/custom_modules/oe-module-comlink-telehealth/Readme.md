### Comlink Telehealth Module

This project implements the Comlink Telehealth system for the Comlink OpenEMR installation.  

### Installing Module
```
composer config repositories.repo-name vcs https://github.com/ComlinkTelecommunicationsServicesInc/oe-module-comlink-telehealth
```

At that point you can run the composer require command to fetch the repository and install it on the filesystem.  This will also setup the autoload class configuration inside OpenEMR.

```
composer require ComlinkTelecommunicationsServicesInc/oe-module-comlink-telehealth
```

Note: This does NOT activate/install the module inside the OpenEMR application.  That happens in the next step.

### Activating Your Module
Once your module is installed in OpenEMR custom_modules folder you can activate your module in OpenEMR by doing the following.

  1. Login to your OpenEMR installation as an administrator
  2. Go to your menu and select Modules -> Manage Modules
  3. Click on the Unregistered tab in your modules list
  4. Find your module and click the *Register* button.  This will reload the page and put your module in the Registered list tab of your modules
  5. Now click the *Install* button next your module name.
  6. Finally click the *Enable* button for your module.
