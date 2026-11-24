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

    // Files an imaging-CD export carries next to the images (readme, autorun, viewers, links); they are not images.
    const NON_IMAGE_NAME = /\.(txt|inf|exe|dll|html?|ini|xml|pdf|bat|cmd|js|css|json|md|csv|log|bin|zip|url|lnk)$/i;
    // Pictures the browser side of dwv reads itself; they keep their own extension.
    const PICTURE_NAME = /\.(jpe?g|png|gif|bmp)$/i;

    /**
     * Only the images of a zip: skip folders, the __MACOSX/ resource forks, dot files, the DICOMDIR index and the
     * non-image files of an imaging-CD export, and return them in name order (zips are not always written sorted).
     */
    const selectImageEntries = function (files) {
        return Object.values(files).filter(function (file) {
            const base = file.name.split('/').pop();
            return !file.dir
                && !/(^|\/)__MACOSX\//.test(file.name)
                && base.charAt(0) !== '.'
                && base.toUpperCase() !== 'DICOMDIR'
                && !NON_IMAGE_NAME.test(base);
        }).sort(function (a, b) {
            return a.name.localeCompare(b.name, undefined, {numeric: true});
        });
    };

    /**
     * The file name dwv gets for a zip entry: dwv decides by the extension, so .dcm is added unless the name already
     * has it or is a picture (JPEG, PNG, ...) that dwv reads by its own extension.
     */
    const dicomFileName = function (entryName) {
        const name = entryName.split('/').pop();
        return /\.dcm$/i.test(name) || PICTURE_NAME.test(name) ? name : name + '.dcm';
    };

    /**
     * Previous/next navigation. One image is read and loaded at a time: the buttons stay disabled until the
     * load ended, and a read that a newer click has replaced is dropped. The counter and the current index
     * change only once an image was read and handed to the viewer, so they always match the image shown.
     * The last two entries read are kept, so flipping between two images does not inflate them again.
     *
     * show() rejects when the very first image cannot be read (nothing is shown yet, so the caller can fall back
     * to another loader); a later failure leaves the image on screen as it was and is reported to onError.
     *
     * @param entries  the zip entries (objects with name and async('arraybuffer'))
     * @param ui       {prev, next, count} elements
     * @param loadFile function (buffer, name, isFirst) that hands the image to the viewer
     * @param onError  function (error) called when a later image cannot be read
     */
    const createNavigator = function (entries, ui, loadFile, onError) {
        let current = 0;
        let started = false;
        let busy = false;
        let loading = false;
        let request = 0;
        const recent = [];
        ui.count.textContent = "1 / " + entries.length;
        const updateNav = function () {
            ui.prev.disabled = busy || current === 0;
            ui.next.disabled = busy || current === entries.length - 1;
        };
        const read = function (index) {
            const hit = recent.find(function (item) {
                return item.index === index;
            });
            if (hit) {
                return Promise.resolve(hit.buffer);
            }
            return entries[index].async('arraybuffer').then(function (buffer) {
                recent.unshift({index: index, buffer: buffer});
                recent.length = Math.min(recent.length, 2);
                return buffer;
            });
        };
        const show = function (index) {
            const mine = ++request;
            busy = true;
            updateNav();
            return read(index).then(function (buffer) {
                if (mine !== request) {
                    return;
                }
                current = index;
                ui.count.textContent = (index + 1) + " / " + entries.length;
                loading = true;
                loadFile(buffer, dicomFileName(entries[index].name), !started);
                started = true;
            }, function (error) {
                if (mine !== request) {
                    return;
                }
                busy = false;
                loading = false;
                updateNav();
                if (!started) {
                    throw error;
                }
                console.log("DICOM zip entry could not be read: " + error);
                if (onError) {
                    onError(error);
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
            // call when the viewer finished loading the image; a load end that this navigator did not start is ignored
            loadEnded: function () {
                if (!loading) {
                    return;
                }
                loading = false;
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
