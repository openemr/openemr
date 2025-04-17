/**
 * custom_bindings.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

ko.bindingHandlers.location={
    init: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        var tabData = ko.unwrap(valueAccessor());
        tabData.window=element.contentWindow;
        element.addEventListener("load",
            function()
            {

                var cwDocument;
                try {
                    cwDocument=this.contentWindow.document;
                } catch ( e ) {
                    // The document is not available, possibly because it's on another domain (ie NewCrop)
                    cwDocument = false;
                }

                if ( cwDocument ) {
                    $(function () {
                            var jqDocument = $(cwDocument);
                            var titleDocument = jqDocument.attr('title');
                            var titleText = "Unknown";
                            var titleClass = jqDocument.find(".title:first");
                            if (titleDocument.length >= 1) {
                                titleText = titleDocument;
                            }
                            else if (titleClass.length >= 1) {
                                titleText = titleClass.text();
                            }
                            else {
                                var frameDocument = jqDocument.find("frame");
                                if (frameDocument.length >= 1) {
                                    titleText = frameDocument.attr("name");
                                    var jqFrameDocument = $(frameDocument.get(0).contentWindow.document);
                                    titleClass = jqFrameDocument.find(".title:first");
                                    if (titleClass.length >= 1) {
                                        titleText = titleClass.text();
                                    }
                                    var subFrame = frameDocument.get(0);
                                    subFrame.addEventListener("load",
                                        function () {
                                            var subFrameDocument = $(subFrame.contentWindow.document);
                                            titleClass = $(subFrameDocument).find(".title:first");
                                            if (titleClass.length >= 1) {
                                                titleText = titleClass.text();
                                                tabData.title(titleText);
                                            }

                                        });
                                }
                                else {
                                    var bold = jqDocument.find("b:first");
                                    if (bold.length) {
                                        titleText = bold.text();
                                    }
                                    else {
                                        var title = jqDocument.find("title");
                                        if (title.length) {
                                            titleText = title.text();
                                        }
                                    }

                                }

                            }
                            tabData.title(titleText);
                        }
                    );
                } else {
                    // need to cancel the loading if we are on another domain
                    // setting the title will hide the spinner and remove the Loading... text
                    tabData.title(xl("Unknown"));
                }
            } ,true
        );

    },
    update: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        var tabData = ko.unwrap(valueAccessor());
        element.src=tabData.url();
    }
};

ko.bindingHandlers.iframeName = {
    init: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
    },
    update: function(element,valueAccessor, allBindings,viewModel, bindingContext)
    {
        element.name=ko.unwrap(valueAccessor());
    }
};
