// namespaces
var dwv = dwv || {};
dwv.decoder = dwv.decoder || {};

/**
 * RLE (Run-length encoding) decoder class.
 * @constructor
 */
dwv.decoder.RleDecoder = function () {};

/**
 * Decode a RLE buffer.
 * @param {Array} buffer The buffer to decode.
 * @param {Number} bitsAllocated The bits allocated per element in the buffer.
 * @param {Boolean} isSigned Is the data signed.
 * @param {Number} sliceSize The size of a slice
    (number of rows per number of columns).
 * @param {Number} samplesPerPixel The number of samples per pixel (3 for RGB).
 * @param {Number} planarConfiguration The planar configuration.
 * @returns The decoded buffer.
 * @see http://dicom.nema.org/dicom/2013/output/chtml/part05/sect_G.3.html
 */
dwv.decoder.RleDecoder.prototype.decode = function ( buffer,
    bitsAllocated, isSigned, sliceSize, samplesPerPixel, planarConfiguration ) {

    // bytes per element
    var bpe = bitsAllocated / 8;

    // input
    var inputDataView = new DataView(buffer.buffer, buffer.byteOffset);
    var inputArray = new Int8Array(buffer.buffer, buffer.byteOffset);
    // output
    var outputBuffer = new ArrayBuffer(sliceSize * samplesPerPixel * bpe);
    var outputArray = new Int8Array(outputBuffer);

    // first value of the RLE header is the number of segments
    var numberOfSegments = inputDataView.getInt32(0, true);

    // index increment in output array
    var outputIndexIncrement = 1;
    var incrementFactor = 1;
    if (samplesPerPixel !== 1 && planarConfiguration === 0) {
        incrementFactor *= samplesPerPixel;
    }
    if (bpe !== 1 ) {
        incrementFactor *= bpe;
    }
    outputIndexIncrement *= incrementFactor;

    // loop on segments
    var outputIndex = 0;
    var inputIndex = 0;
    var remainder = 0;
    var maxOutputIndex = 0;
    var groupOutputIndex = 0;
    for (var segment = 0; segment < numberOfSegments; ++segment) {
        // handle special cases:
        // - more than one sample per pixel: one segment per channel
        // - 16bits: sort high and low bytes
        if (incrementFactor !== 1) {
            remainder = segment % incrementFactor;
            if (remainder === 0) {
                groupOutputIndex = maxOutputIndex;
            }
            outputIndex = groupOutputIndex + remainder;
            // 16bits data
            if (bpe === 2) {
                outputIndex += (remainder % bpe ? -1 : 1);
            }
        }

        // RLE header: list of segment sizes
        var segmentStartIndex = inputDataView.getInt32((segment + 1) * 4, true);
        var nextSegmentStartIndex = inputDataView.getInt32((segment + 2) * 4, true);
        if (segment === numberOfSegments - 1 || nextSegmentStartIndex === 0) {
            nextSegmentStartIndex = buffer.length;
        }
        // decode segment
        inputIndex = segmentStartIndex;
        var count = 0;
        while (inputIndex < nextSegmentStartIndex) {
            // get the count value
            count = inputArray[inputIndex];
            ++inputIndex;
            // store according to count
            if (count >= 0 && count <= 127) {
                // output the next count+1 bytes literally
                for (var i = 0; i < count + 1; ++i) {
                    // store
                    outputArray[outputIndex] = inputArray[inputIndex];
                    // increment indexes
                    ++inputIndex;
                    outputIndex += outputIndexIncrement;
                }
            } else if (count <= -1 && count >= -127) {
                // output the next byte -count+1 times
                var value = inputArray[inputIndex];
                ++inputIndex;
                for (var j = 0; j < -count + 1; ++j) {
                    // store
                    outputArray[outputIndex] = value;
                    // increment index
                    outputIndex += outputIndexIncrement;
                }
            }
        }

        if (outputIndex > maxOutputIndex) {
            maxOutputIndex = outputIndex;
        }
    }

    var decodedBuffer = null;
    if (bitsAllocated === 8) {
        if (isSigned) {
            decodedBuffer = new Int8Array(outputBuffer);
        } else {
            decodedBuffer = new Uint8Array(outputBuffer);
        }
    } else if (bitsAllocated === 16) {
        if (isSigned) {
            decodedBuffer = new Int16Array(outputBuffer);
        } else {
            decodedBuffer = new Uint16Array(outputBuffer);
        }
    }

    return decodedBuffer;
};
