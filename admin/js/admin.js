
jQuery(document).ready( function($) {
	
	// Taxonomies
	if ( $('.tagsdiv').length ) {
		tagBox.init();
	}
	
	// Admin Menu
	if ($('body.attachmenttaxsupp').length > 0) {
		$('#menu-media .wp-submenu li a').each(function(){
			if ($(this).attr('href') == 'edit-tags.php?taxonomy=' + attachmentTaxSuppSettings.taxonomy + '&post_type=attachment') {
				// Deselect Posts Menu
				$('#menu-posts').removeClass('wp-has-current-submenu').removeClass('wp-menu-open').removeClass('open-if-no-js').addClass('wp-not-current-submenu');
				$('#menu-posts > a').removeClass('wp-has-current-submenu').addClass('wp-not-current-submenu');
				// Select Media Menu
				$('#menu-media').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu').addClass('wp-menu-open').addClass('open-if-no-js');
				$('#menu-media > a').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu');
				// Select submenu item
				$(this).closest('li').addClass('current');
			}
		});
	}

});
