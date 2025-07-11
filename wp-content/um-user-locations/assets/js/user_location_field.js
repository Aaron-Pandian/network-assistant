var um_locations_maps = {};
var um_locations_maps_view = {};
var um_locations_map_markers = {};
var um_locations_map_view_markers = {};

function UMLocationSelectOnEnter( input ) {
	// store the original event binding function
	var _addEventListener = ( input.addEventListener ) ? input.addEventListener : input.attachEvent;

	function addEventListenerWrapper( type, listener ) {
		// Simulate a 'down arrow' keypress on hitting 'return' when no pac suggestion is selected,
		// and then trigger the original listener.

		if ( type === 'keydown' ) {
			var orig_listener = listener;

			listener = function( event ) {
				var suggestion_selected = jQuery(".pac-item-selected").length > 0;

				if ( event.which === 13 && ! suggestion_selected ) {
					var simulated_downarrow = jQuery.Event( "keydown", {
						keyCode: 40,
						which: 40
					});

					orig_listener.apply( input, [ simulated_downarrow ] );

					$selected_autocomplete = jQuery( input );
				}

				orig_listener.apply( input, [ event ] );
			};
		}

		_addEventListener.apply( input, [ type, listener ] );
	}

	if ( input.addEventListener ) {
		input.addEventListener = addEventListenerWrapper;
	} else if ( input.attachEvent ) {
		input.attachEvent = addEventListenerWrapper;
	}
}

function UMplaceMarker( position, map, key ) {
	if ( ! um_locations_map_markers[ key ] ) {
		var marker_data = {
			map: um_locations_maps[ key ],
			draggable: false
		};

		um_locations_map_markers[ key ] = new google.maps.Marker( marker_data );
	}

	um_locations_map_markers[ key ].setPosition({
		lat: position.lat(),
		lng: position.lng()
	});

	var $obj = jQuery( '.um-form-field[data-key=' + key + ']' );

	map.panTo( position );

	// init google geocoder
	geocoder = new google.maps.Geocoder();

	// run geocoder
	geocoder.geocode( {
		latLng : new google.maps.LatLng( position.lat(), position.lng() ),
		region : um_user_location_var.region
	}, function( results, status ) {
		if ( status == google.maps.GeocoderStatus.OK ) { // on success
			if ( typeof results[0].formatted_address != 'undefined' ) {

				var formatted_address = wp.hooks.applyFilters( 'um_user_location_formatted_address_set_by_geocode', results[0].formatted_address, results, position );

				$obj.val( formatted_address );
				$obj.siblings('.um_lat_param').val( position.lat() );
				$obj.siblings('.um_lng_param').val( position.lng() );
				$obj.siblings('.um_url_param').val( 'https://www.google.com/maps/search/?api=1&query=' + position.lat() + ',' + position.lng() );

				wp.hooks.doAction( 'um_user_location_after_location_field_set_by_geocode', results, position );
			}
		}
	});
}

