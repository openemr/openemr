<?php

use OpenEMR\Services\FormRegistryService;

//these are the functions used to access the forms registry database
//

/**
 * @param $directory
 * @param $sql_run
 * @param $unpackaged
 * @param $state
 * @deprecated 7.0.3 Use FormRegistryService::registerForm instead
 * @return false|int
 */
function registerForm($directory, $sql_run = 0, $unpackaged = 1, $state = 0)
{
    $service = new FormRegistryService();
    return $service->registerForm($directory, $sql_run, $unpackaged, $state);
}

/**
 * @param $id
 * @param $mod
 * @deprecated 7.0.3 Use FormRegistryService::updateRegistered instead
 * @return \OpenEMR\Common\Database\recordset
 */
function updateRegistered($id, $mod)
{
    $service = new FormRegistryService();
    return $service->updateRegistered($id, $mod);
}

/**
 * @param string $state
 * @param string $limit
 * @param string $offset
 * @param string $encounterType all|patient|therapy_group
 * @deprecated 7.0.3 Use FormRegistryService::getRegistered instead
 */
function getRegistered($state = "1", $limit = "unlimited", $offset = "0", $encounterType = 'all')
{
    $service = new FormRegistryService();
    return $service->getRegistered($state, $limit, $offset, $encounterType);
}

/**
 * @param $id
 * @param $cols
 * @deprecated 7.0.3 Use FormRegistryService::getRegistryEntry instead
 * @return null
 */
function getRegistryEntry($id, $cols = "*")
{
    $service = new FormRegistryService();
    return $service->getRegistryEntry($id, $cols);
}

/**
 * @param $directory
 * @param $cols
 * @deprecated 7.0.3 Use FormRegistryService::getRegistryEntryByDirectory instead
 * @return array|false|null
 */
function getRegistryEntryByDirectory($directory, $cols = "*")
{
    $service = new FormRegistryService();
    return $service->getRegistryEntryByDirectory($directory, $cols);
}

/**
 * @param $dir
 * @deprecated 7.0.3 Use FormRegistryService::installSQL instead
 * @return bool
 */
function installSQL($dir)
{
    $service = new FormRegistryService();
    return $service->installSQL($dir);
}


/*
 * is a form registered
 *  (optional - and active)
 * in the database?
 *
 * NOTE - sometimes the Name of a form has a line-break at the end, thus this function might be better
 *
 *  INPUT =   directory => form directory
 *            state => 0=inactive / 1=active
 *  OUTPUT = true or false
  * @deprecated 7.0.3 Use FormRegistryService::isRegistered instead
 */
function isRegistered($directory, $state = 1)
{
    $service = new FormRegistryService();
    return $service->isRegistered($directory, $state);
}

/**
 * @deprecated 7.0.3 Use FormRegistryService::getTherapyGroupCategories instead
 * @return string[]
 */
function getTherapyGroupCategories()
{
    $service = new FormRegistryService();
    return $service->getTherapyGroupCategories();
}

// This gets an array including both standard and LBF visit form types,
// one row per form type, sorted by category, priority, is lbf, name.
//
/**
 * @param $state
 * @param $lbfonly
 * @deprecated 7.0.3 Use FormRegistryService::getFormsByCategory instead
 * @return array
 */
function getFormsByCategory($state = '1', $lbfonly = false)
{
    $service = new FormRegistryService();
    return $service->getFormsByCategory($state, $lbfonly);
}
