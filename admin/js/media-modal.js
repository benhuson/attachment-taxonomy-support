
jQuery( document ).ready( function( $ ) {
	console.log('loaded');
	// Only in the media modal
	$( '.media-modal .media-sidebar .cat-checklist' ).delegate( 'input', 'change', function() {
		console.log('x');
	} );

} );
