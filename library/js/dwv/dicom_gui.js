/**
 * DWV (Dicom Web Viewer) application GUI
 * OpenEMR v5.0.2
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com> (complete rework 7/1/2020)
 * @copyright Copyright (c) 2016 Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.utils = dwvOemr.utils || {};

/**
 * Application GUI.
 * @TODO v6.0.0 Replace all translations(i18n) with xl().
 */

// Default colour maps.
dwv.tool.colourMaps = {
    "plain": dwv.image.lut.plain,
    "invplain": dwv.image.lut.invPlain,
    "rainbow": dwv.image.lut.rainbow,
    "hot": dwv.image.lut.hot,
    "hotiron": dwv.image.lut.hot_iron,
    "pet": dwv.image.lut.pet,
    "hotmetalblue": dwv.image.lut.hot_metal_blue,
    "pet20step": dwv.image.lut.pet_20step
};
// Default window level presets.
dwv.tool.defaultpresets = {};
// Default window level presets for CT.
dwv.tool.defaultpresets.CT = {
    "mediastinum": {"center": 40, "width": 400},
    "lung": {"center": -500, "width": 1500},
    "bone": {"center": 500, "width": 2000},
    "brain": {"center": 40, "width": 80},
    "head": {"center": 90, "width": 350}
};

// dwv overrides -------------------------

// prompt
dwv.gui.prompt = dwvOemr.gui.prompt;
// get element
dwv.gui.getElement = dwvOemr.gui.getElement;

// [end] dwv overrides -------------------------

// tool toggle
function toggle(popupId) {
    let popup = document.getElementById(popupId);
    popup.classList.toggle("show-popup");
}

