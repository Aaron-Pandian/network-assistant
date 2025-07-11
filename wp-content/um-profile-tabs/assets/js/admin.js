jQuery(document).ready( function() {
	jQuery(document.body).on( 'change', '#title', function() {
		let title = jQuery(this).val();
		let slugField = jQuery('#um_profile_tab__tab_slug:visible');
		if ( '' !== title && '' === slugField.val() ) {
			slugField.val( wp.url.cleanForSlug( title ) );
		}
	});
});
