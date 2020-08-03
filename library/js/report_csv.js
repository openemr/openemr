/*
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

function download_csv(csv, filename) {
    let csvFile;
    let downloadLink;

    // CSV FILE
    csvFile = new Blob(["\uFEFF"+csv], {type: 'text/csv;charset=utf-8;'});

    // Creates Download link
    downloadLink = document.createElement('a');

    // File name
    downloadLink.download = filename;

    // We have to create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Make sure that the link is not displayed
    downloadLink.style.display = "none";

    // Add the link to your DOM
    document.body.appendChild(downloadLink);

    // Lanzamos
    downloadLink.click();
}

function export_table_to_csv(html, filename) {
    const csv = [];
    const rows = document.querySelectorAll("#export tr");

    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll("td, th");

        for (let j = 0; j < cols.length; j++)
            row.push(cols[j].innerText);
        csv.push(row.join(","));
    }

    // Download CSV
    download_csv(csv.join("\n"), filename);
}
if (document.querySelector("button") != null) {
    document.querySelector("button").addEventListener("click", function () {
        let reportname;
        let rn;
        reportname = document.getElementById('csv-report').name;
        alert(reportname);
        const html = document.querySelector("table").innerHTML;
        rn = reportname + '.csv';
        export_table_to_csv(html, rn);
    });
}
