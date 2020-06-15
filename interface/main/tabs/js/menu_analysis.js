/**
 * menu_analysis.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var targets={};
targets['Calendar']='lst';
targets['Flow Board']='lst';
targets['Messages ']='pat';
targets['Administration']='adm';
targets['Reports']='rep';
targets['Miscellaneous']='msc';

targets['Patients']='lst';

var acl_reqs={};
var global_reqs={};

//Billing Menu Restrictions
acl_reqs['EDI History']=['acct','eob'];
global_reqs['EDI History']='enable_edihistory_in_left_menu'

// Administration Menu restrictions
acl_reqs['Globals']=['admin','super'];
acl_reqs['Facilities']=['admin','users'];
acl_reqs['Users']=['admin','users'];
acl_reqs['Addr Book']=['admin','practice'];
acl_reqs['Practice']=['admin','practice'];
acl_reqs['Codes']=['admin','superbill'];
acl_reqs['Layouts']=['admin','super'];
acl_reqs['Lists']=['admin','super'];
acl_reqs['ACL']=['admin','acl'];
acl_reqs['Files']=['admin','super'];
acl_reqs['Backup']=['admin','super'];


acl_reqs['Rules']=['admin','super'];
global_reqs['Rules']='enable_cdr'

acl_reqs['Alerts']=['admin','super'];
global_reqs['Alerts']='enable_cdr'

acl_reqs['Patient Reminders']=['admin','super'];
global_reqs['Patient Reminders']='enable_cdr'


acl_reqs['Language']=['admin','language'];
acl_reqs['Forms']=['admin','forms'];

acl_reqs['Calendar']=['admin','calendar','main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig']

acl_reqs['Logs']=['admin','users'];

acl_reqs['Certificates']=['admin','users'];

acl_reqs['Native Data Loads']=['admin','super'];
acl_reqs['External Data Loads']=['admin','super'];
acl_reqs['Merge Patient']=['admin','super'];

global_reqs['Fax/Scan']=['enable_hylafax','enable_scanner'];


function set_acl_reqs(entry)
{
    if('url' in entry)
    {
        if(entry.label in acl_reqs)
        {
            var reqs=acl_reqs[entry.label];
            if(reqs.length===3)
            {
                if(entry.url.indexOf(reqs[2])!==-1)
                {
                    entry.acl_req=[reqs[0],reqs[1]];
                }
            }
            else
            {
                entry.acl_req=acl_reqs[entry.label];
            }
        }
        if(entry.label in global_reqs)
        {
            entry.global_req=global_reqs[entry.label];
        }
    }
}

function setTarget(entry,target)
{
    if('url' in entry)
    {
        entry.target=target
    }
    else
    {
        for(var idx=0;idx<entry.children.length;idx++)
        {
            setTarget(entry.children[idx],target);
        }
    }
}
function post_process(menu_entries)
{
    for(var idx=0;idx<menu_entries.length;idx++)
    {
        var curEntry=menu_entries[idx];
        set_acl_reqs(curEntry);
        if(curEntry.label in targets)
        {
            setTarget(curEntry,targets[curEntry.label]);
        }
        post_process(curEntry.children);
    }
}
function parse_link(link,entry)
{
    if(link)
    {
        var parameters=link.substring(link.indexOf('(')+1,link.indexOf(')'));
        if(parameters==='')
        {
            parameters=link;
        }
        if(link.indexOf("loadFrame2")===-1)
        {
            var url=parameters.replace(/\'/g,"").replace(/\"/g,"").replace("../","/interface/");
            entry.url=url;
            entry.target="report";
        }
        else
        {
            parameters=parameters.replace(/\'/g,"").replace(/\"/g,"");
            var params=parameters.split(",");
            entry.target=params[1];
            if(entry.target==='RTop')
            {
                entry.target='pat';
            }
            if(entry.target==='RBot')
            {
                entry.target='enc';
            }


            entry.url=params[2].replace("../","/");
            if(entry.url.indexOf("/")>0)
            {
                entry.url="/interface/"+entry.url;
            }

        }
    }
}

function menu_entry(label,link,menu_id)
{
    var self=this;
    self.label=label;
    self.menu_id=menu_id;
    parse_link(link,self);
    self.children=[];
    self.icon=icon;
    self.helperText=helperText;
    self.requirement=0;
    if(menu_id)
    {
        if(menu_id.charAt(3)==='1')
        {
            if(self.label==='Summary')
            {
                self.target="pat";
            }
            else
            {
                self.target="enc";
            }
            self.requirement=1;
        } else
        if(menu_id.charAt(3)==='2')
        {
            self.target="enc";
            self.requirement=2;
            // Special case for "Current" visit entry
            if(self.label==="Current")
            {
                self.requirement=3;
            }
        }
    }


    return this;
}

function menu_entry_from_jq(elem)
{
    return new menu_entry(elem.text(),elem.attr("onClick"),elem.attr("id"));
}
var menu_entries=[];
function analyze_menu()
{
    alert('I think you will never see this. --Rod'); // debugging

    if(!top.left_nav)
    {
        setTimeout(analyze_menu,1000);
        return;
    }
    else
    {
        if(!top.left_nav.$)
        {
            alert("no jq!");
            setTimeout(analyze_menu,1000);
            return;
        }
    }
    var jqLeft=top.left_nav.$(top.left_nav.document);
    var $=top.left_nav.$;
    jqLeft.ready(function(){

        var menuTop=jqLeft.find("#navigation-slide");
        menuTop.children().each(
                function(idx,elem)
                {
                    // Header or content
                    var jqElem=$(elem);
                    var anchor=jqElem.children("a");
                    var subMenu = jqElem.children("ul");

                    var newEntry=menu_entry_from_jq(anchor);
                    if(subMenu.length>0)
                    {
                        // 2 (Second) level menu items
                        subMenu.children("li").each(function(idx,elem)
                        {
                            var sub_anchor=$(elem).children("a");
                            var sub_entry=menu_entry_from_jq(sub_anchor);
                            if(sub_anchor.length!==1)
                            {
                                alert(sub_anchor.text());
                            }
                            var subSubMenu=$(elem).children("ul");
                            //Third Level Menu Items
                            if(subSubMenu.length>0 && sub_entry.label !=="Visit Forms")
                            {
                                subSubMenu.children("li").each(function(idx,elem)
                                {
                                    var sub_sub_anchor=$(elem).children("a");
                                    var sub_sub_entry=menu_entry_from_jq(sub_sub_anchor);
                                    sub_entry.children.push(sub_sub_entry);

                                });

                            }
                            //End Third Level Menu Items
                            newEntry.children.push(sub_entry);
                        });
                        // End Second level menu items
                    }
                    else
                    {


                    }
                    menu_entries.push(newEntry);


                }
        );
        // Scan popup select
        var popups = jqLeft.find("select[name='popups'] option");
        var popups_menu_header=new menu_entry("Popups","","popup");
        menu_entries.push(popups_menu_header);
        popups.each(function(idx,elem)
            {
                var jqElem=$(elem);
                if(jqElem.val()!=='')
                {
                    var popup_entry=new menu_entry(jqElem.text(),jqElem.val(),"Popup:"+jqElem.text());
                    popup_entry.target="pop";
                    popup_entry.requirement=1;
                    popups_menu_header.children.push(popup_entry);
                }
            });
        // Process Complete

        post_process(menu_entries);
        var data=$("<div id='#menuData'></div>");
        data.text("$menu_json=\""+JSON.stringify(menu_entries).replace(/\"/g,"\\\"")+"\";");
        $("body").append(data);
    });
}
var toID=setTimeout(analyze_menu,1000);
