// TODO: Convert to jqTree instead of jQuery TreeView

$(function() {
    // first example
    $("#browser").tree();

    // second example
    $("#navigation").tree({
        persist: "location",
        collapsed: true,
        unique: true
    });

    // third example
    $("#red").tree({
        animated: "fast",
        collapsed: true,
        unique: true,
        persist: "cookie",
        toggle: function() {
            window.console && console.log("%o was toggled", this);
        }
    });

    // fourth example
    $("#black, #gray").tree({
        control: "#treecontrol",
        persist: "cookie",
        cookieId: "treeview-black"
    });

});
