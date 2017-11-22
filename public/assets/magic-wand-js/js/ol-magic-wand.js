//
// Magic Wand Control for Openlayers 2.13
//
// The MIT License (MIT)
//
// Copyright (c) 2014, Ryasnoy Paul (ryasnoypaul@gmail.com)
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

/**
 * Class: OpenLayers.Tile.Mask
 * Tile for displaying binary mask
 *
 * Inherits from:
 *  - <OpenLayers.Tile>
 */
OpenLayers.Tile.Mask = OpenLayers.Class(OpenLayers.Tile, {

    /** 
     * Property: cvsDiv
     * {HTMLCanvasElement} The canvas for this tile.
     */
    cvsDiv: null,

    /**
     * Property: canvasContext
     * {CanvasRenderingContext2D} A canvas context associated with
     * the tile mask.
     */
    canvasContext: null,

    /**
     * Property: image
     * {Object} Binary mask is contains {Uint8Array} data, {int} width, {int} height, {Object} bounds
     */
    image: null,

    // private
    border: null,

    //private
    hatchInterval: null,
    
    //private
    hatchOffset: 0,

    initialize: function (layer, position, size, options) {
        OpenLayers.Tile.prototype.initialize.call(this, layer, position, null, null, size, options);

        this.createCanvas();
        if (position) this.setPosition(position);
        if (size) this.setCanvasSize(size);

        var me = this;
        if (this.layer.hatchTimeout && this.layer.hatchTimeout > 0)
            this.hatchInterval = setInterval(function () { me.hatchTick(); }, this.layer.hatchTimeout);
    },
    
    /** 
     * APIMethod: destroy
     * Nullify references to prevent circular references and memory leaks
     */
    destroy: function () {
        if (this.hatchInterval) clearInterval(this.hatchInterval); // stop hatching animation
        if (this.cvsDiv) {
            this.clear();
        }
        OpenLayers.Tile.prototype.destroy.call(this);
    },

    // override
    setBounds: function (bounds) {
        // stub
    },

    // override
    shouldDraw: function () {
        return true;
    },

    // override
    draw: function () {
        return this.drawBorder();
    },

    // private
    hatchTick: function() {
        this.hatchOffset = (this.hatchOffset + 1) % (this.layer.hatchLength * 2);
        return this.drawBorder(true);
    },

    /**
     * Method: setPosition
     */
    setPosition: function (position) {
        var style = this.cvsDiv.style;
        style.left = position.x + "px";
        style.top = position.y + "px";

        this.position = position;
    },

    /**
     * Method: setCanvasSize
     */
    setCanvasSize: function (size) {
        this.canvasContext.canvas.width = size.w;
        this.canvasContext.canvas.height = size.h;

        this.size = size;
    },
    
    // override
    clear: function () {
        OpenLayers.Tile.prototype.clear.call(this);
        if (this.cvsDiv) {
            if (this.cvsDiv.parentNode === this.layer.div) {
                this.layer.div.removeChild(this.cvsDiv);
            }
            this.cvsDiv = null;
        }
        this.canvasContext = null;
    },

    /**
     * Method: createCanvas
     * Creates and returns the tile canvas.
     */
    createCanvas: function () {
        this.cvsDiv = document.createElement("canvas");
        this.layer.div.appendChild(this.cvsDiv);

        this.canvasContext = this.cvsDiv.getContext("2d");

        this.className = "olTileMask";

        var style = this.cvsDiv.style;
        if (this.layer.opacity < 1) {
            style.filter = 'alpha(opacity=' +
                            (this.layer.opacity * 100) +
                            ')';
        }
        style.position = "absolute";
    },

    /**
     * Method: clearImage
     * Clear tile
     */
    clearImage: function () {
        this.image = null;
        this.border = null;
        if (this.canvasContext)
            this.canvasContext.clearRect(0, 0, this.size.w, this.size.h);
    },

    /**
     * Method: setImage
     * Set image (binary mask) for tile
     */
    setImage: function(image) {
        this.image = image;
        this.drawBorder();
    },

    /**
     * Method: drawBorder
     * Draw hatch border of image (binary mask)
     */
    drawBorder: function (noBorder) {
        if (!this.image) return false;

        var i, j, k, q, len,
            w = this.size.w, // viewport size
            h = this.size.h,
            ix = this.size.w - 1, // right bottom of viewport (left top = [0,0])
            iy = this.size.h - 1,
            sw = this.size.w + 2, // extend viewport size (+1 px on each side)
            sh = this.size.h + 2;

        if (!noBorder) { // need create border

            var dx, dy, x0, y0, x1, y1, k1, k2,
                rx0, rx1, ry0, ry1, // result of the intersection image with the viewport (+1 px on each side)
                img = this.image.data,
                w1 = this.image.width,
                b = this.image.bounds,
                of = this.image.globalOffset, // image offset in the viewport basis,
                data = new Uint8Array(sw * sh), // viewport data (+1 px on each side) for correct detection border
                minPx = Math.round(this.layer.map.minPx.x),
                minPy = Math.round(this.layer.map.minPx.y),
                maxPx = Math.round(this.layer.map.maxPx.x),
                pxLen = maxPx - minPx, // width in pixels of the one world
                offset,
                offsets = [{ // all posible world offsets in the viewport basis (considering wrapDateLine)
                    x: -minPx,
                    y: -minPy
                }, { // add left world
                    x: -(minPx - pxLen),
                    y: -minPy
                }, { // add right world
                    x: -(minPx + pxLen),
                    y: -minPy
                }];
            
            // walk through all worlds
            var offsetsLen = offsets.length;
            for (j = 0; j < offsetsLen; j++) {
                offset = offsets[j]; // world offset in the viewport basis
                dx = of.x - offset.x; // delta for the transformation in viewport basis
                dy = of.y - offset.y;
                x0 = dx + b.minX; // left top of image (in viewport basis)
                y0 = dy + b.minY;
                x1 = dx + b.maxX; // right bottom of image (in viewport basis)
                y1 = dy + b.maxY;

                // intersection of the image with viewport 
                if (!(x1 < 0 || x0 > ix || y1 < 0 || y0 > iy)) {
                    rx0 = x0 > -1 ? x0 : -1; // intersection +1 px on each side (for search border)
                    ry0 = y0 > -1 ? y0 : -1;
                    rx1 = x1 < ix + 1 ? x1 : ix + 1;
                    ry1 = y1 < iy + 1 ? y1 : iy + 1;
                } else {
                    continue;
                }
                // copy result of the intersection(+1 px on each side) to mask data for detection border
                len = rx1 - rx0 + 1;
                i = (ry0 + 1) * sw + (rx0 + 1);
                k1 = (ry0 - dy) * w1 + (rx0 - dx);
                k2 = (ry1 - dy) * w1 + (rx0 - dx) + 1;
                // walk through rows (Y)
                for (k = k1; k < k2; k += w1) {
                    // walk through cols (X)
                    for (q = 0; q < len; q++) {
                        if (img[k + q] === 1) data[i + q] = 1; // copy only "black" points
                    }
                    i += sw;
                }
            }

            // save result of border detection for animation
            this.border = MagicWand.getBorderIndices({ data: data, width: sw, height: sh });
        }

        this.canvasContext.clearRect(0, 0, w, h);

        var ind = this.border; // array of indices of the boundary points
        if (!ind) return false;

        var x, y,
            imgData = this.canvasContext.createImageData(w, h), // result image
            res = imgData.data,
            hatchLength = this.layer.hatchLength,
            hatchLength2 = hatchLength * 2,
            hatchOffset = this.hatchOffset;

        len = ind.length;
        
        for (j = 0; j < len; j++) {
            i = ind[j],
            x = i % sw; // calc x by index
            y = (i - x) / sw; // calc y by index
            x -= 1; // viewport coordinates transformed from extend (+1 px) viewport
            y -= 1;
            if (x < 0 || x > ix || y < 0 || y > iy) continue;
            k = (y * w + x) * 4; // result image index by viewport coordinates
            if ((x + y + hatchOffset) % hatchLength2 < hatchLength) { // detect hatch color 
                res[k + 3] = 255; // black, change only alpha
            } else {
                res[k] = 255; // white
                res[k + 1] = 255;
                res[k + 2] = 255;
                res[k + 3] = 255;
            }
        }

        this.canvasContext.putImageData(imgData, 0, 0);

        return true;
    },

    // use without wrapDateLine (faster than drawBorder)
    drawBorderOld: function (noBorder) {
        if (!this.image) return false;

        var ind, // array of indices of the boundary points
            nx0, nx1, ny0, ny1, // result of the intersection image with the viewport
            rx0, rx1, ry0, ry1, // result of the intersection image with the viewport (+1px on each side)
            w, h; // size of the intersection(+1px on each side)

        if (!noBorder) { // need create border
            // copy visible image data (+1 px on each side) for border search
            var offset = { // viewport offset in the global basis
                    x: Math.round(-this.layer.map.minPx.x),
                    y: Math.round(-this.layer.map.minPx.y)
                },
                img = this.image.data,
                w1 = this.image.width,
                b = this.image.bounds,
                of = this.image.globalOffset, // image offset in the global basis
                dx = of.x - offset.x, // delta for the transformation to viewport basis
                dy = of.y - offset.y,
                x0 = dx + b.minX, // left top of image (in viewport basis)
                y0 = dy + b.minY,
                x1 = dx + b.maxX, // right bottom of image (in viewport basis)
                y1 = dy + b.maxY,
                ix = this.size.w - 1, // right bottom of viewport (left top = [0,0])
                iy = this.size.h - 1;

            // intersection of the image with viewport 
            if (!(x1 < 0 || x0 > ix || y1 < 0 || y0 > iy)) {
                nx0 = x0 > 0 ? x0 : 0, // image fully visible in the viewport
                ny0 = y0 > 0 ? y0 : 0,
                nx1 = x1 < ix ? x1 : ix,
                ny1 = y1 < iy ? y1 : iy,
                rx0 = x0 > -1 ? x0 : -1; // intersection +1 px on each side (for search border)
                ry0 = y0 > -1 ? y0 : -1;
                rx1 = x1 < ix + 1 ? x1 : ix + 1;
                ry1 = y1 < iy + 1 ? y1 : iy + 1;
            } else {
                return false;
            }
            // copy result of the intersection(+1px on each side) to mask data for getBorderIndices
            w = rx1 - rx0 + 1;
            h = ry1 - ry0 + 1;
            var data = new Uint8Array(w * h),
                i = 0,
                k1 = (ry0 - dy) * w1 + (rx0 - dx),
                k2 = (ry1 - dy) * w1 + (rx0 - dx) + 1;
            // walk through rows (Y)
            for (var k = k1; k < k2; k += w1) {
                data.set(img.subarray(k, k + w), i); // copy row
                i += w;
            }
            ind = MagicWand.getBorderIndices({ data: data, width: w, height: h });

            //save border for animation
            this.border = {
                data: ind,
                offsetX: rx0,
                offsetY: ry0,
                minX: nx0,
                minY: ny0,
                maxX: nx1,
                maxY: ny1,
                width: w
            };
        } else {
            // restore the existing border
            ind = this.border.data;
            rx0 = this.border.offsetX;
            ry0 = this.border.offsetY;
            nx0 = this.border.minX;
            ny0 = this.border.minY;
            nx1 = this.border.maxX;
            ny1 = this.border.maxY;
            w = this.border.width;
        }

        var nw = nx1 - nx0 + 1,
            nh = ny1 - ny0 + 1,
            imgData = this.canvasContext.createImageData(nw, nh), // result image
            res = imgData.data,
            len = ind.length,
            x, y, j,
            hatchLength = this.layer.hatchLength,
            hatchLength2 = hatchLength * 2,
            hatchOffset = this.hatchOffset;

        for (j = 0; j < len; j++) {
            i = ind[j],
            x = i % w; // calc x by index
            y = (i - x) / w; // calc y by index
            x += rx0; // viewport coordinates
            y += ry0;
            if (x < nx0 || x > nx1 || y < ny0 || y > ny1) continue;
            k = ((y - ny0) * nw + x - nx0) * 4; // result image index by viewport coordinates
            if ((x + y + hatchOffset) % hatchLength2 < hatchLength) { // detect hatch color 
                res[k + 3] = 255; // black, change only alpha
            } else {
                res[k] = 255; // white
                res[k + 1] = 255;
                res[k + 2] = 255;
                res[k + 3] = 255;
            }
        }

        this.canvasContext.clearRect(0, 0, this.size.w, this.size.h);
        this.canvasContext.putImageData(imgData, nx0, ny0);

        return true;
    },

    /**
     * Method: getContours
     * Return contours of image (binary mask) by filter
     */
    getContours: function (filter) {
        if (!this.image) return null;

        var i, j, points, len, plen, c,
            image = this.image,
            dx = image.globalOffset.x + Math.round(this.layer.map.minPx.x),
            dy = image.globalOffset.y + Math.round(this.layer.map.minPx.y),
            contours = MagicWand.traceContours(image),
            result = [];

        if (this.layer.simplifyTolerant > 0) contours = MagicWand.simplifyContours(contours, this.layer.simplifyTolerant, this.layer.simplifyCount);
        len = contours.length;
        for (i = 0; i < len; i++) {
            c = contours[i];
            points = c.points;
            plen = points.length;
            c.initialCount = c.initialCount || plen;
            if (filter && filter(c) === false) continue;
            for (j = 0; j < plen; j++) {
                points[j].x += dx;
                points[j].y += dy;
            }
            result.push(contours[i]);
        }
        return result;
    },

    CLASS_NAME: "OpenLayers.Tile.Mask"

});

