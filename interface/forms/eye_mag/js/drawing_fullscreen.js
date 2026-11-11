/**
 * Expand the current Eye Form drawing without moving or recreating its canvases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (window, document) {
    'use strict';

    var active = null;
    var locks = [];
    var hosts = [];
    var raised = [];
    var resizePending = false;
    var observer = null;
    var modeObserver = null;

    /** Prevent page panning while leaving image-library dialogs scrollable. */
    function blockPagePan(event) {
        if (!active || !event.cancelable) return;
        var target = event.target;
        if (target && target.closest && target.closest('.eye-image-modal[open]')) return;
        var frame = active.panel.querySelector('.eye-drawing-stage-frame');
        if (frame && frame.contains(target) && event.touches && event.touches.length === 2) {
            var y = (event.touches[0].clientY + event.touches[1].clientY) / 2;
            if (active.panY !== null) frame.scrollTop += active.panY - y;
            active.panY = y;
            event.preventDefault();
            return;
        }
        active.panY = null;
        event.preventDefault();
    }

    /** Start each two-finger pan from the current gesture, not the previous one. */
    function resetPagePan(event) {
        if (active && (!event.touches || event.touches.length < 2)) active.panY = null;
    }

    /** Add a temporary scroll lock and frame-expansion rule to one same-origin document. */
    function lockDocument(doc) {
        if (locks.some(function (item) { return item.doc === doc; })) return;
        var view = doc.defaultView;
        var style = doc.createElement('style');
        style.textContent = 'html.eye-drawing-scroll-lock,body.eye-drawing-scroll-lock{overflow:hidden!important;}'
            + 'iframe.eye-drawing-fullscreen-host{position:fixed!important;display:block!important;top:0!important;left:0!important;'
            + 'width:100vw!important;height:100vh!important;max-width:none!important;max-height:none!important;'
            + 'margin:0!important;border:0!important;z-index:2147483000!important;}'
            + '.eye-drawing-fullscreen-ancestor{z-index:2147483000!important;}'
            + '.eye-drawing-fullscreen-static{position:relative!important;}';
        doc.head.appendChild(style);
        var htmlHadLock = doc.documentElement.classList.contains('eye-drawing-scroll-lock');
        var bodyHadLock = doc.body.classList.contains('eye-drawing-scroll-lock');
        locks.push({doc: doc, style: style, htmlHadLock: htmlHadLock, bodyHadLock: bodyHadLock,
            x: view.scrollX || 0, y: view.scrollY || 0});
        doc.documentElement.classList.add('eye-drawing-scroll-lock');
        doc.body.classList.add('eye-drawing-scroll-lock');
        doc.addEventListener('touchmove', blockPagePan, {passive: false});
        doc.addEventListener('touchend', resetPagePan);
        doc.addEventListener('touchcancel', resetPagePan);
        view.addEventListener('resize', scheduleResize);
    }

    /** Keep the canvas, annotation layer and structured overlay at the same scale. */
    function resizeDrawing() {
        resizePending = false;
        if (!active) return;
        // Some iOS versions expand iframe viewports to fit the entire document
        // despite the host's fixed height. Use the outer viewport so the canvas
        // and its controls cannot be centered below the actual screen.
        var viewport = window;
        try { if (window.top && window.top.document) viewport = window.top; } catch (error) { /* cross-origin host */ }
        var viewportWidth = viewport.innerWidth || viewport.document.documentElement.clientWidth;
        var viewportHeight = viewport.innerHeight || viewport.document.documentElement.clientHeight;
        if (viewportWidth > 0 && viewportHeight > 0) {
            active.panel.style.setProperty('width', viewportWidth + 'px', 'important');
            active.panel.style.setProperty('height', viewportHeight + 'px', 'important');
        }
        var frame = active.panel.querySelector('.eye-drawing-stage-frame');
        var canvas = document.getElementById('myCanvas_' + active.zone);
        if (!frame || !canvas || !canvas.width || !canvas.height) return;
        var tools = active.panel.querySelector('.eye-canvas-body > .tools');
        var actions = active.panel.querySelector('.eye-drawing-actions');
        var stage = frame.querySelector('.eye-drawing-stage');
        var content = frame.querySelector('.eye-konva-stage > .konvajs-content');
        if (!tools || !actions || !stage) return;
        var panelRect = active.panel.getBoundingClientRect();
        var top = Math.max(0, tools.getBoundingClientRect().bottom - panelRect.top + 4);
        var bottom = Math.max(8, actions.getBoundingClientRect().height + 16);
        frame.style.top = top + 'px';
        frame.style.bottom = bottom + 'px';
        var frameStyle = window.getComputedStyle(frame);
        var horizontalPadding = (parseFloat(frameStyle.paddingLeft) || 0) + (parseFloat(frameStyle.paddingRight) || 0);
        var verticalPadding = (parseFloat(frameStyle.paddingTop) || 0) + (parseFloat(frameStyle.paddingBottom) || 0);
        var width = Math.max(1, frame.clientWidth - horizontalPadding);
        var height = Math.max(1, frame.clientHeight - verticalPadding);
        var scale = width / canvas.width;
        if (!isFinite(scale) || scale <= 0) return;
        var scrollHint = active.panel.querySelector('.eye-fullscreen-scroll-hint');
        if (scrollHint) scrollHint.hidden = canvas.height * scale <= height + 1;
        // Keep a short drawing beside its tools without a white, non-drawable
        // gutter. Taller drawings remain full width and scroll within the frame.
        var unusedHeight = Math.max(0, height - canvas.height * scale);
        if (unusedHeight > 0) {
            frame.style.bottom = (bottom + unusedHeight) + 'px';
        }
        // Inline dimensions avoid relying on nested flex sizing or CSS-variable
        // transforms in older WebKit. Never resize the canvas backing store.
        stage.style.width = (canvas.width * scale) + 'px';
        stage.style.height = (canvas.height * scale) + 'px';
        if (content) content.style.transform = 'scale(' + scale + ')';
        active.panel.style.setProperty('--eye-fullscreen-width', (canvas.width * scale) + 'px');
        active.panel.style.setProperty('--eye-fullscreen-height', (canvas.height * scale) + 'px');
        active.panel.style.setProperty('--eye-fullscreen-scale', String(scale));
    }

    function scheduleResize() {
        if (!active || resizePending) return;
        resizePending = true;
        window.requestAnimationFrame(resizeDrawing);
    }

    /** Restore the existing panel and every affected frame without resetting drawing state. */
    function closeFullscreen() {
        if (!active) return;
        var panel = active.panel;
        var opener = active.opener;
        var frame = panel.querySelector('.eye-drawing-stage-frame');
        var stage = frame && frame.querySelector('.eye-drawing-stage');
        var content = frame && frame.querySelector('.eye-konva-stage > .konvajs-content');
        if (frame) { frame.style.top = active.frameTop; frame.style.bottom = active.frameBottom; frame.scrollTop = active.frameScrollTop; }
        if (stage) { stage.style.width = active.stageWidth; stage.style.height = active.stageHeight; }
        if (content) content.style.transform = active.contentTransform;
        if (active.panelWidth) panel.style.setProperty('width', active.panelWidth, active.panelWidthPriority);
        else panel.style.removeProperty('width');
        if (active.panelHeight) panel.style.setProperty('height', active.panelHeight, active.panelHeightPriority);
        else panel.style.removeProperty('height');
        active = null;
        if (observer) { observer.disconnect(); observer = null; }
        if (modeObserver) { modeObserver.disconnect(); modeObserver = null; }
        panel.classList.remove('eye-drawing-fullscreen');
        panel.style.removeProperty('--eye-fullscreen-width');
        panel.style.removeProperty('--eye-fullscreen-height');
        panel.style.removeProperty('--eye-fullscreen-scale');
        document.body.classList.remove('eye-fullscreen-active');
        hosts.forEach(function (item) {
            if (!item.hadClass) item.frame.classList.remove('eye-drawing-fullscreen-host');
        });
        hosts = [];
        raised.forEach(function (item) {
            if (!item.hadRaised) item.node.classList.remove('eye-drawing-fullscreen-ancestor');
            if (!item.hadStatic) item.node.classList.remove('eye-drawing-fullscreen-static');
        });
        raised = [];
        locks.reverse().forEach(function (item) {
            var doc = item.doc;
            var view = doc.defaultView;
            doc.removeEventListener('touchmove', blockPagePan, {passive: false});
            doc.removeEventListener('touchend', resetPagePan);
            doc.removeEventListener('touchcancel', resetPagePan);
            view.removeEventListener('resize', scheduleResize);
            if (!item.htmlHadLock) doc.documentElement.classList.remove('eye-drawing-scroll-lock');
            if (!item.bodyHadLock) doc.body.classList.remove('eye-drawing-scroll-lock');
            item.style.remove();
            if (view.scrollX !== item.x || view.scrollY !== item.y) view.scrollTo(item.x, item.y);
        });
        locks = [];
        if (opener && document.contains(opener)) opener.focus();
    }

    /** Expand this panel and its same-origin ancestor frames into the site viewport. */
    function openFullscreen(zone, opener) {
        var panel = document.getElementById('Draw_' + zone);
        if (!panel || !panel.querySelector('.eye-drawing-stage')) return;
        if (active) closeFullscreen();
        var frame = panel.querySelector('.eye-drawing-stage-frame');
        var stage = frame && frame.querySelector('.eye-drawing-stage');
        var content = frame && frame.querySelector('.eye-konva-stage > .konvajs-content');
        if (!frame || !stage) return;
        active = {panel: panel, zone: zone, opener: opener,
            panY: null,
            frameTop: frame.style.top, frameBottom: frame.style.bottom, frameScrollTop: frame.scrollTop,
            stageWidth: stage.style.width, stageHeight: stage.style.height,
            contentTransform: content ? content.style.transform : '',
            panelWidth: panel.style.getPropertyValue('width'), panelWidthPriority: panel.style.getPropertyPriority('width'),
            panelHeight: panel.style.getPropertyValue('height'), panelHeightPriority: panel.style.getPropertyPriority('height')};
        lockDocument(document);
        var current = window;
        try {
            while (current !== current.top) {
                var host = current.frameElement;
                if (!host) break;
                lockDocument(host.ownerDocument);
                hosts.push({frame: host, hadClass: host.classList.contains('eye-drawing-fullscreen-host')});
                host.classList.add('eye-drawing-fullscreen-host');
                // A positioned iframe cannot escape a lower ancestor stacking
                // context (the OpenEMR main navigation otherwise covers Close).
                for (var ancestor = host.parentElement; ancestor && ancestor !== host.ownerDocument.body; ancestor = ancestor.parentElement) {
                    var computed = host.ownerDocument.defaultView.getComputedStyle(ancestor);
                    if (computed.zIndex === 'auto' && computed.transform === 'none' && computed.opacity === '1' && computed.isolation !== 'isolate') continue;
                    raised.push({node: ancestor, hadRaised: ancestor.classList.contains('eye-drawing-fullscreen-ancestor'),
                        hadStatic: ancestor.classList.contains('eye-drawing-fullscreen-static')});
                    ancestor.classList.add('eye-drawing-fullscreen-ancestor');
                    if (computed.position === 'static') ancestor.classList.add('eye-drawing-fullscreen-static');
                }
                current = current.parent;
            }
        } catch (error) {
            // Cross-origin hosts cannot be expanded; use the available form viewport.
        }
        document.body.classList.add('eye-fullscreen-active');
        panel.classList.add('eye-drawing-fullscreen');
        var area = panel.querySelector('.eye-drawing-stage-frame');
        if (typeof window.ResizeObserver === 'function' && area) {
            observer = new window.ResizeObserver(scheduleResize);
            observer.observe(area);
        }
        if (typeof window.MutationObserver === 'function') {
            modeObserver = new window.MutationObserver(scheduleResize);
            modeObserver.observe(panel, {attributes: true, attributeFilter: ['class']});
        }
        resizeDrawing();
        scheduleResize();
        window.requestAnimationFrame(scheduleResize);
        window.setTimeout(scheduleResize, 120);
        var close = panel.querySelector('[data-drawing-fullscreen-close]');
        if (close) close.focus();
    }

    document.addEventListener('click', function (event) {
        var open = event.target.closest && event.target.closest('[data-drawing-fullscreen]');
        if (open) {
            event.preventDefault();
            openFullscreen(open.getAttribute('data-drawing-fullscreen'), open);
            return;
        }
        if (event.target.closest && event.target.closest('[data-drawing-fullscreen-close]')) {
            event.preventDefault();
            closeFullscreen();
            return;
        }
        var panelClose = event.target.closest && event.target.closest('[data-drawing-panel-close]');
        if (panelClose) {
            event.preventDefault();
            var zone = panelClose.getAttribute('data-drawing-panel-close');
            var existingClose = document.getElementById('BUTTON_TEXTD_' + zone)
                || (zone === 'PMH' ? document.getElementById('BUTTON_TEXTD_HPI') : null);
            if (existingClose) existingClose.click();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (active && event.key === 'Escape' && !event.defaultPrevented && !document.querySelector('.eye-image-modal[open]')) {
            event.preventDefault();
            closeFullscreen();
        }
    });
}(window, document));
