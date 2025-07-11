if ( typeof ( wp.UM ) !== 'object' ) {
	wp.UM = {};
}

if ( typeof ( wp.UM.user_locations ) !== 'object' ) {
	wp.UM.user_locations = {};
}

wp.UM.user_locations.map_shortcode = {
	infowindow: {
		customize: function() {
			var iwOuter = jQuery('.gm-style-iw');

			var iwBackground = iwOuter.prev();

			// Removes background shadow DIV
			iwBackground.children(':nth-child(2)').css({'display' : 'none'});

			// Removes white background DIV
			iwBackground.children(':nth-child(4)').css({'display' : 'none'});

			// Moves the infowindow 115px to the right.
			iwOuter.parent().parent().css({left: '115px'});

			// Moves the shadow of the arrow 76px to the left margin.
			iwBackground.children(':nth-child(1)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

			// Moves the arrow 76px to the left margin.
			iwBackground.children(':nth-child(3)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

			// Changes the desired tail shadow color.
			iwBackground.children(':nth-child(3)').find('div').children().css({'box-shadow': 'rgba(72, 181, 233, 0.6) 0px 1px 6px', 'z-index' : '1'});

			// Reference to the div that groups the close button elements.
			var iwCloseBtn = iwOuter.next();

			// Apply the desired effect to the close button
			iwCloseBtn.css({
				opacity: '1',
				right: '48px',
				top: '20px',
				'border-radius': '50%'
			});

			// The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
			iwCloseBtn.mouseout(function(){
				jQuery(this).css({opacity: '1'});
			});
		},
		close: function( flush ) {

			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data'] === 'undefined' ) {
				return;
			}

			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] !== 'undefined' ) {
				jQuery.each( wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'], function( user_id ) {
					wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ].close();
				});
			}

			if ( flush ) {
				wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] = {};
			}
		}
	},

	get_map: function() {

		if ( typeof wp.UM.user_locations.map_shortcode.blocks['data'] !== 'undefined' ) {
			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['map'] !== 'undefined' ) {
				return wp.UM.user_locations.map_shortcode.blocks['data']['map'];
			}
		}

		return null;
	},

	init: function( map ) {

		if ( map.hasClass( 'um-map-inited' ) ) {
			return;
		}

		wp.UM.user_locations.map_shortcode.blocks['data'] = [];

		var latlng = new google.maps.LatLng( map.data( 'lat' ), map.data( 'lng' ) );
		var zoom = map.data( 'zoom' );
		var field = map.data( 'field' );

		if ( typeof zoom == 'undefined' ) {
			zoom = 12;
		}

		var map_args = wp.hooks.applyFilters( 'um_user_locations_map_shortcode_args_init', {
			center: latlng,
			zoom: zoom,
			field: field
		}, 'data', map );

		wp.UM.user_locations.map_shortcode.blocks['data']['map'] = new google.maps.Map( map[0], map_args );

		wp.UM.user_locations.map_shortcode.ajax.handler( map, field );

		map.removeClass( wp.UM.user_locations.map_shortcode.classes.hidden ).addClass( 'um-map-inited' );

		return map;
	},

	ajax: {
		handler: function( map, field ) {
			var request = {
				marker_type:  map.data('marker_type'),
				field:  field,
				nonce:  um_scripts.nonce
			};

			wp.ajax.send( 'um_get_map_members_data', {
				data:  request,
				success: function( answer ) {
					wp.UM.user_locations.map_shortcode.whengoogleloadeddo( wp.UM.user_locations.map_shortcode.ajax.response, [ map, answer ] );
				},
				error: function( data ) {
					console.log( data );
				}
			});
		},
		response: function( map, data ) {
			var field = map.data( 'field' );
			var field_title = map.data( 'field_title' );

			var current_markers = {};
			Object.getOwnPropertyNames( current_markers ).forEach(function ( prop ) {
				delete current_markers[ prop ];
			});

			jQuery.each( data.users, function( key ) {
				var userdata = data.users[ key ];

				if ( typeof current_markers[ userdata.id ] === 'undefined' ) {
					current_markers[ userdata.id ] = {};
				}

				if ( typeof userdata[ field + '_lat' ] !== 'undefined' && typeof userdata[ field + '_lng' ] !== 'undefined' ) {
					var latlng = new google.maps.LatLng( userdata[ field + '_lat' ], userdata[ field + '_lng' ] );

					var infowindow_template = wp.template( 'um-user-location-map-shortcode-marker-infowindow' );

					var infowindow_attr = {
						content: infowindow_template( {
							'field': field,
							'userdata': userdata
						} ),
						maxWidth: 350
					};
					infowindow_attr = wp.hooks.applyFilters( 'um_user_locations_infowindow_data', infowindow_attr, 'data', userdata.id );

					var marker_data = {
						position:       latlng,
						map:            wp.UM.user_locations.map_shortcode.get_map(),
						title:          userdata.display_name + ' ' + field_title,
						zIndex:         key + 1,
						optimized:      false,
						infowindow:     infowindow_attr
					};

					if ( userdata['avatar_url'] !== '' ) {
						var suffix;
						if ( userdata['avatar_url'].indexOf( '?' ) === -1 ) {
							suffix = '?um_avatar_marker=1';
						} else {
							suffix = '&um_avatar_marker=1';
						}

						marker_data.icon = {
							url:        userdata['avatar_url'] + suffix,
							scaledSize: new google.maps.Size(32, 32),
							size:       new google.maps.Size(36, 40),
							anchor:     new google.maps.Point(18, 18)
						};

						marker_data.shape = {
							coords: [ 18, 18, 20 ],
							type:   'circle'
						};
					}

					marker_data = wp.hooks.applyFilters( 'um_user_locations_marker_data', marker_data, 'data', userdata );

					current_markers[ userdata.id ] = marker_data;
				}
			});

			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['markers'] === 'undefined' ) {
				wp.UM.user_locations.map_shortcode.blocks['data']['markers'] = {};
			}

			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] === 'undefined' ) {
				wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] = {};
			}

			if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['marker_clusterer'] !== 'undefined' ) {
				wp.UM.user_locations.map_shortcode.blocks['data']['marker_clusterer'].clearMarkers();
			}

			jQuery.each( current_markers, function( user_id ) {
				if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ] === 'undefined' ) {
					wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ] = {};
				}

				if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ] == 'undefined' ) {
					wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ] = {};
				}

				wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ] = new google.maps.Marker( current_markers[ user_id ] );
				wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ] = new google.maps.InfoWindow( current_markers[ user_id ][ 'infowindow' ] );

				// Click on marker
				wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ].addListener('click', function(e) {
					// avoid the click conflict with markerClusterer: when click on cluster - the infowindow is opened
					if ( typeof e === 'undefined' ) {
						wp.UM.user_locations.map_shortcode.infowindow.close();
						return;
					}

					wp.UM.user_locations.map_shortcode.infowindow.close();

					wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ]['status'] = 'open';

					wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ].open(
						wp.UM.user_locations.map_shortcode.get_map(),
						wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ]
					);
				});

				google.maps.event.addListener( wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ], 'closeclick', function() {
					wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ]['status'] = 'closed';
				});
			});

			wp.hooks.doAction( 'um_member_directory_after_markers_init', false, current_markers );

			var disableSpiderfier = false;
			disableSpiderfier = wp.hooks.applyFilters( 'um_map_shortcode_disable_spiderfier', disableSpiderfier );

			if ( ! disableSpiderfier ) {
				var spiderConfig = {
					keepSpiderfied: true,
					event: 'mouseover'
				};

				var markerSpiderfier = new OverlappingMarkerSpiderfier( wp.UM.user_locations.map_shortcode.get_map(), spiderConfig );

				var markers = [];
				if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['markers'] !== 'undefined' ) {
					jQuery.each( wp.UM.user_locations.map_shortcode.blocks['data']['markers'], function( user_id ) {
						markers.push( wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ] );
						markerSpiderfier.addMarker( wp.UM.user_locations.map_shortcode.blocks['data']['markers'][ user_id ] );
					});
				}

				markerSpiderfier.addListener('spiderfy', function( markers ) {
					if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] !== 'undefined' ) {
						jQuery.each( wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'], function( user_id ) {
							wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ].close();
						});
					}
				});

				markerSpiderfier.addListener('unspiderfy', function( markers ) {
					if ( typeof wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'] !== 'undefined' ) {
						jQuery.each( wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'], function( user_id ) {
							wp.UM.user_locations.map_shortcode.blocks['data']['infowindows'][ user_id ].close();
						});
					}
				});
			}

			var disableClustering = false;
			disableClustering = wp.hooks.applyFilters( 'um_map_shortcode_disable_clustering', disableClustering );

			if ( ! disableClustering ) {
				// gridSize: 60 - default clustering gridSize, please use smaller for biggest zooms
				var clustering_options = wp.hooks.applyFilters( 'um_user_locations_marker_clustering_options',
					{imagePath: um_user_location_map_shortcode.cluster_url}, 'data', wp.UM.user_locations.map_shortcode.get_map(), markers );

				// Add a marker clusterer to manage the markers.
				wp.UM.user_locations.map_shortcode.blocks['data']['marker_clusterer'] = new MarkerClusterer( wp.UM.user_locations.map_shortcode.get_map(), markers, clustering_options );

				wp.UM.user_locations.map_shortcode.blocks['data']['marker_clusterer'].setMaxZoom( 20 );

				google.maps.event.addListener( wp.UM.user_locations.map_shortcode.blocks['data']['marker_clusterer'], 'clusterclick', function( cluster ) {
					var current_cluster = cluster.getMarkers()[0];
					setTimeout( function() {
						google.maps.event.trigger( current_cluster, 'click' );
					},1000 );
				});
			}
		}
	},

	whengoogleloadeddo: function( fnt, args ) {
		if ( typeof google != 'undefined' ) {
			fnt( args[0], args[1] );
		} else {
			setTimeout(function()
			{(function(fnt)
			{
				wp.UM.user_locations.map_shortcode.whengoogleloadeddo( fnt, args )
			})(fnt)}, 500); // You can set timer as you wish //
		}
	},

	blocks: [],
	classes: {
		hidden: 'um-user-location-hidden-map'
	}
};


function UMInitMapShortcode() {
	jQuery( '.um-member-map-shortcode' ).each( function() {
		var map = jQuery(this);
		wp.UM.user_locations.map_shortcode.init( map );
	});
}

var um_ul_script = document.createElement( 'script' );
um_ul_script.src = '//maps.googleapis.com/maps/api/js?key=' + um_user_location_map_shortcode.api_key + '&libraries=places&callback=UMInitMapShortcode';
if ( um_user_location_map_shortcode.region ) {
	um_ul_script.src += '&language=' + um_user_location_map_shortcode.region;
}
document.body.appendChild( um_ul_script );