function UMInitUserLocationAutocomplete() {
	var $selected_autocomplete;

	if ( jQuery('.um-user-locations-map-field-view').length ) {
		jQuery('.um-user-locations-map-field-view').each( function() {
			if ( '' !== jQuery(this).data('lat') && '' !== jQuery(this).data('lng') ) {
				var key = jQuery(this).parents('.um-field').data('key');

				var map_args = wp.hooks.applyFilters( 'um_user_locations_map_field_args_init', {
					center:  {
						lat: parseFloat( jQuery(this).data('lat') ),
						lng: parseFloat( jQuery(this).data('lng') )
					},
					zoom: parseInt( um_user_location_var.start_coords.zoom ),
					fullscreenControl: false,
					streetViewControl: false,
					mapTypeControl: false
				}, key );

				um_locations_maps_view[ key ] = new google.maps.Map( jQuery(this).get(0), map_args );

				um_locations_map_view_markers[ key ] = new google.maps.Marker( {
					position:  {
						lat: parseFloat( jQuery(this).data('lat') ),
						lng: parseFloat( jQuery(this).data('lng') )
					},
					map: um_locations_maps_view[ key ],
					draggable: false
				} );
			}
		});
	}

	//remove marker when location field is empty
	jQuery( document.body ).on( 'keyup', '.um_user_location_g_autocomplete', function() {
		if ( jQuery(this).val() === '' ) {
			jQuery(this).siblings('.um_lat_param').val( '' );
			jQuery(this).siblings('.um_lng_param').val( '' );
			jQuery(this).siblings('.um_url_param').val( '' );

			if ( jQuery(this).siblings('.um_user_location_g_autocomplete_map').length ) {
				if ( um_locations_map_markers[ jQuery(this).data('key') ] ) {
					um_locations_map_markers[ jQuery(this).data('key') ].setMap( null );
					delete um_locations_map_markers[ jQuery(this).data('key') ];
				}
			}

			wp.hooks.doAction( 'um_user_location_after_location_field_empty', jQuery(this) );
		}
	});

	jQuery('.um_user_location_g_autocomplete').each( function() {

		UMLocationSelectOnEnter( jQuery(this).get(0) );

		var directory = jQuery(this).parents('.um-directory');
		if ( directory.length ) {
			return;
		}

		var autocomplete_args = wp.hooks.applyFilters( 'um_user_locations_autocomplete_args', {
			types: ['geocode']
		}, false, jQuery(this) );

		var autocomplete = new google.maps.places.Autocomplete( jQuery(this).get(0), autocomplete_args );

		if ( jQuery(this).siblings('.um_user_location_g_autocomplete_map').length ) {
			var pre_set = false;
			var center = {
				lat: parseFloat( um_user_location_var.start_coords.lat ),
				lng: parseFloat( um_user_location_var.start_coords.lng )
			};
			if ( '' !== jQuery(this).siblings('.um_lat_param').val() && '' !== jQuery(this).siblings('.um_lng_param').val() ) {
				pre_set = true;
				center = {
					lat: parseFloat( jQuery(this).siblings('.um_lat_param').val() ),
					lng: parseFloat( jQuery(this).siblings('.um_lng_param').val() )
				};
			}

			um_locations_maps[ jQuery(this).data('key') ] = new google.maps.Map( jQuery(this).siblings('.um_user_location_g_autocomplete_map').get(0), {
				center: center,
				zoom: parseInt( um_user_location_var.start_coords.zoom ),
				fullscreenControl: false,
				streetViewControl: false,
				mapTypeControl: false
			});

			var marker_data = {
				map: um_locations_maps[ jQuery(this).data('key') ],
				draggable: false
			};
			if ( pre_set ) {
				marker_data.position = {
					lat: parseFloat( jQuery(this).siblings('.um_lat_param').val() ),
					lng: parseFloat( jQuery(this).siblings('.um_lng_param').val() )
				};
			}

			um_locations_map_markers[ jQuery(this).data('key') ] = new google.maps.Marker( marker_data );

			var map = um_locations_maps[ jQuery(this).data('key') ];
			var key = jQuery(this).data('key');

			google.maps.event.addListener( map, 'click', function(e) {
				UMplaceMarker( e.latLng, map, key );
			});
		}

		autocomplete.addListener( 'place_changed', function(e) {
			var place = this.getPlace();

			if ( typeof place == 'undefined' || typeof place.geometry == 'undefined' || typeof place.geometry.location == 'undefined' ) {
				if ( typeof $selected_autocomplete !== 'undefined' ) {
					$selected_autocomplete.siblings('.um_lat_param').val( '' );
					$selected_autocomplete.siblings('.um_lng_param').val( '' );
					$selected_autocomplete.siblings('.um_url_param').val( '' );

					wp.hooks.doAction( 'um_user_location_after_location_field_empty', $selected_autocomplete );
				}
				return;
			}

			var lat = place.geometry.location.lat();
			var lng = place.geometry.location.lng();

			if ( typeof $selected_autocomplete !== 'undefined' ) {
				$selected_autocomplete.siblings('.um_lat_param').val( lat );
				$selected_autocomplete.siblings('.um_lng_param').val( lng );
				$selected_autocomplete.siblings('.um_url_param').val( place.url );

				wp.hooks.doAction( 'um_user_location_after_location_field_set_by_autocomplete', place, place.geometry.location );

				if ( $selected_autocomplete.siblings('.um_user_location_g_autocomplete_map').length ) {
					um_locations_maps[ $selected_autocomplete.data('key') ].setCenter({
						lat: parseFloat(lat),
						lng: parseFloat(lng)
					});

					um_locations_maps[ $selected_autocomplete.data('key') ].fitBounds( place.geometry.viewport );

					if ( ! um_locations_map_markers[ $selected_autocomplete.data('key') ] ) {
						var marker_data = {
							map: um_locations_maps[  $selected_autocomplete.data('key') ],
							draggable: false
						};

						um_locations_map_markers[ $selected_autocomplete.data('key') ] = new google.maps.Marker( marker_data );
					}

					um_locations_map_markers[ $selected_autocomplete.data('key') ].setPosition({
						lat: parseFloat(lat),
						lng: parseFloat(lng)
					});
				}
			}
		});


	}).on('click', function() {
		$selected_autocomplete = jQuery(this);
	});

	wp.hooks.doAction( 'um_member_directory_init_autocomplete' );
}

