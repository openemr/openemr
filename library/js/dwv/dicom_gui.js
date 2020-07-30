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
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

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
dwv.gui.prompt = dwv.gui.base.prompt;
// get element
dwv.gui.getElement = dwv.gui.base.getElement;
dwv.gui.displayProgress = function (percent) {
    if (percent <= 100) {
        var elem = document.getElementById("progressbar");
        elem.style.width = percent + "%";
    }
};
//Reset
dwv.gui.appendResetHtml = function (app) {
    var button = document.createElement("button");
    button.className = "reset-button";
    button.value = "reset";
    button.onclick = app.onDisplayReset;
    button.appendChild(document.createTextNode(dwv.i18n("basics.reset")));

    var node = app.getElement("toolbar");
    node.appendChild(button);
};
dwv.gui.refreshElement = dwv.gui.base.refreshElement;
dwv.gui.Slider = dwv.gui.base.Slider;
// edit state json. 0.27.0 has this as library function
const getState = function (app) {
    var state = new dwv.State();
    return state.toJSON(app);
};

// tool toggle
function toggle(popupId) {
    let popup = document.getElementById(popupId);
    popup.classList.toggle("show-popup");
}

// plot
dwv.gui.plot = function (div, data, options) {
    var plotOptions = {
        "bars": {"show": true},
        "grid": {"backgroundcolor": null},
        "xaxis": {"show": true},
        "yaxis": {"show": false}
    };
    if (typeof options !== "undefined" &&
        typeof options.markings !== "undefined") {
        plotOptions.grid.markings = options.markings;
    }
    $.plot(div, [data], plotOptions);
};
// Post process table
dwv.gui.postProcessTable = function (table) {
    var tableClass = table.className;
    // css
    table.className += " table-stripe ui-responsive";
    // add columntoggle
    table.setAttribute("data-role", "table");
    table.setAttribute("data-mode", "columntoggle");
    table.setAttribute("data-column-btn-text", dwv.i18n("basics.columns") + "...");
    // add priority columns for columntoggle
    var addDataPriority = function (cell) {
        var text = cell.firstChild.data;
        if (tableClass === "tagsTable") {
            if (text !== "value" && text !== "name") {
                cell.setAttribute("data-priority", "5");
            }
        } else if (tableClass === "drawsTable") {
            if (text === "description") {
                cell.setAttribute("data-priority", "1");
            } else if (text === "frame" || text === "slice") {
                cell.setAttribute("data-priority", "5");
            }

        }
    };
    if (table.rows.length !== 0) {
        var hCells = table.rows.item(0).cells;
        for (var c = 0; c < hCells.length; ++c) {
            addDataPriority(hCells[c]);
        }
    }
    // return
    return table;
};
// Tags table
dwv.gui.DicomTags = dwv.gui.base.DicomTags;
// DrawList table
dwv.gui.DrawList = dwv.gui.base.DrawList;
// Loaders
dwv.gui.Loadbox = dwv.gui.base.Loadbox;
// File loader
dwv.gui.FileLoad = dwv.gui.base.FileLoad;
// Folder loader
dwv.gui.FolderLoad = dwv.gui.base.FolderLoad;
// Url loader
dwv.gui.UrlLoad = dwv.gui.base.UrlLoad;
// Toolbox
dwv.gui.Toolbox = function (app) {
    var base = new dwv.gui.base.Toolbox(app);
    // save document url
    dwv.oemrDocumentUrl = $("#dwv").attr("src") ?? false;
    dwv.oemrDocumentStateUrl = $("#state_url").val() ?? false;
    dwv.oemrDocumentDocId = $("#doc_id").val() ?? false;
    dwv.oemrDocumentCsrf = $("#csrf").val() ?? false;

    this.setup = function (list) {
        // toolbar
        base.setup(list);

        var undo = document.createElement("button");
        undo.setAttribute("class", "fa fa-undo");
        undo.title = "Undo";
        undo.onclick = function () {
            app.onUndo();
        };

        var redo = document.createElement("button");
        redo.setAttribute("class", "fa fa-repeat");
        redo.title = "Redo";
        redo.onclick = function () {
            app.onRedo();
        };

        // open
        var openSpan = document.createElement("span");
        openSpan.className = "fa fa-file-o";
        var open = document.createElement("button");
        open.appendChild(openSpan);
        open.title = dwv.i18n("basics.open");
        open.onclick = function () {
            dwv.gui.displayProgress(0);
            toggle("loaderlist");
        };

        // toolbox
        var toolboxSpan = document.createElement("span");
        toolboxSpan.className = "fa fa-wrench";
        var toolbox = document.createElement("button");
        toolbox.appendChild(toolboxSpan);
        toolbox.title = dwv.i18n("basics.toolbox");
        toolbox.onclick = function () {
            let toolList = app.getElement("toolList");
            dwv.html.toggleDisplay(toolList);
            dwv.html.toggleDisplay(app.getElement("editspan"));
        };

        // DICOM tags
        var tagsSpan = document.createElement("span");
        tagsSpan.className = "fa fa-list";
        var tags = document.createElement("button");
        tags.appendChild(tagsSpan);
        tags.title = dwv.i18n("basics.dicomTags");
        tags.onclick = function () {
            toggle("tagsPopup");
        };

        // draw list
        var drawListSpan = document.createElement("span");
        drawListSpan.className = "fa fa-pencil";
        var drawList = document.createElement("button");
        drawList.appendChild(drawListSpan);
        drawList.title = dwv.i18n("basics.drawList");
        drawList.onclick = function () {
            let drawList = app.getElement("drawList");
            dwv.html.toggleDisplay(drawList);
        };

        // image
        var imageSpan = document.createElement("span");
        imageSpan.className = "fa fa-image";
        var image = document.createElement("button");
        image.appendChild(imageSpan);
        image.title = dwv.i18n("basics.image");
        image.onclick = function () {
            let layerDialog = app.getElement("layerDialog");
            dwv.html.toggleDisplay(layerDialog);
        };

        // info
        var infoSpan = document.createElement("span");
        infoSpan.className = "fa fa-info";
        var info = document.createElement("button");
        info.appendChild(infoSpan);
        info.title = dwv.i18n("basics.info");
        info.onclick = function () {
            var infoLayer = app.getElement("infoLayer");
            dwv.html.toggleDisplay(infoLayer);
        };

        // help
        var helpSpan = document.createElement("span");
        helpSpan.className = "fa fa-question";
        var help = document.createElement("button");
        help.appendChild(helpSpan);
        help.title = dwv.i18n("basics.help");
        help.onclick = function () {
            toggle("helpPopup");
        };
        /**/
        // save state button
        if (dwv.oemrDocumentUrl) {
            var saveButton = document.createElement("button");
            saveButton.appendChild(document.createTextNode(dwv.i18n("Save Image Edits")));
            node = app.getElement("openData");
            node.appendChild(saveButton);
            saveButton.onclick = function () {
                let oemrStateSaveUrl = dwv.oemrDocumentStateUrl;
                let data = new FormData();
                let json = getState(app);
                data.append('action', 'save');
                data.append("json_data", json);
                data.append('csrf_token_form', dwv.oemrDocumentCsrf);
                data.append('doc_id', dwv.oemrDocumentDocId);
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
            saveButton.appendChild(document.createTextNode(dwv.i18n("Download Image Edits")));
            var toggleSaveState = document.createElement("a");
            toggleSaveState.onclick = function () {
                var blob = new Blob([getState(app)], {type: 'application/json'});
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
        if (dwv.oemrDocumentUrl) {
            var restoreButton = document.createElement("button");
            restoreButton.appendChild(document.createTextNode(dwv.i18n("Restore Image Edits")));
            node = app.getElement("openData");
            node.appendChild(restoreButton);
            // save state link
            restoreButton.onclick = function () {
                let oemrStateSaveUrl = dwv.oemrDocumentStateUrl;
                let data = new FormData();
                let json = getState(app);
                data.append('action', 'fetch');
                data.append('csrf_token_form', dwv.oemrDocumentCsrf);
                data.append('doc_id', dwv.oemrDocumentDocId);
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
                }).then(() => {
                    toggle("loaderlist");
                })
            };

            // turn off file loader if using OpenEMR controllers
            dwv.html.toggleDisplay(app.getElement("loaderlist"));
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

        dwv.gui.refreshElement(node);
        dwv.html.toggleDisplay(spannode);
        dwv.html.toggleDisplay(app.getElement("toolList"));
    };

    this.display = function (flag) {
        base.display(flag);
    };
    this.initialise = function (list) {
        base.initialise(list);
    };

};

// special setup
dwv.utils.getUrlFromUri = function (uri) {
    var url = null;
    if (dwv.browser.askModernizr('urlparser') &&
        dwv.browser.askModernizr('urlsearchparams')) {
        url = dwv.utils.getUrlFromUriFull(uri);
    } else {
        url = dwv.utils.getUrlFromUriSimple(uri);
    }
    url.pathname = url.search;
    return url;
};

// Window/level
dwv.gui.WindowLevel = dwv.gui.base.WindowLevel;
// Draw
dwv.gui.Draw = dwv.gui.base.Draw;
// ColourTool
dwv.gui.ColourTool = dwv.gui.base.ColourTool;
// ZoomAndPan
dwv.gui.ZoomAndPan = dwv.gui.base.ZoomAndPan;
// Scroll
dwv.gui.Scroll = dwv.gui.base.Scroll;
// Filter
dwv.gui.Filter = dwv.gui.base.Filter;

// Filter: threshold
dwv.gui.Threshold = dwv.gui.base.Threshold;
// Filter: sharpen
dwv.gui.Sharpen = dwv.gui.base.Sharpen;
// Filter: sobel
dwv.gui.Sobel = dwv.gui.base.Sobel;
// Undo/redo
dwv.gui.Undo = dwv.gui.base.Undo;
// Help
dwv.gui.appendHelpHtml = dwv.gui.base.appendHelpHtml;
// Version
dwv.gui.appendVersionHtml = dwv.gui.base.appendVersionHtml;
