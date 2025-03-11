document.addEventListener('DOMContentLoaded', function () {
    if (phpData.groupDisabled === 'true') {
        document.querySelectorAll("input, select").forEach(function (element) {
            element.disabled = true;
        });
    }
});