/**
 * Class: OpenLayers.Layer.Mask
 * Layer for displaying binary mask
 *
 * Inherits from:
 *  - <OpenLayers.Layer>
 */
OpenLayers.Layer.Mask = OpenLayers.Class(OpenLayers.Layer, {

    // override
    alwaysInRange: true,

    /**
     * Property: tile
     * {<OpenLayers.Tile.Mask>}
     */
    tile: null,

    /**
     * Property: hatchLength
     * {Integer} Thickness of the stroke (in pixels)
     */
    hatchLength: 4,

    /**
     * Property: hatchTimeout
     * {Integer} Hatching redraw timeout (in ms)
     */
    hatchTimeout: 300,

    /**
     * Property: simplifyTolerant
     * {Float} Tool parameter: Simplify tolerant (see method 'simplifyContours' in MagicWand.js)
     */
    simplifyTolerant: 1,

    /**
     * Property: simplifyCount
     * {Integer} Tool parameter: Simplify count (see method 'simplifyContours' in MagicWand.js)
     */
    simplifyCount: 30,

    // private
    resolution: null,
    
    // override
    destroy: function () {
        if (this.tile) {
            this.tile.destroy();
            this.tile = null;
        }
        OpenLayers.Layer.prototype.destroy.call(this);
    },

    // override
    moveTo: function (bounds, zoomChanged, dragging) {
        OpenLayers.Layer.prototype.moveTo.call(this, bounds, zoomChanged, dragging);
        // recreate tile only when resolution is changed
        if (Math.abs(this.map.resolution - this.resolution) > 0.000001 || !this.tile) {
            this.resolution = this.map.resolution; // save current mask resolution
            //create the new tile
            if (this.tile) this.tile.destroy();
            this.tile = new OpenLayers.Tile.Mask(this, this.getTilePosition(), this.getTileSize());
            this.events.triggerEvent("createtile", { "tile": this.tile });
        } else {
            //just resize the tile and set it's new position
            this.tile.setCanvasSize(this.getTileSize());
            this.tile.setPosition(this.getTilePosition());
        }
        this.tile.draw();
    },

    // override
    moveByPx: function (dx, dy) {
        this.tile.setPosition(this.getTilePosition());
        this.tile.draw();
    },

    /**
     * Method: getTileSize
     * Calc the tile size based on the map size.
     */
    getTileSize: function () {
        return this.map.getCurrentSize().clone();
    },

    /**
     * Method: getTilePosition
     * Calc the tile position based on the map position.
     */
    getTilePosition: function () {
        return new OpenLayers.Pixel(-this.map.layerContainerOriginPx.x, -this.map.layerContainerOriginPx.y);
    },
    
    onResize: function () {
        this.tile.setCanvasSize(this.getTileSize());
        this.tile.draw();
    },
    
    CLASS_NAME: "OpenLayers.Layer.Mask"
});

