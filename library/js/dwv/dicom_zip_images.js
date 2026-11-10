/**
 * Helpers of the DICOM viewer for a zip of DICOM images (document type application/dicom+zip).
 *
 * dwv stacks the files of one load into a single volume, which fails for files of different sizes, so the
 * viewer reads the zip itself and loads one image at a time with previous/next buttons. The logic that does
 * not need dwv or the DOM of the page lives here so that it can be tested (tests/js/dicom-zip-images.test.js).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var dwvOemr = dwvOemr || {};

dwvOemr.zipImages = (function () {
    /**
     * Is this the url of a zip document (as built by the documents controller)?
     */
    const isZipDocumentUrl = function (url) {
        return /[?&]type=\.zip(&|$)/.test(url);
    };

    /**
     * Only the images of a zip: skip folders, the __MACOSX/ resource forks, dot files and the DICOMDIR index of
     * an imaging-CD export, and return them in name order (zips are not always written sorted).
     */
    const selectImageEntries = function (files) {
        return Object.values(files).filter(function (file) {
            const base = file.name.split('/').pop();
            return !file.dir
                && !/(^|\/)__MACOSX\//.test(file.name)
                && base.charAt(0) !== '.'
                && base.toUpperCase() !== 'DICOMDIR';
        }).sort(function (a, b) {
            return a.name.localeCompare(b.name, undefined, {numeric: true});
        });
    };

    /**
     * The file name dwv gets for a zip entry: dwv decides by the extension, so one is added when missing.
     */
    const dicomFileName = function (entryName) {
        const name = entryName.split('/').pop();
        return /\.dcm$/i.test(name) ? name : name + '.dcm';
    };

    /**
     * Previous/next navigation. One image is read and loaded at a time: the buttons stay disabled until the
     * load ended, and a read that a newer click has replaced is dropped, so the counter always matches the
     * image shown.
     *
     * @param entries  the zip entries (objects with name and async('arraybuffer'))
     * @param ui       {prev, next, count} elements
     * @param loadFile function (buffer, name, isFirst) that hands the image to the viewer
     */
    const createNavigator = function (entries, ui, loadFile) {
        let current = 0;
        let started = false;
        let busy = false;
        let request = 0;
        const updateNav = function () {
            ui.prev.disabled = busy || current === 0;
            ui.next.disabled = busy || current === entries.length - 1;
        };
        const show = function (index) {
            const mine = ++request;
            busy = true;
            current = index;
            ui.count.textContent = (index + 1) + " / " + entries.length;
            updateNav();
            return entries[index].async('arraybuffer').then(function (buffer) {
                if (mine !== request) {
                    return;
                }
                loadFile(buffer, dicomFileName(entries[index].name), !started);
                started = true;
            }).catch(function (error) {
                console.log("DICOM zip entry could not be read: " + error);
                if (mine === request) {
                    busy = false;
                    updateNav();
                }
            });
        };
        ui.prev.onclick = function () {
            if (!busy && current > 0) {
                show(current - 1);
            }
        };
        ui.next.onclick = function () {
            if (!busy && current < entries.length - 1) {
                show(current + 1);
            }
        };
        return {
            show: show,
            // call when the viewer finished loading the image
            loadEnded: function () {
                busy = false;
                updateNav();
            },
            current: function () {
                return current;
            }
        };
    };

    return {
        isZipDocumentUrl: isZipDocumentUrl,
        selectImageEntries: selectImageEntries,
        dicomFileName: dicomFileName,
        createNavigator: createNavigator
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = dwvOemr.zipImages;
}