(function($) {

	function um_set_center_marker( key, latlng ) {
		um_locations_maps[ key ].setCenter({
			lat: parseFloat( latlng.lat ),
			lng: parseFloat( latlng.lng )
		});

		um_locations_map_markers[ key ].setPosition({
			lat: parseFloat( latlng.lat ),
			lng: parseFloat( latlng.lng )
		});
	}


	$(document).ready(function() {
		//stop Enter to submit form
		jQuery( document.body ).on( 'keypress, keyup, keydown', '.um-form', function(e) {
			if ( ! jQuery( e.target ).parents( '.um-field' ).hasClass( 'um-field-textarea' ) ) {
				if ( e.which === 13 ) {
					e.preventDefault();
				}
			}
		});

		$( document.body ).on( 'click', '.um_current_user_location', function() {
			var $obj = $(this);
			if ( $obj.parents('.um-member-directory-map-controls').length > 0 ) {
				var directory = $obj.parents('.um-directory');
			}
			var autocomplete = $obj.siblings('.um_user_location_g_autocomplete');

			if ( navigator.geolocation || ! um_user_location_var.is_ssl ) {
				navigator.geolocation.getCurrentPosition(function( data ) {
					if ( typeof data.coords != 'undefined' ) {
						// init google geocoder
						geocoder = new google.maps.Geocoder();

						var geocode_args = wp.hooks.applyFilters( 'um_user_locations_geocode_args_init', {
							latLng : new google.maps.LatLng( data.coords.latitude, data.coords.longitude ),
							region : um_user_location_var.region
						}, data );

						// run geocoder
						geocoder.geocode( geocode_args, function( results, status ) {
							if ( status == google.maps.GeocoderStatus.OK ) { // on success
								if ( typeof results[0].formatted_address != 'undefined' ) {

									var formatted_address = wp.hooks.applyFilters( 'um_user_location_formatted_address_set_by_geocode', results[0].formatted_address, results, data.coords );

									autocomplete.val( formatted_address );
									$obj.siblings('.um_lat_param').val( data.coords.latitude );
									$obj.siblings('.um_lng_param').val( data.coords.longitude );
									$obj.siblings('.um_url_param').val( 'https://www.google.com/maps/search/?api=1&query=' + data.coords.latitude + ',' + data.coords.longitude );

									if ( $obj.siblings('.um_user_location_g_autocomplete_map').length ) {
										um_set_center_marker( autocomplete.data('key'), {lat: data.coords.latitude, lng: data.coords.longitude} );
									}

									wp.hooks.doAction( 'um_user_location_after_location_field_set_by_geocode', results, data.coords );

								}
							} else { // on failed
								$.support.cors = true;
								$.ajax({
									url: 'https://freegeoip.app/json/',
									type: 'GET',
									crossDomain: true,
									dataType: 'jsonp',
									success: function ( data ) {
										if ( typeof data.country_name != 'undefined' ) {

											var formatted_address = wp.hooks.applyFilters( 'um_user_location_formatted_address_set_by_freegeoip', data.country_name + ',' + data.region_name + ',' + data.city, data );

											$obj.prev().val( formatted_address );
											$obj.siblings('.um_lat_param').val( data.latitude );
											$obj.siblings('.um_lng_param').val( data.longitude );
											$obj.siblings('.um_url_param').val( 'https://www.google.com/maps/search/?api=1&query=' + data.latitude + ',' + data.longitude );

											if ( $obj.siblings('.um_user_location_g_autocomplete_map').length ) {
												um_set_center_marker( autocomplete.data('key'), {lat: data.latitude, lng: data.longitude} );
											}

											wp.hooks.doAction( 'um_user_location_after_location_field_set_by_freegeoip', data );

										} else {
											alert( wp.i18n.__( 'Can not get your current location', 'um-user-locations' ) );
										}
									},
									error: function ( data ) {
										alert( wp.i18n.__( 'Can not get your current location', 'um-user-locations' ) );
									}
								});
							}
						});
					}
				});
			}
			return false;
		} );
	});
})(jQuery);

var um_ul_script = document.createElement( 'script' );
um_ul_script.src = '//maps.googleapis.com/maps/api/js?key=' + um_user_location_var.api_key + '&libraries=places&callback=UMInitUserLocationAutocomplete';
if ( um_user_location_var.region ) {
	um_ul_script.src += '&language=' + um_user_location_var.region;
}
document.body.appendChild( um_ul_script );


// init location field when popup is loaded
wp.hooks.addAction( 'um_profile_completeness_popup_loaded', 'um_user_locations', function( popup ) {
	UMInitUserLocationAutocomplete();
});

// show header if there is map
wp.hooks.addFilter( 'um_profile_completeness_save_field_value', 'um_user_locations', function( value, type, key, popup ) {
	if ( popup.find( 'input[data-key="' + key + '_lat"]' ).length ) {
		value = {};

		value[ key ] = popup.find( 'input[data-key="' + key + '"]' ).val();
		value[ key + '_lat' ] = popup.find( 'input[data-key="' + key + '_lat"]' ).val();
		value[ key + '_lng' ] = popup.find( 'input[data-key="' + key + '_lng"]' ).val();
		value[ key + '_url' ] = popup.find( 'input[data-key="' + key + '_url"]' ).val();
	}

	return value;
}, 10 );