/**
 * Class: OpenLayers.Layer.Snapshot
 * Base snapshot class
 */
OpenLayers.Layer.Snapshot = OpenLayers.Class({

    /**
     * APIProperty: layer
     * {<OpenLayers.Layer>} Layer from which the snapshot will be made
     */
    layer: null,

    /**
     * Property: bytes
     * {Integer} Amount of bytes per pixel
     */
    bytes: 4,

    /**
     * Property: size
     * {Object} w = width, h = height
     */
    size: null,

    /**
     * Property: image
     * {Uint8Array} Snapshot data
     */
    image: null,

    /**
     * APIProperty: eventListeners
     * {Object} If set as an option at construction, the eventListeners
     *     object will be registered with <OpenLayers.Events.on>.  Object
     *     structure must be a listeners object as shown in the example for
     *     the events.on method.
     */
    eventListeners: null,

    /**
     * APIProperty: events
     * {<OpenLayers.Events>} An events object that handles all events on the map
     */
    events: null,

    initialize: function (layer, options) {
        OpenLayers.Util.extend(this, options);

        this.events = new OpenLayers.Events(this);
        if (this.eventListeners instanceof Object) {
            this.events.on(this.eventListeners);
        }

        this.layer = layer;
    },
    
    /**
     * Method: getPixelColor
     * Get color by screen coordinates
     */
    getPixelColor: function (x, y) {
        var i = (y * this.size.w + x) * this.bytes;
        var res = [this.image[i], this.image[i + 1], this.image[i + 2], this.image[i + 3]];
        return res;
    },
    
    /**
     * Method: toImageUrl
     * Create data URL from the snapshot image
     */
    toImageUrl: function (format) {
        if (!this.image || !this.size) return null;

        format = format || "image/png";
        var canvas = document.createElement("canvas");
        var context = canvas.getContext("2d");
        context.canvas.width = this.size.w;
        context.canvas.height = this.size.h;

        var imgData = context.createImageData(this.size.w, this.size.h);
        for (var i = 0; i < this.image.length; i++) {
            imgData.data[i] = this.image[i];
        }
        context.putImageData(imgData, 0, 0);
        
        return canvas.toDataURL(format);
    },

    /**
     * Method: isReady
     * Indicates whether or not the snapshot is fully loaded. This should be overriden by any subclass
     */
    isReady: function () {
        return false;
    },

    /**
     * Method: destroy
     * Destroy this snapshot
     */
    destroy: function () {
        this.image = null;
        this.events.triggerEvent('destroy', null);
        this.events.destroy();
        this.events = null;
    },

    CLASS_NAME: "OpenLayers.Layer.Snapshot"

});

