/**
 * user_data_view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function user_data_view_model(username,fname,lname,authGrp)
{
    var self=this;
    self.username=ko.observable(username);
    self.fname=ko.observable(fname);
    self.lname=ko.observable(lname);
    self.authorization_group=ko.observable(authGrp);
    self.messages=ko.observable(false);
    self.portal=ko.observable(isPortalEnabled);
    self.portalAlerts=ko.observable("");
    self.portalAudits=ko.observable("");
    self.portalMail=ko.observable("");
    self.portalChats=ko.observable("");

    return this;

}

function viewPtFinder()
{
    navigateTab(webroot_url+"/interface/main/finder/dynamic_finder.php","fin", function () {
        activateTabByName("fin",true);
    });
}

function viewTgFinder() {

    navigateTab(webroot_url+"/interface/therapy_groups/index.php?method=listGroups","gfn", function () {
        activateTabByName("gfn",true);
    });
}

function viewMessages()
{
    navigateTab(webroot_url+"/interface/main/messages/messages.php?form_active=1","msg", function () {
        activateTabByName("msg",true);
    });
}

function viewPortalAudits()
{
    navigateTab(webroot_url+"/portal/patient/onsiteactivityviews","msc", function () {
        activateTabByName("msc",true);
    });
}

function viewPortalMail()
{
    navigateTab(webroot_url+"/portal/messaging/messages.php","por", function () {
       activateTabByName("por",true);
    });
}

function viewPortalChats()
{
    navigateTab(webroot_url+"/portal/messaging/secure_chat.php","pop", function () {
        activateTabByName("pop",true);
    });
}

function editSettings()
{
    navigateTab(webroot_url+"/interface/super/edit_globals.php?mode=user","msc", function () {
        activateTabByName("msc",true);
    });
}

function changePassword()
{
    navigateTab(webroot_url+"/interface/usergroup/user_info.php","msc", function () {
        activateTabByName("msc",true);
    });
}

function logout()
{
    top.restoreSession();
    top.window.location=webroot_url+"/interface/logout.php";
}
