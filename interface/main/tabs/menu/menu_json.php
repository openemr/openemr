<?php
/**
 * Menu loader.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Robert Down <robertdown@live.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\MainMenuRole;

// Collect the menu then build it
$menuMain = new MainMenuRole();
$menu_restrictions = $menuMain->getMenu();
?>
<script type="text/javascript">

    function menu_entry(object)
    {
        var self=this;
        self.label=ko.observable(object.label);

        self.header=false;
        if('url' in object )
        {
            self.url=ko.observable(object.url);
            self.header=false;
        }
        else
        {
            self.header=true;
        }
        if('target' in object)
        {
            self.target=object.target;
        }
        self.requirement=object.requirement;

        if('icon' in object)
        {
            self.icon=object.icon;
        }
        self.icon=object.icon;

        if('helperText' in object)
        {
            self.helperText=object.helperText;
        }
        self.helperText=object.helperText;

        if(object.requirement===0)
        {
            self.enabled=ko.observable(true);
        } else if(object.requirement===1)
        {
            self.enabled=ko.computed(function()
            {
                return app_view_model.application_data.patient()!==null;
            });
        } else if((object.requirement===2) || (object.requirement===3))
        {
            self.enabled=ko.computed(function()
            {
                return (app_view_model.application_data.patient()!==null
                        && app_view_model.application_data.patient().selectedEncounter()!=null);
            });

        }
        else if(object.requirement===4)
        {
            self.enabled=ko.computed(function()
            {
                return app_view_model.application_data.therapy_group()!==null;
            });
        }
        else if(object.requirement===5)
        {
            self.enabled=ko.computed(function()
            {
                return (app_view_model.application_data.therapy_group()!==null
                && app_view_model.application_data.therapy_group().selectedEncounter()!=null);
            });
        }
        if(self.header)
        {
            self.children=ko.observableArray();
            for(var childIdx=0;childIdx<object.children.length;childIdx++)
            {
                var childObj=new menu_entry(object.children[childIdx]);
                self.children.push(childObj);
            }
        }
        return this;
    }
    function process_menu_object(object,target)
    {
        var newEntry=new menu_entry(object);
        target.push(newEntry);
    }
    var menu_objects=<?php echo json_encode($menu_restrictions); ?>;
    app_view_model.application_data.menu=ko.observableArray();
    for(var menuIdx=0;menuIdx<menu_objects.length;menuIdx++)
    {
        var curObj=menu_objects[menuIdx];
        process_menu_object(curObj,app_view_model.application_data.menu);
    }
</script>
