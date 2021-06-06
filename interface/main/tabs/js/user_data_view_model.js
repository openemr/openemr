/**
 * user_data_view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
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
    self.portalPayments=ko.observable("");

    return this;

}

function viewPtFinder(myMessage, searchAnyType, data, event)
{
    event.stopImmediatePropagation();
    event.preventDefault();
    let srchBox = document.getElementById("anySearchBox");
    srchBox.focus();

    let finderUrl = webroot_url+"/interface/main/finder/dynamic_finder.php";
    let srchBoxVal = srchBox.value.trim();
    let srchBoxWidth = srchBox.offsetWidth;
    let srchBoxLength = srchBoxWidth < 50 ? 0 : srchBoxVal.length;// to let input box with values be displayed on mousedown on Smartphones

    if (srchBoxLength > 0 ) {
        finderUrl += "?search_any=" + encodeURIComponent(srchBoxVal);
        navigateTab(finderUrl,"fin", function () {
            activateTabByName("fin",true);
        });
        srchBox.blur();
    } else if (srchBoxLength == 0 && srchBoxWidth > 50) {
        if (searchAnyType == 'dual') {
            srchBox.blur();
            navigateTab(finderUrl,"fin", function () {
                activateTabByName("fin",true);
            });
        } else if (searchAnyType == 'comprehensive') {
            alert(arguments[0]);
            srchBox.focus();
        }
    }

}

function viewTgFinder()
{
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

function viewPortalPayments()
{
    navigateTab(webroot_url+"/portal/patient/onsiteactivityviews","msc", function () {
        activateTabByName("msc",true);
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

function changeMFA()
{
    navigateTab(webroot_url+"/interface/usergroup/mfa_registrations.php","msc", function () {
        activateTabByName("msc",true);
    });
}

function logout()
{
    top.restoreSession();
    document.getElementById("logoutinnerframe").src=webroot_url+"/interface/logout.php";
}

function timeoutLogout()
{
    top.restoreSession();
    document.getElementById("logoutinnerframe").src=webroot_url+"/interface/logout.php?timeout=1";
}
