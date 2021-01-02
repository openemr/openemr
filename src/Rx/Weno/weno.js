/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

document.getElementById('connected').addEventListener('click', runScript);

function runScript()
{
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'wenoconnected.php', true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if(this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            if (this.responseText === 'imported') {
                    alert('Update Complete');
                $('#loading').hide();
                }
        }
    }

    xhr.send()
}