/**
 * Class: OpenLayers.Layer.Snapshot.Grid
 * Snapshot for all grid layers
 *
 * Inherits from:
 *  - <OpenLayers.Layer.Snapshot>
 */
OpenLayers.Layer.Snapshot.Grid = OpenLayers.Class(OpenLayers.Layer.Snapshot, {
    
    // private
    cache: null,

    // private
    cacheCount: 0,
    
    // private
    tilesCount: 0,
    
    initialize: function (layer, options) {
        OpenLayers.Layer.Snapshot.prototype.initialize.call(this, layer, options);
        this.scan();
    },

    scan: function () {
        this.cacheCount = 0;
        this.cache = {};
        if (this.layer.map && this.layer.grid.length > 0) {
            this.events.triggerEvent("scanstart", { snapshot: this });

            this.size = this.layer.map.getCurrentSize();
            this.image = new Uint8Array(this.size.w * this.size.h * this.bytes);
            this.tilesCount = this.layer.grid.length * this.layer.grid[0].length;

            this.connectToLayer();

            for (var i = 0, lenI = this.layer.grid.length; i < lenI; i++) {
                for (var j = 0, lenJ = this.layer.grid[i].length; j < lenJ; j++) {
                    var tile = this.layer.grid[i][j];
                    if (tile.shouldDraw()) {
                        if (!tile.isLoading && tile.imgDiv) this.addToCache(tile);
                    } else {
                        this.tilesCount--;
                    }
                }
            }
            if (this.isReady()) this.onLoadEnd();
            return true;
        }
        return false;
    },

    // private
    connectToLayer: function () {
        this.layer.events.register('tileloaded', this, this.onTileLoaded);
    },

    // private
    disconnectFromLayer: function () {
        this.layer.events.unregister('tileloaded', this, this.onTileLoaded);
    },

    // private
    onTileLoaded: function (evt) {
        this.addToCache(evt.tile);
        if (this.isReady()) this.onLoadEnd();
    },
    
    // private
    onLoadEnd: function () {
        this.disconnectFromLayer();
        this.events.triggerEvent("scanfinish", { snapshot: this });
    },
    
    // private
    addToCache: function (tile) {
        if (!this.cache[tile.id]) {
            this.cache[tile.id] = tile;
            this.cacheCount++;
            try {
                this.addToImage(tile);
            } catch (e) {
                // stub
            } 
        }
    },
    
    // private
    addToImage: function (tile) {
        var tileWidth = this.layer.tileSize.w,
            tileHeight = this.layer.tileSize.h,
            imgData = tile.getCanvasContext().getImageData(0, 0, tileWidth, tileHeight),
            tileData = imgData.data,
            data = this.image,
            bytes = this.bytes,
            x = tile.position.x + this.layer.map.layerContainerOriginPx.x,  // tile position in viewport
            y = tile.position.y + this.layer.map.layerContainerOriginPx.y,
            w = this.size.w,
            x0 = Math.max(0, x),   // intersection of tile and shapshot (viewport)
            y0 = Math.max(0, y),
            x1 = Math.min(w, x + tileWidth) - 1,
            y1 = Math.min(this.size.h, y + tileHeight) - 1;

        if (x0 > x1 || y0 > y1) return; // tile isn't visible
        
        var i = (y0 * w + x0) * bytes,
            len = (x1 - x0 + 1) * bytes,
            w1 = tileWidth * bytes,
            k1 = ((y0 - y) * tileWidth + x0 - x) * bytes,
            k2 = ((y1 - y) * tileWidth + x0 - x + 1) * bytes;

        w *= bytes;
        // copy only the visible data of image to snapshot
        for (var k = k1; k < k2; k += w1) { // walk through rows (Y)
            data.set(tileData.subarray(k, k + len), i); // copy row
            i += w;
        }
    },

    // override
    isReady: function () {
        return this.tilesCount == this.cacheCount;
    },
    
    // override
    destroy: function () {
        this.cache = null;
        this.disconnectFromLayer();
        OpenLayers.Layer.Snapshot.prototype.destroy.call(this);
    },

    CLASS_NAME: "OpenLayers.Layer.Snapshot.Grid"
    
});

/**
 * Class: OpenLayers.Layer.Snapshot.Google
 * Snapshot for Google layers
 *
 * Inherits from:
 *  - <OpenLayers.Layer.Snapshot>
 */
