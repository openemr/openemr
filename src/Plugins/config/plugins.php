<?php

// Plugin handling
use OpenEMR\Plugins\{
    Activation\StateProvider,
    Activation\EnableAll,
    PluginManager,
};

return [
    // TODO: this needs special handling
    StateProvider::class => EnableAll::class,
    EnableAll::class,
    PluginManager::class,
];
