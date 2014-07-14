<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Jacob T.Paul <jacob@zhservices.com>
*    @author  Basil PT <basil@zhservices.com>  
*
* +------------------------------------------------------------------------------+
*/

return array(
    'controllers' => array(
        'invokables' => array(
            'Acl' => 'Acl\Controller\AclController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'acl' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/acl[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Acl',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

	'view_manager' => array(
        'template_path_stack' => array(
            'acl' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'acl/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
);