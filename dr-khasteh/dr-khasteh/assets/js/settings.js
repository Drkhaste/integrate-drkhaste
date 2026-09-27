/**
 * Dr. Khasteh – Admin Settings JS
 */
( function ( $ ) {
	'use strict';

	$( function () {
		// Initialise all color pickers.
		$( '.wpts-color-picker' ).wpColorPicker( {
			change: function ( event, ui ) {
				updatePreview( $( this ), ui.color.toString() );
			},
			clear: function () {
				updatePreview( $( this ), '' );
			},
		} );

		// Accordion functionality.
		$( '.drkh-accordion-header' ).on( 'click', function () {
			var $item    = $( this ).closest( '.drkh-accordion-item' );
			var $content = $item.find( '.drkh-accordion-content' );

			$content.slideToggle( 300, function() {
				$item.toggleClass( 'active' );
			} );
		} );

		function updatePreview( $input, color ) {
			var $row = $input.closest( 'tr' );

			// Update highlight swatch.
			if ( $input.attr('name').indexOf('highlights') !== -1 ) {
                if ( $input.attr('name').indexOf('dark_color') !== -1 ) {
                    $row.find( '.wpts-preview-swatch-dark' ).css( 'background', color );
                } else {
                    $row.find( '.wpts-preview-swatch' ).css( 'background', color );
                }
				return;
			}

            // Box previews are a bit more complex in the new layout,
            // but for now let's just make sure color pickers work.
		}
	} );

}( jQuery ) );
