// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Append the version HTML.
 */
dwvOemr.gui.appendVersionHtml = function (version)
{
    var nodes = document.getElementsByClassName("dwv-version");
    if ( nodes ) {
        for( var i = 0; i < nodes.length; ++i ){
            nodes[i].appendChild( document.createTextNode(version) );
        }
    }
};

/**
 * Build the help HTML.
 * @param {Object} toolList The list of tool objects.
 * @param {Boolean} mobile Flag for mobile or not environement.
 * @param {Object} app The associated app.
 * @param {String} resourcesPath The path to help resources.
 */
dwvOemr.gui.appendHelpHtml = function (toolList, mobile, app, resourcesPath)
{
    var actionType = "mouse";
    if( mobile ) {
        actionType = "touch";
    }

    var toolHelpDiv = document.createElement("div");

    var helpKeys = null;
    var tkeys = Object.keys(toolList);
    for ( var t=0; t < tkeys.length; ++t )
    {
        helpKeys = toolList[tkeys[t]].getHelpKeys();
        // title
        var titleElement = document.createElement("h3");
        var titleStr = dwv.i18n(helpKeys.title);
        titleElement.appendChild(document.createTextNode(titleStr));
        // doc div
        var docDiv = document.createElement("div");
        // brief
        var briefElement = document.createElement("p");
        var briefStr = dwv.i18n(helpKeys.brief);
        briefElement.appendChild(document.createTextNode(briefStr));
        docDiv.appendChild(briefElement);
        // details
        if( helpKeys[actionType] ) {
            var keys = Object.keys(helpKeys[actionType]);
            for( var i=0; i<keys.length; ++i )
            {
                var action = keys[i];

                var img = document.createElement("img");
                img.src = resourcesPath + "/" + action + ".png";
                img.style.float = "left";
                img.style.margin = "0px 15px 15px 0px";

                var br = document.createElement("br");
                br.style.clear = "both";

                var para = document.createElement("p");
                para.appendChild(img);
                var actionHelp = dwv.i18n(helpKeys[actionType][action]);
                para.appendChild(document.createTextNode(actionHelp));
                para.appendChild(br);
                docDiv.appendChild(para);
            }
        }

        // different div structure for mobile or static
        if( mobile )
        {
            var toolDiv = document.createElement("div");
            toolDiv.setAttribute("data-role", "collapsible");
            toolDiv.appendChild(titleElement);
            toolDiv.appendChild(docDiv);
            toolHelpDiv.appendChild(toolDiv);
        }
        else
        {
            toolHelpDiv.id = "accordion";
            toolHelpDiv.appendChild(titleElement);
            toolHelpDiv.appendChild(docDiv);
        }
    }

    var helpNode = app.getElement("help");

    var headPara = document.createElement("p");
    headPara.appendChild(document.createTextNode(dwv.i18n("help.intro.p0")));
    helpNode.appendChild(headPara);

    var secondPara = document.createElement("p");
    secondPara.appendChild(document.createTextNode(dwv.i18n("help.intro.p1")));
    helpNode.appendChild(secondPara);

    var toolPara = document.createElement("p");
    toolPara.appendChild(document.createTextNode(dwv.i18n("help.tool_intro")));
    helpNode.appendChild(toolPara);
    helpNode.appendChild(toolHelpDiv);
};