OpenLayers.Layer.Snapshot.Google = OpenLayers.Class(OpenLayers.Layer.Snapshot, {

    // private
    cache: null,

    // private
    tilesAll: 0,

    // private
    tilesLoaded: 0,
    
    // private
    ready: false,
    
    // private
    googleListeners: null,
    
    initialize: function (layer, options) {
        OpenLayers.Layer.Snapshot.prototype.initialize.call(this, layer, options);

        this.cache = [];
        this.scan();
    },

    scan: function () {
        if (this.layer.map) {

            this.size = this.layer.map.getCurrentSize();

            this.connectToGoogle(); // if google tiles are not yet loaded
            this.onTilesLoad(); // if google tiles have been loaded already

            return true;
        }
        return false;
    },

    // private
    connectToGoogle: function () {
        var me = this;

        this.disconnectFromGoogle();

        this.googleListeners = [];
        this.googleListeners.push(google.maps.event.addListenerOnce(this.layer.mapObject, 'tilesloaded', function () {
            me.onGoogleTilesLoaded();
        }));
    },

    // private
    disconnectFromGoogle: function () {
        if (!this.googleListeners) return false;
        // remove all listeners
        for (var i = 0; i < this.googleListeners.length; i++) {
            google.maps.event.removeListener(this.googleListeners[i]);
        }
        this.googleListeners.length = 0;
        return true;
    },
    
    // private
    onGoogleIdle: function () {
        this.onTilesLoad();
    },

    // private
    onGoogleTilesLoaded: function () {
        var me = this;
        this.googleListeners.push(google.maps.event.addListenerOnce(this.layer.mapObject, 'idle', function () {
            me.onGoogleIdle();
        }));
        // force simulation of panning for trigger idle event 
        this.layer.mapObject.panBy(0, 1);
        this.layer.mapObject.panBy(0, -1);
    },
    
    // private
    getGoogleImages: function (layer) {
        if (!layer.getMapContainer) return [];
        // find all images in layer div
        var all = Array.prototype.slice.call(layer.getMapContainer().getElementsByTagName('img'));
        var len = all.length,
            images = [],
            tileSize = layer.tileSize,
            //zoom = "z=" + layer.map.getZoom(),
            i, tile;

        // filter google tiles with current zoom among all
        for (i = 0; i < len; i++) {
            tile = all[i];
            if (tile.src.search("google") != -1 /*&& tile.src.search(zoom) != -1*/ && tile.width == tileSize.w && tile.height == tileSize.h) {
                images.push(tile);
            }
        }

        return images;
    },

    // private
    onTilesLoad: function () {
        this.ready = false;
        this.tilesAll = 0;
        this.tilesLoaded = 0;
        this.image = new Uint8Array(this.size.w * this.size.h * this.bytes);

        this.disconnectFromImages();

        this.events.triggerEvent("scanstart", { snapshot: this });

        var images = this.getGoogleImages(this.layer),
            j, k, len, len2, wrongImages, index;
        var overviews = this.layer.map.getControlsByClass("OpenLayers.Control.OverviewMap");
        for (j = 0, len = overviews.length; j < len; j++) {
            wrongImages = this.getGoogleImages(overviews[j].ovmap.baseLayer);
            for (k = 0, len2 = wrongImages.length; k < len2; k++) {
                index = images.indexOf(wrongImages[k]);
                if (index != -1) {
                    images.splice(index, 1);
                }
            }
        }
        
        this.tilesAll = images.length;

        var view = this.layer.map.viewPortDiv.getBoundingClientRect();
        // create clones for google tile with anonymous crossorigin
        for (var i = 0; i < this.tilesAll; i++) {
            var tile = images[i];
            var bounds = tile.getBoundingClientRect();
            
            var img = new Image();
            this.cache.push(img);

            img.setAttribute("crossorigin", "anonymous");
            OpenLayers.Event.observe(img, "load",
                OpenLayers.Function.bind(this.onImageLoad, this, img, bounds.left - view.left, bounds.top - view.top)
            );
            img.src = tile.src; // set same url
        }
        
        return true;
    },

    // private
    onImageLoad: function (img, x, y) {
        this.addToImage(img, x, y); // add data of clone to snapshot
        if (++this.tilesLoaded == this.tilesAll) this.onLoadEnd(); // all clones of tiles are loaded
    },
    
    // private
    disconnectFromImages: function () {
        for (var i = 0; i < this.cache.length; i++) {
            OpenLayers.Event.stopObservingElement(this.cache[i]);
        }
        this.cache.length = 0;
    },

    // private
    onLoadEnd: function () {
        this.disconnectFromImages();
        this.ready = true;
        this.events.triggerEvent("scanfinish", { snapshot: this });
    },

    // private
    addToImage: function (tile, x, y) {
        var tileWidth = this.layer.tileSize.w,
            tileHeight = this.layer.tileSize.h,
            data = this.image,
            bytes = this.bytes,
            w = this.size.w,
            x0 = Math.max(0, x), // intersection of tile and shapshot (viewport)
            y0 = Math.max(0, y),
            x1 = Math.min(w, x + tileWidth) - 1,
            y1 = Math.min(this.size.h, y + tileHeight) - 1;

        if (x0 > x1 || y0 > y1) return; // tile isn't visible

        var canvas = document.createElement("canvas"); // create canvas for tile
        canvas.width = tileWidth;
        canvas.height = tileHeight;

        var ctx = canvas.getContext("2d"); // copy to canvas context tile image
        ctx.drawImage(tile, 0, 0);

        var imgData = ctx.getImageData(0, 0, tileWidth, tileHeight),
            tileData = imgData.data,
            i = (y0 * w + x0) * bytes,
            len = (x1 - x0 + 1) * bytes,
            w1 = tileWidth * bytes,
            k1 = ((y0 - y) * tileWidth + x0 - x) * bytes,
            k2 = ((y1 - y) * tileWidth + x0 - x + 1) * bytes;

        w *= bytes;
        // copy only the visible data of image to snapshot
        for (var k = k1; k < k2; k += w1) { // walk through rows (Y)
            data.set(tileData.subarray(k, k + len), i); // copy row
            i += w;
        }
    },
    
    // override
    isReady: function () {
        return this.ready;
    },
    
    // override
    destroy: function () {
        this.disconnectFromImages();
        this.disconnectFromGoogle();
        this.cache = null;
        OpenLayers.Layer.Snapshot.prototype.destroy.call(this);
    },

    CLASS_NAME: "OpenLayers.Layer.Snapshot.Google"
    
});

/**
 * Class: OpenLayers.Handler.Mouse
 * Simple mouse handler
 *
 * Inherits from:
 *  - <OpenLayers.Handler>
 */
OpenLayers.Handler.Mouse = OpenLayers.Class(OpenLayers.Handler, {

    mousemove: function (e) {
        this.callback("move", [e]);
    },
    
    mousedown: function (e) {
        this.callback("down", [e]);
    },
    
    mouseup: function (e) {
        this.callback("up", [e]);
    }
    
});

/**
 * Class: OpenLayers.Control.MagicWand
 * The MagicWand control
 *
 * Inherits from:
 *  - <OpenLayers.Control>
 */
