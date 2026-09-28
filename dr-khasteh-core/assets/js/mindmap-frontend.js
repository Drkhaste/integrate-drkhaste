(function () {
    'use strict';

    var RETRY_MAX  = 30;
    var retryCount = 0;

    var NODE_COLORS = {
        'default': null,
        'teal'   : { bg: 'linear-gradient(135deg,#ccfbf1,#99f6e4)', border: '#2dd4bf', text: '#134e4a' },
        'blue'   : { bg: 'linear-gradient(135deg,#dbeafe,#bfdbfe)', border: '#60a5fa', text: '#1e3a8a' },
        'purple' : { bg: 'linear-gradient(135deg,#ede9fe,#ddd6fe)', border: '#a78bfa', text: '#4c1d95' },
        'pink'   : { bg: 'linear-gradient(135deg,#fce7f3,#fbcfe8)', border: '#f472b6', text: '#831843' },
        'red'    : { bg: 'linear-gradient(135deg,#fee2e2,#fecaca)', border: '#f87171', text: '#7f1d1d' },
        'orange' : { bg: 'linear-gradient(135deg,#ffedd5,#fed7aa)', border: '#fb923c', text: '#7c2d12' },
        'yellow' : { bg: 'linear-gradient(135deg,#fef9c3,#fef08a)', border: '#facc15', text: '#713f12' },
        'green'  : { bg: 'linear-gradient(135deg,#dcfce7,#bbf7d0)', border: '#4ade80', text: '#14532d' },
        'dark'   : { bg: 'linear-gradient(135deg,#334155,#1e293b)', border: '#475569', text: '#f1f5f9' },
    };

    function boot() {
        if (typeof jsMind === 'undefined') {
            if (retryCount < RETRY_MAX) { retryCount++; setTimeout(boot, 300); }
            else { console.error('[MindMapStudio] jsMind not found.'); }
            return;
        }

        applyLineStyleOverrides();

        var els = document.querySelectorAll('.mindmap-studio-container');
        for (var i = 0; i < els.length; i++) {
            initMap(els[i]);
        }
    }

    function initMap(el) {
        if (el.classList.contains('mms-modal-content')) return;

        el.addEventListener('click', function(e) {
            if (e.target.closest('jmnode')) return;
            openModal(el);
        });

        var rawData    = el.getAttribute('data-mindmap-data');
        var layout     = el.getAttribute('data-mindmap-layout') || 'both';
        var lineColor  = el.getAttribute('data-line-color') || '#94a3b8';
        var lineStyle  = el.getAttribute('data-line-style') || 'bezier';
        var lineWidth  = parseFloat(el.getAttribute('data-line-width') || '1.5');
        var nodeStylesRaw = el.getAttribute('data-node-styles') || '{}';
        var nodeStyles = {};
        try { nodeStyles = JSON.parse(nodeStylesRaw); } catch(e) {}

        if (!rawData || !rawData.trim()) return;

        var nodes = parseNodes(rawData, layout);
        if (!nodes.length) return;

        var isDark = document.body.classList.contains('dark-mode');
        var theme  = isDark
            ? (getS('theme_dark')  || 'primary')
            : (getS('theme_light') || 'primary');

        if (!el.id) el.id = 'mms_' + Math.random().toString(36).slice(2, 9);

        el.style.width      = '100%';
        el.style.height     = '500px';
        el.style.background = 'transparent';
        el.style.display    = 'block';

        var jmInst;
        try {
            jmInst = new jsMind({
                container : el.id,
                theme     : theme,
                mode      : 'full',
                editable  : false,
                view   : {
                    engine    : 'svg',
                    line_width : lineWidth,
                    line_color : lineColor,
                    line_style : lineStyle
                },
                layout : { hspace: 46, vspace: 16, pspace: 12 }
            });
            jmInst.show({
                meta   : { name: 'Map', author: 'MMS', version: '1.0' },
                format : 'node_array',
                data   : nodes
            });
            enablePinchToZoom(el, jmInst);
        } catch (e) {
            console.error('[MindMapStudio] render error:', e);
            return;
        }

        function update() {
            scaleAndCenter(el);
            applyWatermark(el);
            // Apply node colours
            setTimeout(function() {
                applyNodeStyles(el, nodeStyles);
                applyLineStyles(el, nodeStyles, jmInst);
            }, 80);
        }

        setTimeout(update, 400);

        var details = el.closest('details');
        if (details) {
            details.addEventListener('toggle', function() {
                if (details.open) { setTimeout(update, 100); }
            });
        }

        if (window.ResizeObserver) {
            new ResizeObserver(function () { setTimeout(update, 100); }).observe(el.parentElement || el);
        } else {
            window.addEventListener('resize', function () { setTimeout(update, 100); });
        }
    }

    /* ── Apply node colours from saved styles ── */
    function applyNodeStyles(el, nodeStyles) {
        Object.keys(nodeStyles).forEach(function(nodeId) {
            var style = nodeStyles[nodeId];
            if (!style || !style.color || style.color === 'default') return;
            var colorData = NODE_COLORS[style.color];
            if (!colorData) return;
            var nodeEl = el.querySelector('jmnode[nodeid="' + nodeId + '"]');
            if (nodeEl) {
                nodeEl.style.background  = colorData.bg;
                nodeEl.style.color       = colorData.text;
                nodeEl.style.borderColor = colorData.border;
            }
        });
    }

    /* ── Apply dashed/dotted lines ── */
    function applyLineStyles(el, nodeStyles, jmInst) {
        var svg = el.querySelector('svg.jsmind');
        if (!svg) return;

        var allNodes = [];
        if (jmInst && jmInst.mind && jmInst.mind.nodes) {
            allNodes = Object.values(jmInst.mind.nodes).filter(function(n){ return !n.isroot; });
        }
        var paths = svg.querySelectorAll('path');

        allNodes.forEach(function(node, idx) {
            var style = nodeStyles[node.id];
            if (!style || !style.dashed) return;
            var path = paths[idx];
            if (!path) return;
            if (style.dashed === 'dashed') {
                path.style.strokeDasharray = '7 4';
            } else if (style.dashed === 'dotted') {
                path.style.strokeDasharray = '2 5';
            }
        });
    }

    /* ── scaleAndCenter ── */
    function scaleAndCenter(el) {
        var wrap = el.querySelector('jmnodes');
        if (!wrap) return;
        var nodes = el.querySelectorAll('jmnode');
        if (!nodes.length) return;

        wrap.style.transform = 'none';
        var svg = el.querySelector('svg');
        if (svg) svg.style.transform = 'none';

        var minX = 99999, maxX = -99999, minY = 99999, maxY = -99999;
        for (var j = 0; j < nodes.length; j++) {
            var node = nodes[j];
            var x = node.offsetLeft, y = node.offsetTop;
            var w = node.offsetWidth, h = node.offsetHeight;
            if (x < minX) minX = x;
            if (x + w > maxX) maxX = x + w;
            if (y < minY) minY = y;
            if (y + h > maxY) maxY = y + h;
        }

        var mW = maxX - minX, mH = maxY - minY;
        var cRect = el.getBoundingClientRect();
        var cW = cRect.width, cH = cRect.height || 500;

        var baseScale = 1;
        if (mW > (cW - 40) && cW > 0) baseScale = (cW - 40) / mW;
        if (baseScale > 1) baseScale = 1;

        var userZoom = parseFloat(el.getAttribute('data-user-zoom') || '1');
        var scale = baseScale * userZoom;

        var spacer = el.querySelector('.mms-spacer');
        if (!spacer) {
            spacer = document.createElement('div');
            spacer.className = 'mms-spacer';
            el.appendChild(spacer);
        }

        var margin = 100 * scale;
        var fullWidth  = mW * scale + margin * 2;
        var fullHeight = mH * scale + margin * 2;

        spacer.style.cssText = 'position:absolute;top:0;left:0;pointer-events:none;' +
            'width:' + Math.max(fullWidth, cW) + 'px;height:' + Math.max(fullHeight, cH) + 'px;';

        var offsetX = -minX * scale + margin;
        var offsetY = -minY * scale + margin;
        var tfm = 'translate(' + Math.round(offsetX) + 'px, ' + Math.round(offsetY) + 'px) scale(' + scale + ')';

        wrap.style.transformOrigin = '0 0';
        wrap.style.transform       = tfm;

        if (svg) {
            svg.style.transformOrigin = '0 0';
            svg.style.transform       = tfm;
            svg.style.overflow        = 'visible';
        }

        el.style.overflow  = 'auto';
        el.style.direction = 'ltr';
    }

    function applyWatermark(el) {
        var cap = findCapture(el);
        if (!cap) return;
        var s = getS('watermark');
        if (!s || !s.text) return;
        var isMobile = window.innerWidth < 768;
        var spacing  = isMobile ? (s.spacing_mobile || 110) : (s.spacing_desktop || 220);
        var height   = spacing * 0.72;
        var wmSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + spacing + '" height="' + height + '">'
            + '<text x="' + (spacing/2) + '" y="' + (height/2) + '" dominant-baseline="middle" text-anchor="middle"'
            + ' fill="' + (s.color || '#94a3b8') + '" opacity="' + (s.opacity || 0.18) + '"'
            + ' font-size="' + (s.size || 14) + '" font-weight="600" font-family="Vazirmatn,Tahoma,sans-serif"'
            + ' transform="rotate(-30,' + (spacing/2) + ',' + (height/2) + ')">'
            + s.text + '</text></svg>';
        cap.style.backgroundImage  = 'url("data:image/svg+xml;charset=utf-8,' + encodeURIComponent(wmSvg) + '")';
        cap.style.backgroundRepeat = 'repeat';
        cap.style.backgroundSize   = spacing + 'px ' + height + 'px';
    }

    function parseNodes(text, dir) {
        var lines = text.split(/\r?\n/);
        var nodes = [], stack = [], rootDone = false, right = true;
        for (var i = 0; i < lines.length; i++) {
            var raw = lines[i], t = raw.trim();
            if (!t) continue;
            var ind = 0;
            while (ind < raw.length && (raw[ind] === ' ' || raw[ind] === '\t')) ind++;
            var node = { id: 'n' + i, topic: t, indent: ind };
            if (!rootDone) {
                node.isroot = true; rootDone = true; stack = [node]; nodes.push(node); continue;
            }
            while (stack.length && stack[stack.length - 1].indent >= ind) stack.pop();
            var par = stack.length ? stack[stack.length - 1] : nodes[0];
            node.parentid = par.id;
            if (par.isroot) {
                node.direction = (dir === 'both') ? (right ? 'right' : 'left') : 'right';
                if (dir === 'both') right = !right;
            }
            stack.push(node); nodes.push(node);
        }
        return nodes;
    }

    function findCapture(el) {
        var p = el.parentElement;
        while (p) {
            if (p.classList && p.classList.contains('mindmap-studio-capture')) return p;
            p = p.parentElement;
        }
        return null;
    }

    function openModal(originalEl) {
        var modal = document.createElement('div');
        modal.className = 'mms-modal';
        document.body.classList.add('mms-modal-open');

        var closeBtn = document.createElement('div');
        closeBtn.className = 'mms-modal-close';
        closeBtn.innerHTML = '×';
        closeBtn.onclick = function() {
            document.body.removeChild(modal);
            document.body.classList.remove('mms-modal-open');
        };
        modal.appendChild(closeBtn);

        var content = document.createElement('div');
        content.className = 'mms-modal-content mindmap-studio-container';
        var attrs = originalEl.attributes;
        for (var i = 0; i < attrs.length; i++) {
            content.setAttribute(attrs[i].name, attrs[i].value);
        }
        modal.appendChild(content);
        document.body.appendChild(modal);

        var rawData        = content.getAttribute('data-mindmap-data');
        var layout         = content.getAttribute('data-mindmap-layout') || 'both';
        var lineColor      = content.getAttribute('data-line-color') || '#94a3b8';
        var lineStyle      = content.getAttribute('data-line-style') || 'bezier';
        var lineWidth      = parseFloat(content.getAttribute('data-line-width') || '1.5');
        var nodeStylesRaw  = content.getAttribute('data-node-styles') || '{}';
        var nodeStyles     = {};
        try { nodeStyles = JSON.parse(nodeStylesRaw); } catch(e) {}

        var nodes  = parseNodes(rawData, layout);
        var isDark = document.body.classList.contains('dark-mode');
        var theme  = isDark ? (getS('theme_dark') || 'primary') : (getS('theme_light') || 'primary');

        content.id = 'mms_modal_' + Math.random().toString(36).slice(2, 9);

        var jmModal = new jsMind({
            container : content.id,
            theme     : theme,
            mode      : 'full',
            editable  : false,
            view   : { engine: 'svg', line_width: lineWidth, line_color: lineColor, line_style: lineStyle },
            layout : { hspace: 46, vspace: 16, pspace: 12 }
        });
        jmModal.show({ meta: { name: 'Map' }, format: 'node_array', data: nodes });
        enablePinchToZoom(content, jmModal);

        setTimeout(function() {
            scaleAndCenter(content);
            applyNodeStyles(content, nodeStyles);
            applyLineStyles(content, nodeStyles, jmModal);
        }, 200);
    }

    function enablePinchToZoom(el, jmInst) {
        var startDist = 0, initialZoom = 1, isPinching = false, rafId = null;
        var updateScale = function() { scaleAndCenter(el); rafId = null; };

        el.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                isPinching = true;
                startDist  = Math.hypot(e.touches[0].pageX - e.touches[1].pageX, e.touches[0].pageY - e.touches[1].pageY);
                initialZoom = parseFloat(el.getAttribute('data-user-zoom') || '1');
            }
        }, { passive: false });

        el.addEventListener('touchmove', function(e) {
            if (isPinching && e.touches.length === 2) {
                e.preventDefault();
                var d = Math.hypot(e.touches[0].pageX - e.touches[1].pageX, e.touches[0].pageY - e.touches[1].pageY);
                var newZoom = Math.min(Math.max(initialZoom * (d / startDist), 0.5), 10);
                el.setAttribute('data-user-zoom', newZoom.toString());
                if (!rafId) rafId = requestAnimationFrame(updateScale);
            }
        }, { passive: false });

        el.addEventListener('touchend', function(e) { if (e.touches.length < 2) isPinching = false; });

        var isDragging = false, startX, startY, scrollLeft, scrollTop;
        var velocityX = 0, velocityY = 0, lastX, lastY, lastTime;

        var startDragging = function(e) {
            isDragging = true;
            var pageX = e.pageX || (e.touches ? e.touches[0].pageX : 0);
            var pageY = e.pageY || (e.touches ? e.touches[0].pageY : 0);
            startX = pageX - el.offsetLeft; startY = pageY - el.offsetTop;
            scrollLeft = el.scrollLeft; scrollTop = el.scrollTop;
            lastX = pageX; lastY = pageY; lastTime = Date.now();
            velocityX = velocityY = 0;
        };

        var stopDragging = function() { isDragging = false; requestAnimationFrame(applyMomentum); };

        var moveDragging = function(e) {
            if (!isDragging || isPinching) return;
            var pageX = e.pageX || (e.touches ? e.touches[0].pageX : 0);
            var pageY = e.pageY || (e.touches ? e.touches[0].pageY : 0);
            var dt = Date.now() - lastTime;
            if (dt > 0) { velocityX = (pageX - lastX) / dt; velocityY = (pageY - lastY) / dt; }
            lastX = pageX; lastY = pageY; lastTime = Date.now();
            el.scrollLeft = scrollLeft - ((pageX - el.offsetLeft) - startX);
            el.scrollTop  = scrollTop  - ((pageY - el.offsetTop)  - startY);
        };

        var applyMomentum = function() {
            if (isDragging || (Math.abs(velocityX) < 0.1 && Math.abs(velocityY) < 0.1)) return;
            el.scrollLeft -= velocityX * 16; el.scrollTop -= velocityY * 16;
            velocityX *= 0.92; velocityY *= 0.92;
            requestAnimationFrame(applyMomentum);
        };

        el.addEventListener('mousedown',  startDragging);
        el.addEventListener('mouseleave', stopDragging);
        el.addEventListener('mouseup',    stopDragging);
        el.addEventListener('mousemove',  moveDragging);
        el.addEventListener('touchstart', function(e) { if (e.touches.length === 1) startDragging(e); }, { passive: true });
        el.addEventListener('touchend',   stopDragging, { passive: true });
        el.addEventListener('touchmove',  function(e) { if (e.touches.length === 1) moveDragging(e); }, { passive: true });
    }

    function getS(key) {
        return (typeof mindMapStudioSettings !== 'undefined') ? mindMapStudioSettings[key] : null;
    }

    function applyLineStyleOverrides() {
        if (typeof jsMind === 'undefined') return;
        if (jsMind.graph_svg && !jsMind.graph_svg.prototype._bezier_to_orig) {
            jsMind.graph_svg.prototype._bezier_to_orig = jsMind.graph_svg.prototype._bezier_to;
            jsMind.graph_svg.prototype._bezier_to = function (path, x1, y1, x2, y2) {
                var style = (this.opts.line_style || 'bezier');
                if (style === 'straight') {
                    path.setAttribute('d', 'M' + x1 + ' ' + y1 + ' L' + x2 + ' ' + y2);
                } else if (style === 'rounded') {
                    var midX = x1 + (x2 - x1) * 0.5;
                    path.setAttribute('d', 'M' + x1 + ' ' + y1 + ' L' + midX + ' ' + y1 + ' L' + midX + ' ' + y2 + ' L' + x2 + ' ' + y2);
                } else {
                    this._bezier_to_orig(path, x1, y1, x2, y2);
                }
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
            };
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 150); });
    } else {
        setTimeout(boot, 150);
    }
}());
