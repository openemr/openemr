// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Loadbox base gui.
 * @constructor
 */
dwvOemr.gui.Loadbox = function (app)
{
    var loaderGuis = {};

    /**
     * Setup the loadbox HTML.
     */
    this.setup = function (list)
    {
        // loader select
        var loaderSelector = dwvOemr.html.createHtmlSelect("loaderSelect", list, "io");
        loaderSelector.onchange = function (event) {
            // show tool gui
            for ( var gui in loaderGuis ) {
                loaderGuis[gui].display(false);
            }
            loaderGuis[event.currentTarget.value].display(true);
        };

        // get node
        var node = app.getElement("loaderlist");
        // clear it
        while(node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }
        // append selector
        node.appendChild(loaderSelector);
        // refresh
        dwvOemr.gui.refreshElement(node);

        // create tool guis and call setup
        loaderGuis = [];
        var first = true;
        for ( var key in list ) {
            var name = list[key];
            var guiClass = name + "Load";
            if (typeof dwvOemr.gui[guiClass] === "undefined") {
                console.warn("Could not create unknown loader gui: "+guiClass);
                continue;
            }
            var gui = new dwvOemr.gui[guiClass](app);
            gui.setup();
            // display
            gui.display(first);
            if (first) {
                first = false;
            }
            // store
            loaderGuis[name] = gui;
        }
    };

}; // class dwvOemr.gui.Loadbox

/**
 * FileLoad base gui.
 * @constructor
 */
dwvOemr.gui.FileLoad = function (app)
{
    // closure to self
    var self = this;

    /**
     * Internal file input change handler.
     * @param {Object} event The change event.
     */
    function onchangeinternal(event) {
        if (typeof self.onchange === "function") {
            self.onchange(event);
        }
        app.loadFiles(event.target.files);
    }

    /**
     * Setup the file load HTML to the page.
     */
    this.setup = function()
    {
        // input
        var fileLoadInput = document.createElement("input");
        fileLoadInput.onchange = onchangeinternal;
        fileLoadInput.type = "file";
        fileLoadInput.multiple = true;
        fileLoadInput.className = "imagefiles";
        fileLoadInput.setAttribute("data-clear-btn","true");
        fileLoadInput.setAttribute("data-mini","true");

        // associated div
        var fileLoadDiv = document.createElement("div");
        fileLoadDiv.className = "imagefilesdiv";
        fileLoadDiv.style.display = "none";
        fileLoadDiv.appendChild(fileLoadInput);

        // node
        var node = app.getElement("loaderlist");
        // append
        node.appendChild(fileLoadDiv);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the file load HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // file div element
        var node = app.getElement("loaderlist");
        var filediv = node.getElementsByClassName("imagefilesdiv")[0];
        filediv.style.display = bool ? "" : "none";
    };

}; // class dwvOemr.gui.FileLoad

/**
 * FolderLoad base gui.
 * @constructor
 */
dwvOemr.gui.FolderLoad = function (app)
{
    // closure to self
    var self = this;

    /**
     * Internal file input change handler.
     * @param {Object} event The change event.
     */
    function onchangeinternal(event) {
        if (typeof self.onchange === "function") {
            self.onchange(event);
        }
        app.loadFiles(event.target.files);
    }

    /**
     * Setup the file load HTML to the page.
     */
    this.setup = function()
    {
        // input
        var fileLoadInput = document.createElement("input");
        fileLoadInput.onchange = onchangeinternal;
        fileLoadInput.type = "file";
        fileLoadInput.multiple = true;
        fileLoadInput.webkitdirectory = true;
        fileLoadInput.className = "imagefolder";
        fileLoadInput.setAttribute("data-clear-btn","true");
        fileLoadInput.setAttribute("data-mini","true");

        // associated div
        var folderLoadDiv = document.createElement("div");
        folderLoadDiv.className = "imagefolderdiv";
        folderLoadDiv.style.display = "none";
        folderLoadDiv.appendChild(fileLoadInput);

        // node
        var node = app.getElement("loaderlist");
        // append
        node.appendChild(folderLoadDiv);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the folder load HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // file div element
        var node = app.getElement("loaderlist");
        var folderdiv = node.getElementsByClassName("imagefolderdiv")[0];
        folderdiv.style.display = bool ? "" : "none";
    };

}; // class dwvOemr.gui.FileLoad

/**
 * UrlLoad base gui.
 * @constructor
 */
dwvOemr.gui.UrlLoad = function (app)
{
    // closure to self
    var self = this;

    /**
     * Internal url input change handler.
     * @param {Object} event The change event.
     */
    function onchangeinternal(event) {
        if (typeof self.onchange === "function") {
            self.onchange(event);
        }
        app.loadURLs([event.target.value]);
    }

    /**
     * Setup the url load HTML to the page.
     */
    this.setup = function ()
    {
        // input
        var urlLoadInput = document.createElement("input");
        urlLoadInput.onchange = onchangeinternal;
        urlLoadInput.type = "url";
        urlLoadInput.className = "imageurl";
        urlLoadInput.setAttribute("data-clear-btn","true");
        urlLoadInput.setAttribute("data-mini","true");

        // associated div
        var urlLoadDiv = document.createElement("div");
        urlLoadDiv.className = "imageurldiv";
        urlLoadDiv.style.display = "none";
        urlLoadDiv.appendChild(urlLoadInput);

        // node
        var node = app.getElement("loaderlist");
        // append
        node.appendChild(urlLoadDiv);
        // refresh
        dwvOemr.gui.refreshElement(node);
    };

    /**
     * Display the url load HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // url div element
        var node = app.getElement("loaderlist");
        var urldiv = node.getElementsByClassName("imageurldiv")[0];
        urldiv.style.display = bool ? "" : "none";
    };

}; // class dwvOemr.gui.UrlLoad
