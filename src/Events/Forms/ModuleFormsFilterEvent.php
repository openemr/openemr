<?php

namespace OpenEMR\Events\Forms;

use OpenEMR\Services\FormRegistryService;

class ModuleFormsFilterEvent
{
    const EVENT_NAME = 'forms.module_forms_filter';

    private array $forms = [];
    public function __construct()
    {
    }

    public function addModuleForm(array $form): void
    {
        // Required keys: name, directory, state, nickname, priority, category, aco_spec, sql_run
        $required = ['name', 'directory', 'state', 'nickname', 'priority', 'category', 'aco_spec', 'sql_run'];
        foreach ($required as $key) {
            if (!isset($form[$key])) {
                throw new \InvalidArgumentException("Missing required key $key in module form registration");
            }
        }

        // Validate directory path
        $path = realpath($GLOBALS['fileroot'] . '/interface/modules/' . $form['directory']);
        if (!$path || strpos($path, $GLOBALS['fileroot'] . '/interface/modules/') !== 0) {
            throw new \InvalidArgumentException('Invalid module directory path');
        }

        $this->forms[] = array_merge([
            'id' => 'module_' . count($this->forms), // Unique ID for module forms
            'unpackaged' => FormRegistryService::FORM_PACKAGE_STATE_UNPACKAGED,
            'date' => date('Y-m-d H:i:s'),
            'patient_encounter' => FormRegistryService::FORM_PATIENT_ENCOUNTER_ENABLED,
            'sql_file' => null,
            'info_file' => null,
            'sql_run' => FormRegistryService::FORM_SQL_INSTALLED, // for now we assume all module forms have sql already executed via their module table.sql mechanism
            'form_source' => FormRegistryService::FORM_SOURCE_MODULE,
            'therapy_group_encounter' => FormRegistryService::FORM_THERAPY_GROUP_ENCOUNTER_DISABLED,
        ], $form);
    }

    public function getModuleForms(): array
    {
        return $this->forms;
    }

    public function hasModuleForms(): bool
    {
        return !empty($this->forms);
    }
}
