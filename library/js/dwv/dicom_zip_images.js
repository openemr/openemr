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

    // dwv picks its image loader by the MIME type of the File (RawImageLoader: file.type.match("image.*")) and its DICOM
    // loader by the extension, so a picture needs its type or no loader is found for it.
    const PICTURE_TYPES = {jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', gif: 'image/gif', bmp: 'image/bmp'};

    /**
     * The MIME type to give the File that dwv gets for a name made by dicomFileName(): the picture type for a picture,
     * application/dicom for .dcm and '' for anything else.
     */
    const fileType = function (name) {
        if (/\.dcm$/i.test(name)) {
            return 'application/dicom';
        }
        const extension = name.split('.').pop().toLowerCase();
        return Object.prototype.hasOwnProperty.call(PICTURE_TYPES, extension) ? PICTURE_TYPES[extension] : '';
    };

    /**
     * Read a fetch() response into an ArrayBuffer. When the size is known (Content-Length) and the body can be read as a
     * stream, onProgress(received, total) is called after every chunk, so a large zip shows progress while it downloads.
     * Without a size or a stream reader the body is read in one go. A cancelled fetch (AbortController) rejects the read.
     * Note that a compressed transfer can report more bytes than Content-Length: the caller should cap the percentage.
     */
    const readResponse = function (response, onProgress) {
        const total = parseInt(response.headers.get('Content-Length'), 10) || 0;
        if (!total || !response.body || typeof response.body.getReader !== 'function') {
            return response.arrayBuffer();
        }
        const reader = response.body.getReader();
        const chunks = [];
        let received = 0;
        const pump = function () {
            return reader.read().then(function (result) {
                if (result.done) {
                    const all = new Uint8Array(received);
                    let offset = 0;
                    chunks.forEach(function (chunk) {
                        all.set(chunk, offset);
                        offset += chunk.length;
                    });
                    return all.buffer;
                }
                chunks.push(result.value);
                received += result.value.length;
                onProgress(received, total);
                return pump();
            });
        };
        return pump();
    };

    // Did the user cancel (AbortController.abort() rejects a fetch with an AbortError)?
    const isAbortError = function (error) {
        return !!error && error.name === 'AbortError';
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
        // A failure to read an entry or to hand it to the viewer (for example dwv finds no loader for the file): the buttons
        // work again; the first image rethrows so that the caller can fall back, a later one is reported to onError.
        const fail = function (error) {
            busy = false;
            loading = false;
            updateNav();
            if (!started) {
                throw error;
            }
            console.log("DICOM zip entry could not be shown: " + error);
            if (onError) {
                onError(error);
            }
        };
        const show = function (index) {
            const mine = ++request;
            busy = true;
            updateNav();
            return read(index).then(function (buffer) {
                if (mine !== request) {
                    return;
                }
                loading = true;
                try {
                    loadFile(buffer, dicomFileName(entries[index].name), !started);
                } catch (error) {
                    // the viewer refused the image: the selection and the counter stay on the image that is shown
                    fail(error);
                    return;
                }
                current = index;
                ui.count.textContent = (index + 1) + " / " + entries.length;
                started = true;
                updateNav();
            }, function (error) {
                if (mine !== request) {
                    return;
                }
                fail(error);
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
        fileType: fileType,
        readResponse: readResponse,
        isAbortError: isAbortError,
        createNavigator: createNavigator
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = dwvOemr.zipImages;
}
