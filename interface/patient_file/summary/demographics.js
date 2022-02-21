/* Reformat EMR Patient Record Display
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MD Support<mdsupport@users.sourceforge.net>
 * @copyright Copyright (c) 2021-2022 MD Support<mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

$('body')
.addClass('m-0')
.removeClass('mt-3');

$('body > nav.navbar:first')
.addClass('navbar-secondary bg-secondary text-white m-0 p-0')
.removeClass('navbar-light bg-light');

$('#ptMenuNavbar > ul > li')
.addClass('mx-2');

$('#ptMenuNavbar > ul > li a')
.addClass('text-white my-0 py-0')
.removeClass('text-body');

$('#ptMenuNavbar .dropdown-menu a')
.addClass('text-body py-1')
.removeClass('text-white');

// Replace generic Dashboard label by patient name
try
{
    let objPt = $('#dashboard').data('ptdata');
    let disp = objPt.lfname;
    if (objPt.deceased_date !== null) {
        disp += `<i class="fas fa-exclamation-triangle ml-1 bg-light text-warning" data-toggle="tooltip" title="${objPt.inactive_days}"></i>`;
    }
    $('#dashboard a.nav-link:first').html(disp);
    $('[data-toggle="tooltip"]').tooltip();
    $('#demo-tab1 h2:first').remove();
}
catch(e)
{
    
};

$('a.btn.deleter.delete').each(function(ix) {
    let href = this.getAttribute('href');
    $('#act_delete > a.nav-link').attr('href', href);
    $(this).closest('div.form-group').remove();
});

// MenuClickHandler
$('#ptMenuNavbar .nav-link:not(".dropdown-toggle")').on('click', function(evClick) {
    evClick.preventDefault();
    let evHref = evClick.target.getAttribute('href');
    console.log(evHref);
    $.ajax({
        url: evHref,
        success: function(msg){
          $('#demo-tab1').html(msg)
        }
    });
});
