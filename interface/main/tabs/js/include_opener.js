/**
 * This is code needed to connect the iframe for a dialog back to the window which makes the call.
 * It is neccessary to include this script at the "top" of any php file that is used as a dialog.
 * It was not possible to inject this code at "document ready" because sometimes the opened dialog 
 * has a redirect or a close before the document ever becomes ready. 
 *
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

if(top.tab_mode===true)
{
    if(!opener)
    {
        opener=top.get_opener(window.name);
    }

    window.close=
            function()
            {
                var dialogDiv=top.$("#dialogDiv");
                var frameName=window.name
                var body=top.$("body");
                    var removeFrame=body.find("iframe[name='"+frameName+"']");
                    removeFrame.remove();
                    var removeDiv=body.find("div.dialogIframe[name='"+frameName+"']");
                    removeDiv.remove();
                    if(body.children("div.dialogIframe").length===0)
                    {   
                        dialogDiv.hide();
                    };
                };    
}
