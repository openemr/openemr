<?php

// File: $Id$ $Name$
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// Thatware - http://thatware.org/
// PHP-NUKE Web Portal System - http://phpnuke.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


$prefix = $pnconfig['prefix'];

$pntable = array();

$module_vars = $prefix . '_module_vars';
$pntable['module_vars'] = $module_vars;
$pntable['module_vars_column'] = array ('id'      => $module_vars . '.pn_id',
                                        'modname' => $module_vars . '.pn_modname',
                                        'name'    => $module_vars . '.pn_name',
                                        'value'   => $module_vars . '.pn_value');

$modules = $prefix . '_modules';
$pntable['modules'] = $modules;
$pntable['modules_column'] = array ('id'            => $modules . '.pn_id',
                                    'name'          => $modules . '.pn_name',
                                    'type'          => $modules . '.pn_type',
                                    'displayname'   => $modules . '.pn_displayname',
                                    'description'   => $modules . '.pn_description',
                                    'regid'         => $modules . '.pn_regid',
                                    'directory'     => $modules . '.pn_directory',
                                    'version'       => $modules . '.pn_version',
                                    'admin_capable' => $modules . '.pn_admin_capable',
                                    'user_capable'  => $modules . '.pn_user_capable',
                                    'state'         => $modules . '.pn_state');
