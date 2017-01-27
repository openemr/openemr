/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

var app_view_model={};

app_view_model.application_data={};

app_view_model.application_data.tabs=new tabs_view_model();

app_view_model.application_data.patient=ko.observable(null);

app_view_model.application_data.user=ko.observable(null);

app_view_model.application_data.therapy_group=ko.observable(null);

app_view_model.attendant_template_type=ko.observable('patient-data-template');