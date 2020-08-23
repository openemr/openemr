// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Append the version HTML.
 */
dwvOemr.gui.appendVersionHtml = function (version) {
    const nodes = document.getElementsByClassName('dwv-version');
    if (nodes) {
        for (let i = 0; i < nodes.length; i += 1) {
            nodes[i].appendChild(document.createTextNode(version));
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
dwvOemr.gui.appendHelpHtml = function (toolList, mobile, app, resourcesPath) {
    let actionType = 'mouse';
    if (mobile) {
        actionType = 'touch';
    }

    const toolHelpDiv = document.createElement('div');

    let helpKeys = null;
    const tkeys = Object.keys(toolList);
    for (let t = 0; t < tkeys.length; t += 1) {
        helpKeys = toolList[tkeys[t]].getHelpKeys();
        // title
        const titleElement = document.createElement('h3');
        const titleStr = dwv.i18n(helpKeys.title);
        titleElement.appendChild(document.createTextNode(titleStr));
        // doc div
        const docDiv = document.createElement('div');
        // brief
        const briefElement = document.createElement('p');
        const briefStr = dwv.i18n(helpKeys.brief);
        briefElement.appendChild(document.createTextNode(briefStr));
        docDiv.appendChild(briefElement);
        // details
        if (helpKeys[actionType]) {
            const keys = Object.keys(helpKeys[actionType]);
            for (let i = 0; i < keys.length; i += 1) {
                const action = keys[i];

                const img = document.createElement('img');
                img.src = `${resourcesPath}/${action}.png`;
                img.style.float = 'left';
                img.style.margin = '0px 15px 15px 0px';

                const br = document.createElement('br');
                br.style.clear = 'both';

                const para = document.createElement('p');
                para.appendChild(img);
                const actionHelp = dwv.i18n(helpKeys[actionType][action]);
                para.appendChild(document.createTextNode(actionHelp));
                para.appendChild(br);
                docDiv.appendChild(para);
            }
        }

        // different div structure for mobile or static
        if (mobile) {
            const toolDiv = document.createElement('div');
            toolDiv.setAttribute('data-role', 'collapsible');
            toolDiv.appendChild(titleElement);
            toolDiv.appendChild(docDiv);
            toolHelpDiv.appendChild(toolDiv);
        } else {
            toolHelpDiv.id = 'accordion';
            toolHelpDiv.appendChild(titleElement);
            toolHelpDiv.appendChild(docDiv);
        }
    }

    const helpNode = app.getElement('help');

    const headPara = document.createElement('p');
    headPara.appendChild(document.createTextNode(dwv.i18n('help.intro.p0')));
    helpNode.appendChild(headPara);

    const secondPara = document.createElement('p');
    secondPara.appendChild(document.createTextNode(dwv.i18n('help.intro.p1')));
    helpNode.appendChild(secondPara);

    const toolPara = document.createElement('p');
    toolPara.appendChild(document.createTextNode(dwv.i18n('help.tool_intro')));
    helpNode.appendChild(toolPara);
    helpNode.appendChild(toolHelpDiv);
};
