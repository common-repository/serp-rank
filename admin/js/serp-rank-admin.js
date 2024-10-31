(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( window ).load(
		function() {
			$( '.serpr-keys-list' ).each(
				function(k, el){
					if ( $( el ).find( '.compact-list' ).length > 3) {
						 $( el ).find( '.compact-list:gt(2)' ).hide();
						 $( el ).find( '.show-more' ).show();
					}
				}
			);
			$( '.serpr-keys-list .show-more' ).on(
				'click',
				function(e) {
					$( this ).parent().find( '.compact-list:gt(2)' ).toggle();
					$( this ).text() === 'Show more' ? $( this ).text( 'Show less' ) : $( this ).text( 'Show more' );
				}
			);
		}
	);
})( jQuery );