OpenLayers.Control.MagicWand = OpenLayers.Class(OpenLayers.Control, {

    // override
    type: OpenLayers.Control.TYPE_TOOL,

    /**
     * APIProperty: layer
     * {<OpenLayers.Layer>} Overlay (don't use for base layers) from which the snapshot will be made
     */
    layer: null,

    /**
     * APIProperty: maskLayer
     * {<OpenLayers.Layer.Mask>} Layer for displaying mask
     */
    maskLayer: null,
    
    /**
     * Property: waitClass
     * {String} CSS class for map when snapshot is loading
     */
    waitClass: null,

    /**
     * Property: drawClass 
     * {String} CSS class for map when "add mode" is turned off (default)
     */
    drawClass: null,
        
    /**
     * Property: addClass
     * {String} CSS class for map when "add mode" is turned on
     */
    addClass: null,

    /**
     * Property: colorThreshold
     * {Integer} Tool parameter: Color threshold [1-255] (see method 'floodFill' in MagicWand.js) 
     */
    colorThreshold: 15,

    /**
     * Property: blurRadius
     * {Integer} Tool parameter: Blur radius [1-15] (see method 'gaussBlurOnlyBorder' in MagicWand.js)
     */
    blurRadius: 5,

    /**
     * Property: resetLayerOnDeactivate 
     * {Boolean} Indicates whether or not the layer should be set to null when control is deactivated
     */
    resetLayerOnDeactivate: false,

    // private
    snapshot: null,

    // private
    history: null,

    // private
    isMapConnect: false,
    
    // private
    allowDraw: false,
    
    // private
    addMode: false,

    // private
    currentThreshold: 0,

    // private
    downPoint: null,
    
    // private
    oldImage: null,

    initialize: function (options) {
        OpenLayers.Control.prototype.initialize.call(this, options);

        this.init();
    },

    // private
    init: function () {
        this.currentThreshold = this.colorThreshold;

        if (this.layer) this.setLayer(this.layer);

        this.history = new OpenLayers.Control.MagicWand.MaskHistory();

        this.handlers = {
            keyboard: new OpenLayers.Handler.Keyboard(this, {
                "keydown": this.keydown,
                "keyup": this.keyup,
            }),
            mouse: new OpenLayers.Handler.Mouse(this, {
                "move": this.move,
                "down": this.down,
                "up": this.up,
            })
        };
    },
    
    // override
    destroy: function () {
        this.onDeactivate();

        if (this.history) this.history.destroy();

        OpenLayers.Control.prototype.destroy.call(this);
    },
    
    setColorThreshold: function (threshold) {
        this.currentThreshold = this.colorThreshold = threshold;
    },

    setBlurRadius: function (radius) {
        this.blurRadius = radius;
    },

    getLayer: function() {
        return this.layer || this.map.baseLayer;
    },

    setLayer: function (layer) {
        this.layer = layer.isBaseLayer ? null : layer; // property layer only for overlays
        if (this.active) {
            this.clearImage();
            this.createSnapshot();
        }
    },

    clearImage: function() {
        if (this.maskLayer && this.maskLayer.tile) {
            this.maskLayer.tile.clearImage();
        }
    },

    // private
    connectToMaskLayer: function () {
        this.maskLayer.events.on({
            'createtile': this.onCreateMaskTile,
            scope: this
        });
    },

    // private
    disconnectFromMaskLayer: function () {
        this.maskLayer.events.un({
            'createtile': this.onCreateMaskTile,
            scope: this
        });
    },

    // private
    onCreateMaskTile: function (e) {
        if (this.history) this.history.clear();
    },

    // override
    setMap: function (map) {
        OpenLayers.Control.prototype.setMap.call(this, map);
        if (this.handlers.mouse) {
            this.handlers.mouse.setMap(map);
        }
        if (this.handlers.keyboard) {
            this.handlers.keyboard.setMap(map);
        }
        this.connectToMap();
    },

    // private
    connectToMap: function () {
        if (this.isMapConnect || !this.active) return false;

        this.map.events.on({
            'moveend': this.onMapMoveEnd,
            'updatesize': this.onMapUpdateSize,
            'changebaselayer': this.onChangeBaseLayer,
            scope: this
        });
        this.onMapMoveEnd();

        this.isMapConnect = true;
        return true;
    },

    // private
    disconnectFromMap: function () {
        if (!this.isMapConnect || this.active) return false;

        this.map.events.un({
            'moveend': this.onMapMoveEnd,
            'updatesize': this.onMapUpdateSize,
            'changebaselayer': this.onChangeBaseLayer,
            scope: this
        });

        this.isMapConnect = false;
        return true;
    },

    // private
    onChangeBaseLayer: function () {
        this.createSnapshot();
    },

    // private
    onMapMoveEnd: function () {
        this.createSnapshot();
    },
    
    // private
    onMapUpdateSize: function () {
        var me = this;
        setTimeout(function () {
            me.onMapResize();
        }, 50);
    },

    // private
    onMapResize: function () {
        this.createSnapshot();
        this.maskLayer.onResize();
    },

    // override
    activate: function () {
        OpenLayers.Control.prototype.activate.call(this);
        if (this.handlers.mouse) {
            this.handlers.mouse.activate();
        }
        if (this.handlers.keyboard) {
            this.handlers.keyboard.activate();
        }
        this.map.div.classList.add(this.drawClass);
        this.connectToMap();
        if (this.maskLayer) this.connectToMaskLayer();
    },

    // override
    deactivate: function () {
        OpenLayers.Control.prototype.deactivate.call(this);
        this.onDeactivate();
    },
    
    // private
    onDeactivate: function () {
        if (this.handlers.mouse) {
            this.handlers.mouse.deactivate();
        }
        if (this.handlers.keyboard) {
            this.handlers.keyboard.deactivate();
        }
        if (this.resetLayerOnDeactivate) this.layer = null;
        this.allowDraw = false;
        this.downPoint = null;
        this.oldImage = null;
        this.addMode = false;
        this.disconnectFromMap();
        if (this.maskLayer) this.disconnectFromMaskLayer();
        this.removeSnapshot();
        this.clearImage();
        if (this.history) this.history.clear();
        this.map.div.classList.remove(this.drawClass);
        this.map.div.classList.remove(this.waitClass);
        this.map.div.classList.remove(this.addClass);
    },
    
    // private
    keydown: function (evt) {
        // ctrl press (add mode on)
        if (evt.keyCode == 17 && !this.map.div.classList.contains(this.addClass)) 
            this.map.div.classList.add(this.addClass);
    },
    
    // private
    keyup: function (evt) {
        // ctrl unpress (add mode off)
        if (evt.keyCode == 17) this.map.div.classList.remove(this.addClass);
        if (evt.keyCode == 67) { // show contours (debug mode)
            var cs = this.maskLayer.tile.getContours();

            var ctx = this.maskLayer.tile.canvasContext;
            ctx.clearRect(0, 0, this.maskLayer.tile.size.w, this.maskLayer.tile.size.h);

            var i, j, ps;
            // lines
            ctx.beginPath();
            for (i = 0; i < cs.length; i++) {
                ps = cs[i].points;
                ctx.moveTo(ps[0].x, ps[0].y);
                for (j = 1; j < ps.length; j++) {
                    ctx.lineTo(ps[j].x, ps[j].y);
                }
            }
            ctx.strokeStyle = "red";
            ctx.stroke();

            // vertices
            ctx.fillStyle = "aqua";
            for (i = 0; i < cs.length; i++) {
                ps = cs[i].points;
                for (j = 0; j < ps.length; j++) {
                    ctx.fillRect(ps[j].x, ps[j].y, 1, 1);
                }
            }
        }
        if (evt.ctrlKey) { // history manipulations
            var img = null;
            if (evt.keyCode == 89) img = this.history.redo(); // ctrl + y
            if (evt.keyCode == 90) img = this.history.undo(); // ctrl + z
            if (img && this.maskLayer) this.maskLayer.tile.setImage(img); // apply mask from history
        }
    },
    
    // private
    move: function (e) {
        // log current pixel color (debug mode)
        //var pixel = this.map.events.getMousePosition(e);
        //if (this.snapshot && this.snapshot.isReady()) {
        //    var r = this.snapshot.getPixelColor(Math.round(pixel.x), Math.round(pixel.y));
        //    console.log(r[0] + " " + r[1] + " " + r[2] + " " + r[3]);
        //}
        //return;
        if (this.allowDraw) {
            var pixel = this.map.events.getMousePosition(e);
            var x = Math.round(pixel.x);
            var y = Math.round(pixel.y);
            var px = this.downPoint.x;
            var py = this.downPoint.y;
            if (x != px || y != py)
            {
                // color threshold calculation
                var dx = x - px;
                var dy = y - py;
                var len = Math.sqrt(dx * dx + dy * dy);
                var adx = Math.abs(dx);
                var ady = Math.abs(dy);
                var sign = adx > ady ? dx / adx : dy / ady;
                sign = sign < 0 ? sign / 5 : sign / 3;
                var thres = Math.min(Math.max(this.colorThreshold + Math.round(sign * len), 1), 255); // 1st method
                //var thres = Math.min(Math.max(this.colorThreshold + dx / 2, 1), 255); // 2nd method
                //var thres = Math.min(this.colorThreshold + Math.round(len / 3), 255); // 3rd method
                if (thres != this.currentThreshold) {
                    this.currentThreshold = thres;
                    this.drawMask(px, py);
                }
            }
        }
    },

    // private
    down: function (e) {
        if (e.button == 2) { // right button - draw mask
            if (!this.maskLayer || !this.snapshot || !this.snapshot.isReady()) return;
            this.downPoint = this.map.events.getMousePosition(e);
            this.downPoint.x = Math.round(this.downPoint.x); // mouse down point (base point)
            this.downPoint.y = Math.round(this.downPoint.y);
            this.allowDraw = true;
            this.addMode = e.ctrlKey; // || e.shiftKey;
            this.drawMask(this.downPoint.x, this.downPoint.y);
        } else if (e.button == 1) { // show current snapshot (debug mode)
            var imgData = this.maskLayer.tile.canvasContext.createImageData(this.maskLayer.tile.size.w, this.maskLayer.tile.size.h);
            for (var i = 0; i < this.snapshot.image.length; i++) {
                imgData.data[i] = this.snapshot.image[i];
            }
            this.maskLayer.tile.canvasContext.clearRect(0, 0, this.maskLayer.tile.size.w, this.maskLayer.tile.size.h);
            this.maskLayer.tile.canvasContext.putImageData(imgData, 0, 0);
        } else { // reset all
            this.allowDraw = false;
            this.oldImage = null;
            this.addMode = false;
        }
    },
    
    // private
    up: function (e) {
        // add current mask to history
        if (this.allowDraw && this.maskLayer && this.history) {
            this.history.addMask(this.maskLayer.tile.image);
        }

        // reset all
        this.currentThreshold = this.colorThreshold;
        this.allowDraw = false;
        this.oldImage = null;
        this.addMode = false;
    },

    createSnapshot: function () {
        var layer = this.getLayer();

        if (this.snapshot && this.snapshot.layer == layer) {
            this.snapshot.scan();
            return;
        }

        this.removeSnapshot();

        var options = {            
            eventListeners: {
                'scanstart': function (evt) {
                    this.map.div.classList.add(this.waitClass);
                },
                'scanfinish': function (evt) {
                    this.map.div.classList.remove(this.waitClass);
                },
                scope: this
            }
        };
        if (layer instanceof OpenLayers.Layer.Grid) {
            this.snapshot = new OpenLayers.Layer.Snapshot.Grid(layer, options);
        } else if (layer instanceof OpenLayers.Layer.Google) {
            this.snapshot = new OpenLayers.Layer.Snapshot.Google(layer, options);
        }
    },
    
    removeSnapshot: function() {
        if (this.snapshot) {
            this.snapshot.destroy();
            this.snapshot = null;
        }
    },

    // return concatenation of image and old masks
    concatMask: function (image, old) {
        var data1 = old.data,
            data2 = image.data,
            w1 = old.width,
            w2 = image.width,
            px1 = old.globalOffset.x,
            py1 = old.globalOffset.y,
            px2 = image.globalOffset.x,
            py2 = image.globalOffset.y,
            b1 = old.bounds,
            b2 = image.bounds,
            px = Math.min(b1.minX + px1, b2.minX + px2), // global offset for new image (by min in bounds)
            py = Math.min(b1.minY + py1, b2.minY + py2),
            b = { // bounds for new image include all of the pixels [0,0,width,height] (reduce to bounds)
                minX: 0,
                minY: 0,
                maxX: Math.max(b1.maxX + px1, b2.maxX + px2) - px,
                maxY: Math.max(b1.maxY + py1, b2.maxY + py2) - py,
            },
            w = b.maxX + 1, // size for new image
            h = b.maxY + 1,
            i, j, k, k1, k2, len;

        var result = new Uint8Array(w * h);

        // copy all old image
        len = b1.maxX - b1.minX + 1;
        i = (py1 - py + b1.minY) * w + (px1 - px + b1.minX);
        k1 = b1.minY * w1 + b1.minX;
        k2 = b1.maxY * w1 + b1.minX + 1;
        // walk through rows (Y)
        for (k = k1; k < k2; k += w1) {
            result.set(data1.subarray(k, k + len), i); // copy row
            i += w;
        }

        // copy new image (only "black" pixels)
        len = b2.maxX - b2.minX + 1;
        i = (py2 - py + b2.minY) * w + (px2 - px + b2.minX);
        k1 = b2.minY * w2 + b2.minX;
        k2 = b2.maxY * w2 + b2.minX + 1;
        // walk through rows (Y)
        for (k = k1; k < k2; k += w2) {
            // walk through cols (X)
            for (j = 0; j < len; j++) {
                if (data2[k + j] === 1) result[i + j] = 1;
            }
            i += w;
        }

        return {
            data: result,
            width: w,
            height: h,
            bounds: b,
            globalOffset: {
                x: px,
                y: py,
            }
        };
    },

    drawMask: function (x, y) {
        if (!(this.snapshot && this.snapshot.image)) return false;

        var size = this.snapshot.size;
        var mapSize = this.map.getCurrentSize();
        if (size.w != mapSize.w || size.h != mapSize.h) { // if map size is not equal to snapshot size then recreate snapshot
            this.onMapResize();
            if (!this.snapshot.isReady()) return false;
            size = this.snapshot.size;
        }

        var tile = this.maskLayer.tile;
        var offset = { x: Math.round(-this.map.minPx.x), y: Math.round(-this.map.minPx.y) }; // offset from the map

        var image = {
            data: this.snapshot.image,
            width: size.w,
            height: size.h,
            bytes: this.snapshot.bytes
        };

        if (this.addMode && tile.image) {
            if (!this.oldImage) {
                var img = tile.image;
                var bounds = img.bounds;
                // clone image
                this.oldImage = {
                    data: new Uint8Array(img.data),
                    width: img.width,
                    height: img.height,
                    bounds: {
                        minX: bounds.minX,
                        maxX: bounds.maxX,
                        minY: bounds.minY,
                        maxY: bounds.maxY,
                    },
                    globalOffset: {
                        x: img.globalOffset.x,
                        y: img.globalOffset.y
                    }
                };
                var oldOffset = this.oldImage.globalOffset,
                    minPx = Math.round(this.map.minPx.x),
                    maxPx = Math.round(this.map.maxPx.x),
                    offsets = [{ x: oldOffset.x, y: oldOffset.y }]; // add old image offset (current world)

                var pxLen = maxPx - minPx;
                offsets.push({ x: oldOffset.x - pxLen, y: oldOffset.y }); // add additional old image offsets (neighboring worlds)
                offsets.push({ x: oldOffset.x + pxLen, y: oldOffset.y }); 

                //// set correct offset for new image in old image world
                //if (oldOffset.x <= 0 && offset.x > 0) {
                //    offset.x -= pxLen;
                //} else if (oldOffset.x > 0 && offset.x <= 0) {
                //    offset.x += pxLen;
                //}
                var i, j, k, k1, k2, len, of,
                    x0, y0, x1, y1, dx, dy,
                    rx0, rx1, ry0, ry1,
                    w = image.width,
                    h = image.height,
                    data = new Uint8Array(w * h),
                    old = this.oldImage.data,
                    w1 = this.oldImage.width,
                    b = this.oldImage.bounds,
                    ix = image.width - 1, // right bottom of image (left top = [0,0])
                    iy = image.height - 1,
                    offsetsLen = offsets.length;
                    
                // copy visible data from old mask for floodfill (considering wrapDateLine and neighboring worlds) 
                for (j = 0; j < offsetsLen; j++) {
                    of = offsets[j]; // old mask offset in the global basis
                    dx = of.x - offset.x; // delta for the transformation to image basis
                    dy = of.y - offset.y;
                    x0 = dx + b.minX; // left top of old mask (in image basis)
                    y0 = dy + b.minY;
                    x1 = dx + b.maxX; // right bottom of old mask (in image basis)
                    y1 = dy + b.maxY;

                    // intersection of the old mask with the image (viewport)
                    if (!(x1 < 0 || x0 > ix || y1 < 0 || y0 > iy)) {
                        rx0 = x0 > 0 ? x0 : 0;  // result of the intersection
                        ry0 = y0 > 0 ? y0 : 0;
                        rx1 = x1 < ix ? x1 : ix;
                        ry1 = y1 < iy ? y1 : iy;
                    } else {
                        continue;
                    }
                    // copy result of the intersection to mask data for floodfill
                    len = rx1 - rx0 + 1;
                    i = ry0 * w + rx0;
                    k1 = (ry0 - dy) * w1 + (rx0 - dx);
                    k2 = (ry1 - dy) * w1 + (rx0 - dx) + 1;
                    // walk through rows (Y)
                    for (k = k1; k < k2; k += w1) {
                        data.set(old.subarray(k, k + len), i); // copy row
                        i += w;
                    }
                }
                this.oldImage.visibleData = data;
            }

            // create the new mask considering the current visible data
            image = MagicWand.floodFill(image, x, y, this.currentThreshold, this.oldImage.visibleData);
            if (!image) return false;
            // blur the new mask considering the current visible data
            if (this.blurRadius > 0) image = MagicWand.gaussBlurOnlyBorder(image, this.blurRadius, this.oldImage.visibleData);
            image.globalOffset = offset;
            image = this.concatMask(image, this.oldImage); // old mask + new mask
        } else {
            image = MagicWand.floodFill(image, x, y, this.currentThreshold);
            if (this.blurRadius > 0) image = MagicWand.gaussBlurOnlyBorder(image, this.blurRadius);
            image.globalOffset = offset;
        }
        tile.setImage(image);

        return true;
    },

    CLASS_NAME: "OpenLayers.Control.MagicWand"
});

/**
 * Class: OpenLayers.Control.MagicWand.MaskHistory
 * History of masks
 */
OpenLayers.Control.MagicWand.MaskHistory = OpenLayers.Class({
    
    /**
     * Property: history
     * {Array} Array of masks
     */
    history: null,

    /**
     * Property: current
     * {Integer} Current index of history array 
     */
    current: -1,  

    initialize: function (options) {
        OpenLayers.Util.extend(this, options);

        this.history = [];
    },

    destroy: function() {
        this.history = null;
    },

    clear: function() {
        this.history.length = 0;
        this.current = -1;
    },

    addMask: function (mask) {
        if (!mask) return false;

        this.current++;
        this.history.length = this.current;
        this.history.push(mask);

        return true;
    },

    getCurrent: function () {
        return this.current > -1 ? this.history[this.current] : null;
    },

    allowUndo: function() {
        return this.current > 0;
    },

    allowRedo: function () {
        return this.current < this.history.length - 1;
    },

    undo: function () {
        if (!this.allowUndo()) return null;
        this.current--;
        return this.getCurrent();
    },

    redo: function () {
        if (!this.allowRedo()) return null;
        this.current++;
        return this.getCurrent();
    }

});
