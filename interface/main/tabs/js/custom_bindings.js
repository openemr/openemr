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

ko.bindingHandlers.location={
    init: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        var tabData = ko.unwrap(valueAccessor());
        tabData.window=element.contentWindow;
        element.addEventListener("load",
            function()
            {

                var cwDocument=this.contentWindow.document
                $(cwDocument).ready(function(){
                        var jqDocument=$(cwDocument);
                        var titleDocument=jqDocument.attr('title');
                        var titleText="Unknown";
                        var titleClass=jqDocument.find(".title:first");
                        if (titleDocument.length>=1)
                        {
                            titleText=titleDocument;
                        }
                        else if (titleClass.length>=1)
                        {
                            titleText=titleClass.text();
                        }
                        else
                        {
                            var frameDocument=jqDocument.find("frame");
                            if(frameDocument.length>=1)
                            {
                                titleText=frameDocument.attr("name");
                                var jqFrameDocument=$(frameDocument.get(0).contentWindow.document);
                                titleClass=jqFrameDocument.find(".title:first");
                                if(titleClass.length>=1)
                                {
                                    titleText=titleClass.text();
                                }
                                var subFrame= frameDocument.get(0);
                                subFrame.addEventListener("load",
                                function()
                                {
                                    var subFrameDocument=$(subFrame.contentWindow.document);
                                    titleClass=$(subFrameDocument).find(".title:first");
                                    if(titleClass.length>=1)
                                    {
                                        titleText=titleClass.text();
                                        tabData.title(titleText);
                                    }

                                });
                            }
                            else
                            {
                                var bold=jqDocument.find("b:first");
                                if(bold.length)
                                {
                                    titleText=bold.text();
                                }
                                else
                                {
                                    var title=jqDocument.find("title");
                                    if(title.length)
                                    {
                                        titleText=title.text();
                                    }
                                }

                            }

                        }
                        tabData.title(titleText);
                    }
                );
            }
            ,true
        );

    },
    update: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        var tabData = ko.unwrap(valueAccessor());
        element.src=tabData.url();
    }
}

ko.bindingHandlers.iframeName = {
    init: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
    },
    update: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        element.name=ko.unwrap(valueAccessor());
    }
}


