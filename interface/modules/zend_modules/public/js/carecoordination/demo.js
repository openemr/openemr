$(function () {
    // first example
    $('#browser').treeview();

    // second example
    $('#navigation').treeview({
        persist: 'location',
        collapsed: true,
        unique: true,
    });

    // third example
    $('#red').treeview({
        animated: 'fast',
        collapsed: true,
        unique: true,
        persist: 'cookie',
        toggle() {
            // eslint-disable-next-line no-unused-expressions
            window.console && console.log('%o was toggled', this);
        },
    });

    // fourth example
    $('#black, #gray').treeview({
        control: '#treecontrol',
        persist: 'cookie',
        cookieId: 'treeview-black',
    });
});
