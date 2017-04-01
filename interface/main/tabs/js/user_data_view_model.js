/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

function user_data_view_model(username,fname,lname,authGrp)
{
    var self=this;
    self.username=ko.observable(username);
    self.fname=ko.observable(fname);
    self.lname=ko.observable(lname);
    self.authorization_group=ko.observable(authGrp);
    self.messages=ko.observable("");
    return this;

}

function viewPtFinder()
{
    navigateTab(webroot_url+"/interface/main/finder/dynamic_finder.php","fin");
    activateTabByName("fin",true);
}

function viewTgFinder() {

    navigateTab(webroot_url+"/interface/therapy_groups/index.php?method=listGroups","gfn");
    activateTabByName("gfn",true);
}

function viewMessages()
{
    navigateTab(webroot_url+"/interface/main/messages/messages.php?form_active=1","msg");
    activateTabByName("msg",true);
}

function editSettings()
{
    navigateTab(webroot_url+"/interface/super/edit_globals.php?mode=user","msc");
    activateTabByName("msc",true);
}

function changePassword()
{
    navigateTab(webroot_url+"/interface/usergroup/user_info.php","msc");
    activateTabByName("msc",true);
}

function logout()
{
    top.restoreSession();
    top.window.location=webroot_url+"/interface/logout.php";
}
