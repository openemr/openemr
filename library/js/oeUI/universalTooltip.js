/**
 * Universal jquery tooltip
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
$(function () {
    $(document).tooltip({
        show: {
            delay: 400
        },
        hide: {
            delay: 0
        },
        position: {
            my: "center top",
            at: "center bottom-5",
            collision: "flipfit"
        }
    });
    $(this).click(function () {
        $(this).tooltip({
            hide: {
                delay: 0
            }
        });
    });
});