// Toolbox
dwvOemr.gui.ToolboxContainer = function (app, infoController) {
    var base = new dwvOemr.gui.Toolbox(app);
    // save document url
    dwvOemr.oemrDocumentUrl = $("#dwv").attr("src") ?? false;
    dwvOemr.oemrDocumentStateUrl = $("#state_url").val() ?? false;
    dwvOemr.oemrDocumentDocId = $("#doc_id").val() ?? false;
    dwvOemr.oemrDocumentCsrf = $("#csrf").val() ?? false;

    this.setup = function (list) {
        // toolbar
        base.setup(list);

        var undo = document.createElement("button");
        undo.setAttribute("class", "btn-sm btn-primary fa fa-undo");
        undo.title = "Undo";
        undo.onclick = function () {
            app.undo();
        };

        var redo = document.createElement("button");
        redo.setAttribute("class", "btn-sm btn-primary fa fa-redo");
        redo.title = "Redo";
        redo.onclick = function () {
            app.redo();
        };

        // open
        var open = document.createElement("button");
        open.setAttribute("class", "btn-sm btn-primary fa fa-file");
        open.title = dwv.i18n("basics.open");
        open.onclick = function () {
            dwvOemr.gui.displayProgress(0);
            toggle("loaderlist");
        };

        // toolbox
        var toolbox = document.createElement("button");
        toolbox.setAttribute("class", "btn-sm btn-primary fa fa-wrench");
        toolbox.title = dwv.i18n("basics.toolbox");
        toolbox.onclick = function () {
            let toolList = app.getElement("toolList");
            dwvOemr.html.toggleDisplay(toolList);
            dwvOemr.html.toggleDisplay(app.getElement("editspan"));
        };

        // DICOM tags
        var tags = document.createElement("button");
        tags.setAttribute("class", "btn-sm btn-primary fa fa-list");
        tags.title = dwv.i18n("basics.dicomTags");
        tags.onclick = function () {
            toggle("tagsPopup");
        };

        // draw list
        var drawList = document.createElement("button");
        drawList.setAttribute("class", "btn-sm btn-primary fa fa-edit");
        drawList.title = dwv.i18n("basics.drawList");
        drawList.onclick = function () {
            let drawList = app.getElement("drawList");
            dwvOemr.html.toggleDisplay(drawList);
        };

        // image
        var image = document.createElement("button");
        image.setAttribute("class", "btn-sm btn-primary fa fa-image");
        image.title = dwv.i18n("basics.image");
        image.onclick = function () {
            let layerDialog = app.getElement("layerDialog");
            dwvOemr.html.toggleDisplay(layerDialog);
        };

        // info
        var info = document.createElement("button");
        info.setAttribute("class", "btn-sm btn-primary fa fa-info");
        info.title = dwv.i18n("basics.info");
        info.onclick = function () {
            var infoLayer = app.getElement("infoLayer");
            dwvOemr.html.toggleDisplay(infoLayer);
            infoController.toggleListeners();
        };

        // help
        var help = document.createElement("button");
        help.setAttribute("class", "btn-sm btn-primary fa fa-question");
        help.title = dwv.i18n("basics.help");
        help.onclick = function () {
            toggle("helpPopup");
        };

        // save state button
        if (dwvOemr.oemrDocumentUrl) {
            var saveButton = document.createElement("button");
            saveButton.setAttribute("class", "btn-sm btn-primary btn-save");
            saveButton.appendChild(document.createTextNode(dwv.i18n("Save Image Edits")));
            node = app.getElement("openData");
            node.appendChild(saveButton);
            saveButton.onclick = function () {
                let oemrStateSaveUrl = dwvOemr.oemrDocumentStateUrl;
                let data = new FormData();
                let json = app.getState();
                data.append('action', 'save');
                data.append( "json_data", json);
                data.append('csrf_token_form', dwvOemr.oemrDocumentCsrf);
                data.append('doc_id', dwvOemr.oemrDocumentDocId);
                fetch(oemrStateSaveUrl, {
                    method: 'POST',
                    body: data
                }).then(response => {
                    return response.json();
                }).then((rtn) => {
                    console.log("State Save Returned: " + rtn);
                    // translated from fetch
                    // @TODO v6.0 change to our utility timed alert
                    //alert(rtn);
                    toggle("loaderlist");
                });
            };
        } else {
            // for stand alone save state link
            var saveButton = document.createElement("button");
            saveButton.className = "btn-sm btn-primary";
            saveButton.appendChild(document.createTextNode(dwv.i18n("Download Image Edits")));
            var toggleSaveState = document.createElement("a");
            toggleSaveState.onclick = function () {
                var blob = new Blob([app.getState()], {type: 'application/json'});
                toggleSaveState.href = window.URL.createObjectURL(blob);
            };
            toggleSaveState.download = "state.json";
            toggleSaveState.id = "download-state";
            toggleSaveState.className += "download-state";
            toggleSaveState.appendChild(saveButton);
            // add to openData window
            node = app.getElement("openData");
            node.appendChild(toggleSaveState);
        }

        // fetch state button
        if (dwvOemr.oemrDocumentUrl) {
            var restoreButton = document.createElement("button");
            restoreButton.setAttribute("class", "btn-sm btn-primary btn-add");
            restoreButton.appendChild(document.createTextNode(dwv.i18n("Restore Image Edits")));
            node = app.getElement("openData");
            node.appendChild(restoreButton);
            // save state link
            restoreButton.onclick = function () {
                let oemrStateSaveUrl = dwvOemr.oemrDocumentStateUrl;
                let data = new FormData();
                let json = app.getState();
                data.append('action', 'fetch');
                data.append('csrf_token_form', dwvOemr.oemrDocumentCsrf);
                data.append('doc_id', dwvOemr.oemrDocumentDocId);
                fetch(oemrStateSaveUrl, {
                    method: 'POST',
                    body: data
                }).then(response => {
                    return response.text();
                }).then((rtn) => {
                    if (!rtn) {
                        alert("No current edits to restore.");
                        toggle("loaderlist");
                        return false;
                    }
                    const file = new File([rtn], 'state.json', {type: 'application/json', lastModified: Date.now()})
                    app.loadFiles([file]);
                });
            };

            // turn off file loader for in app
            dwvOemr.html.toggleDisplay(app.getElement("loaderlist"));
        }
        // add rest to form
        var node = app.getElement("toolbar");
        var spannode = app.getElement("editspan");
        spannode.appendChild(undo);
        spannode.appendChild(redo);
        node.appendChild(open);
        node.appendChild(toolbox);
        node.appendChild(tags);
        node.appendChild(drawList);
        node.appendChild(image);
        node.appendChild(info);
        node.appendChild(help);

        dwvOemr.gui.refreshElement(node);
        dwvOemr.html.toggleDisplay(spannode);
        dwvOemr.html.toggleDisplay(app.getElement("toolList"));
    };

    this.display = function (flag) {
        base.display(flag);
    };
    this.initialise = function () {
        base.initialise();
    };

};

// special setup
dwvOemr.gui.setup = function () {
    // override standard dwv library file/url functions to accommodate our secure
    // document fetch controller if we are in Patient Documents. v0.27 changed how
    // image url's are parsed for ext etc. Ignore if running stand alone.
    if ($("#dwv").attr("src")) {
        dwv.utils.getUrlFromUri = function (uri) {
            var url = null;
            if (dwv.env.askModernizr('urlparser') &&
                dwv.env.askModernizr('urlsearchparams')) {
                url = dwv.utils.getUrlFromUriFull(uri);
            } else {
                url = dwv.utils.getUrlFromUriSimple(uri);
            }
            url.pathname = url.search;
            return url;
        };

        dwv.utils.getFileExtension = function (filePath) {
            var ext = null;
            if (typeof filePath !== 'undefined' && filePath) {
                var pathSplit = filePath.split('.');
                if (pathSplit.length !== 1) {
                    ext = pathSplit.pop().toLowerCase();
                }
            }
            return ext;
        };
    }
};
