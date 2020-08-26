// eslint-disable-next-line no-var
var type_ahead = function (args) {
    const url = args.url;
    const inputId = args.inputId;

    const fn_work = function () {

    };

    const fn_wire_events = function () {
        $(function () {
            $(`#${inputId}`).autocomplete(
                url, {
                    delay: 10,
                    minChars: 2,
                    matchSubset: 1,
                    matchContains: 1,
                    cacheLength: 10,
                },
            );
        });
    };

    return {
        init() {
            $(function () {
                fn_wire_events();
                fn_work();
            });
        },
    };
};
