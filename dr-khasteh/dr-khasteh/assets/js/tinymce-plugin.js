/**
 * Dr. Khasteh – TinyMCE Plugin
 */
( function () {
	'use strict';

	tinymce.PluginManager.add( 'dr_khasteh_text_styler', function ( editor ) {

		editor.on('BeforeSetContent', function(e) {
			if (e.content) {
				// Match spaces before any element with class starting with 'plugin-'
				// Converting them to &nbsp; ensures TinyMCE and WordPress don't trim them.
				e.content = e.content.replace(/([ ]+)(<[^>]+class=["'][^"']*plugin-[^"']*["'])/g, function(match, p1, p2) {
					return p1.replace(/ /g, '&nbsp;') + p2;
				});
			}
		});

        function addHighlightButton(id, key, title, text) {
            editor.addButton( id, {
                title  : title,
                text   : text,
                icon   : false,
                onclick: function () {
                    var sel = editor.selection.getContent();
                    if ( ! sel ) { return; }
                    editor.insertContent( '<span class="plugin-highlight-' + key + '">' + sel + '</span>' );
                },
            } );
        }

        function addBoxButton(id, key, title, text) {
            editor.addButton( id, {
                title  : title,
                text   : text,
                icon   : false,
                onclick: function () {
                    var sel = editor.selection.getContent();
                    if ( ! sel ) { return; }
                    editor.insertContent( '<div class="plugin-box-' + key + '">' + sel + '</div>' );
                },
            } );
        }

		// Standard highlights
		addHighlightButton('drkh_hl_yellow', 'yellow', 'Yellow Highlight', 'HL');
		addHighlightButton('drkh_hl_green', 'green', 'Green Highlight', 'GR');
		addHighlightButton('drkh_hl_blue', 'blue', 'Blue Highlight', 'BL');
		addHighlightButton('drkh_hl_red', 'red', 'Red Highlight', 'RD');

		// Standard boxes
		addBoxButton('drkh_box_info', 'info', 'Info Box', 'INF');
		addBoxButton('drkh_box_warning', 'warning', 'Warning Box', 'WAR');
		addBoxButton('drkh_box_success', 'success', 'Success Box', 'SUC');
		addBoxButton('drkh_box_custom', 'custom', 'Custom Box', 'CUS');

        // Custom Styles
        editor.addButton( 'drkh_custom_tip_box', {
            title  : 'نکته',
            text   : 'نکته',
            icon   : false,
            onclick: function () {
                var html = '<span class="plugin-tip_box" contenteditable="false">' +
                    '<span class="fold">&nbsp;</span>' +
                    '<span class="points_wrapper">' +
                        '<span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span>' +
                        '<span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span>' +
                    '</span>' +
                    '<span class="inner">' +
                        'نکته' +
                    '</span>' +
                '</span>&nbsp;';
                editor.insertContent( html );
            },
        } );

        // Numbers 0-9
        for (var i = 0; i <= 9; i++) {
            (function(n) {
                editor.addButton('drkh_n' + n, {
                    title: 'Number ' + n,
                    text: '' + n,
                    icon: false,
                    onclick: function() {
                        editor.insertContent('<span class="plugin-num-badge" contenteditable="false"><span class="plugin-num-inner plugin-num-' + n + '">' + n + '</span></span>&nbsp;');
                    }
                });
            })(i);
        }

        // Custom Symbols
        if (window.drKhastehTextStylerConfig && window.drKhastehTextStylerConfig.symbols) {
            window.drKhastehTextStylerConfig.symbols.forEach(function(sym) {
                editor.addButton('drkh_sym_' + sym.key, {
                    title: sym.label || 'Symbol',
                    text: sym.char,
                    icon: false,
                    onclick: function() {
                        editor.insertContent('<span class="plugin-symbol plugin-symbol-' + sym.key + '" contenteditable="false">' + sym.char + '</span>&nbsp;');
                    }
                });
            });
        }

        editor.addButton( 'drkh_custom_accent_h3', {
            title  : 'Accent Heading',
            text   : 'H3+',
            icon   : false,
            onclick: function () {
                var sel = editor.selection.getContent();
                if ( ! sel ) { return; }
                editor.insertContent( '<h3 class="plugin-accent_h3">' + sel + '</h3>' );
            },
        } );

        editor.addButton( 'drkh_custom_symbol_bullet', {
            title  : 'Symbol Bullet',
            text   : '✦',
            icon   : false,
            onclick: function () {
                var sel = editor.selection.getContent();
                if ( ! sel ) { return; }
                editor.insertContent( '<span class="plugin-symbol_bullet">' + sel + '</span>' );
            },
        } );

        editor.addButton( 'drkh_custom_glow_text', {
            title  : 'Glow Text',
            text   : 'GLO',
            icon   : false,
            onclick: function () {
                var sel = editor.selection.getContent();
                if ( ! sel ) { return; }
                editor.insertContent( '<span class="plugin-glow_text">' + sel + '</span>' );
            },
        } );

        editor.addButton( 'drkh_custom_corner_border', {
            title  : 'Corner Border',
            text   : 'CNR',
            icon   : false,
            onclick: function () {
                var sel = editor.selection.getContent();
                if ( ! sel ) { return; }
                editor.insertContent( '<div class="plugin-corner_border">' + sel + '</div>' );
            },
        } );

		editor.addButton( 'drkh_remove', {
			title  : 'Remove Style',
			text   : '✕',
			icon   : false,
			onclick: function () {
				var node = editor.selection.getNode();
				var parent = editor.dom.getParent(node, function(n) {
					return n.className && typeof n.className === 'string' && n.className.indexOf('plugin-') !== -1;
				});
				if ( parent ) {
					editor.dom.remove( parent, true );
				} else if ( node && node.className && typeof node.className === 'string' && node.className.indexOf('plugin-') !== -1 ) {
					editor.dom.remove( node, true );
				}
			},
		} );

	} );
}() );
