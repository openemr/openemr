/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

$(document).ready(function() {
    const lockModal = $("#lock-modal");
    const loadingCircle = $("#loading-circle");
    const form = $("#lifemesh-form");

    form.on('submit', function(e) {

        const firstname = $("input[name=user_firstname]").val();
        const lastname = $("input[name=user_lastname]").val();

        // lock down the form
        lockModal.css("display", "block");
        loadingCircle.css("display", "block");

        form.children("input").each(function() {
            $(this).attr("readonly", true);
        });

        setTimeout(function() {
            // re-enable the form
            lockModal.css("display", "none");
            loadingCircle.css("display", "none");

            form.children("input").each(function() {
                $(this).attr("readonly", false);
            });

        }, 9000);
    });

});
