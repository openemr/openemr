<?php

// Plugin handling
use OpenEMR\Plugins\{
    Activation\StateProvider,
    Activation\EnableAll,
    PluginManager,
};

return [
    /**
     * TODO: dynamic state provider
     *
     * 'PLUGIN_STATE_PROVIDER' => env('..', 'SomeDefaultClassName')
     * StateProvider::class => function (TC $c) {
     *   $className = $c->get('PLUGIN_STATE_PROVIDER');
     *   // try to be slightly smart about the resolution to not require FQCN?
     *   // assert is_a($className, StateProvider)
     *   return $c->get($className);
     * }
     */
    StateProvider::class => EnableAll::class,
    EnableAll::class,
    PluginManager::class,
];
