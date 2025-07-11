jQuery(document).ready(function() {
	if ( jQuery('#um-user-location-current-lat').length ) {
		if ( navigator.geolocation ) {
			function um_user_location_set_current_location( position ) {
				jQuery('#um-user-location-current-lat').val( position.coords.latitude );
				jQuery('#um-user-location-current-lng').val( position.coords.longitude );

				jQuery('.um-field-distance, .um-distance-meta').each( function() {
					var field_wrapper = jQuery(this);
					var result_wrapper = field_wrapper.find('.um-user-location-distance-calculation-result');
					var location_key = result_wrapper.data('location_source');

					wp.ajax.send( 'um_get_user_distance', {
						data: {
							location: [
								jQuery('#um-user-location-current-lat').val(),
								jQuery('#um-user-location-current-lng').val()
							],
							user_coords: [
								jQuery('#um-user-location-distance-' + location_key + '-lat').val(),
								jQuery('#um-user-location-distance-' + location_key + '-lng').val()
							],
							unit: result_wrapper.data('distance_unit'),
							nonce: um_scripts.nonce
						},
						success: function ( answer ) {
							result_wrapper.text( answer );
						},
						error: function ( data ) {
							console.log( data );
						}
					});

				});
			}

			function um_user_location_set_current_location_error( error ) {
				if ( error.code = error.PERMISSION_DENIED ) {
					jQuery('#um-user-location-current-denied').val( 1 );
				}
			}

			navigator.geolocation.getCurrentPosition( um_user_location_set_current_location, um_user_location_set_current_location_error );
		}
	}
});


// don't call AJAX while getting current user location
wp.hooks.addFilter( 'um_member_directory_get_members_allow', 'um_user_locations_distance', function( allow, hash, directory ) {
	if ( ! directory.find('#um-user-location-current-lat').length || ! directory.find('#um-user-location-current-lng').length ) {
		return allow;
	}

	if ( parseInt( directory.find('#um-user-location-current-denied').val(), 10 ) === 1 ) {
		return allow;
	}

	if ( directory.find('#um-user-location-current-lat').val() === '' ||
		 directory.find('#um-user-location-current-lng').val() === '' ) {
		allow = false;
	}

	return allow;
}, 10 );

// extends AJAX request arguments
wp.hooks.addFilter( 'um_member_directory_filter_request', 'um_user_locations', function( request ) {
	var directory = jQuery( '.um-directory[data-hash="' + request.directory_id + '"]' );

	if ( ! directory.length ) {
		return request;
	}

	if ( ! directory.find('#um-user-location-current-lat').length || ! directory.find('#um-user-location-current-lng').length ) {
		return request;
	}

	if ( directory.find('#um-user-location-current-lat').val() !== '' &&
		directory.find('#um-user-location-current-lng').val() !== '' ) {
		var lat = directory.find('#um-user-location-current-lat').val();
		var lng = directory.find('#um-user-location-current-lng').val();
		request.current_user_location = [ lat, lng ];
	}

	return request;
}, 10 );