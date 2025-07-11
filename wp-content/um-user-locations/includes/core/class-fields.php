<?php
namespace um_ext\um_user_locations\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Fields
 * @package um_ext\um_user_locations\core
 */
class Fields {

	/**
	 * @var array
	 */
	public $location_fields = array();

	/**
	 * @var bool
	 */
	private $current_distance_visible = false;

	/**
	 * Fields constructor.
	 */
	public function __construct() {
		add_filter( 'init', array( &$this, 'init_variables' ), 2 );

		add_filter( 'um_core_fields_hook', array( &$this, 'add_location_field' ) );

		add_filter( 'um_edit_field_register_user_location', array( &$this, 'location_field_html' ), 10, 2 );
		add_filter( 'um_edit_field_profile_user_location', array( &$this, 'location_field_html' ), 10, 2 );

		add_action( 'um_social_login_before_show_overlay', array( &$this, 'add_location_assets' ) );

		// for integration with Profile Completeness when form "mode" == ''
		add_filter( 'um_edit_field__user_location', array( &$this, 'location_field_html' ), 10, 2 );

		add_filter( 'um_view_field_value_user_location', array( &$this, 'view_field_user_location' ), 10, 2 );

		add_action( 'um_add_error_on_form_submit_validation', array( &$this, 'location_validation' ), 10, 3 );

		add_action( 'um_add_new_field', array( &$this, 'save_location_fields' ), 10, 2 );
		add_action( 'um_delete_custom_field', array( &$this, 'remove_location_field' ), 10, 2 );

		add_action( 'um_admin_field_edit_hook_location_source', array( &$this, 'location_source_field' ), 10, 1 );
		add_action( 'um_admin_field_edit_hook_location_unit', array( &$this, 'location_unit_field' ), 10, 1 );
		add_filter( 'um_edit_field_register_distance', array( &$this, 'distance_field_html' ), 10, 2 );
		add_filter( 'um_edit_field_profile_distance', array( &$this, 'distance_field_html' ), 10, 2 );
		add_filter( 'um_view_field_value_distance', array( &$this, 'view_field_distance' ), 10, 2 );
		add_filter( 'um_fields_without_metakey', array( &$this, 'add_distance_field' ), 10, 1 );
		add_filter( 'um_all_user_fields_without_metakey', array( &$this, 'remove_distance_field' ), 10, 1 );

		add_filter( 'um_show_meta_item_html', array( &$this, 'meta_item_user_location_distance_html' ), 10, 2 );

		add_filter( 'um_profile_completeness_multisave_meta', array( &$this, 'profile_completeness_user_location_field' ), 10, 2 );

		// for um_metadata table
		add_filter( 'um_metadata_same_page_update_ajax', array( &$this, 'same_page_update_ajax' ), 10, 2 );
		add_action( 'um_metadata_on_new_field_added', array( &$this, 'on_new_field_added' ), 10, 3 );
		add_action( 'um_metadata_on_delete_custom_field', array( &$this, 'on_delete_custom_field' ), 10, 3 );
	}


	/**
	 * Handle user location field in the profile completeness widget as array with multi meta keys
	 *
	 * @param bool $multisave
	 * @param string $key
	 *
	 * @return bool
	 */
	function profile_completeness_user_location_field( $multisave, $key ) {
		$field_type = UM()->fields()->get_field_type( $key );
		if ( $field_type == 'user_location' ) {
			$multisave = true;
		}

		return $multisave;
	}


	/**
	 * @param $metakeys
	 * @param $all_user_fields
	 *
	 * @return array
	 */
	public function same_page_update_ajax( $metakeys, $all_user_fields ) {
		foreach ( $all_user_fields as $all_user_field ) {
			if ( array_key_exists( 'type', $all_user_field ) && 'user_location' === $all_user_field['type'] ) {
				$metakeys[] = $all_user_field['metakey'] . '_lat';
				$metakeys[] = $all_user_field['metakey'] . '_lng';
				$metakeys[] = $all_user_field['metakey'] . '_url';
			}
		}

		return $metakeys;
	}


	/**
	 * @param $metakeys
	 * @param $metakey
	 * @param $args
	 *
	 */
	function on_new_field_added( $metakeys, $metakey, $args ) {
		if ( $args['type'] == 'user_location' ) {
			$update = false;
			if ( ! in_array( $metakey . '_lat', $metakeys ) ) {
				$update     = true;
				$metakeys[] = $metakey . '_lat';
			}

			if ( ! in_array( $metakey . '_lng', $metakeys ) ) {
				$update     = true;
				$metakeys[] = $metakey . '_lng';
			}

			if ( ! in_array( $metakey . '_url', $metakeys ) ) {
				$update     = true;
				$metakeys[] = $metakey . '_url';
			}

			if ( ! in_array( $metakey, $metakeys ) ) {
				$update     = true;
				$metakeys[] = $metakey;
			}

			if ( $update ) {
				update_option( 'um_usermeta_fields', array_values( $metakeys ) );
			}
		}
	}


	/**
	 * @param $metakeys
	 * @param $metakey
	 * @param $args
	 *
	 */
	function on_delete_custom_field( $metakeys, $metakey, $args ) {
		if ( $args['type'] == 'user_location' ) {
			if ( array_intersect( array( $metakey . '_lat', $metakey . '_lng', $metakey . '_url' ), $metakeys ) ) {
				if ( false !== $searched = array_search( $metakey . '_lat', $metakeys ) ) {
					unset( $metakeys[ $searched ] );
				}
				if ( false !== $searched = array_search( $metakey . '_lng', $metakeys ) ) {
					unset( $metakeys[ $searched ] );
				}
				if ( false !== $searched = array_search( $metakey . '_url', $metakeys ) ) {
					unset( $metakeys[ $searched ] );
				}

				global $wpdb;

				$wpdb->query( $wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}um_metadata
					WHERE um_key = %s OR
						  um_key = %s OR
						  um_key = %s",
					$metakey . '_lat',
					$metakey . '_lng',
					$metakey . '_url'
				) );

				update_option( 'um_usermeta_fields', array_values( $metakeys ) );
			}
		}
	}


	/**
	 * Add possible user tags
	 *
	 * @param $array
	 * @param $key
	 * @param $args
	 */
	function location_validation( $array, $key, $args ) {
		if ( isset( $array['type'] ) && $array['type'] == 'user_location' &&
			 isset( $array['required'] ) && $array['required'] == 1 &&
			 ( empty( $args[ $key ] ) || empty( $args[ $key . '_lat' ] ) || empty( $args[ $key . '_lng' ] ) ) ) {

			if ( empty( $array['label'] ) ) {
				UM()->form()->add_error( $key, __( 'This field is required', 'um-user-locations' ) );
			} else {
				// translators: %s is the field's label.
				UM()->form()->add_error( $key, sprintf( __( '%s is required', 'um-user-locations' ), $array['label'] ) );
			}
		}
	}


	/**
	 * Change meta in profile
	 *
	 * @param string $html
	 * @param string $key
	 *
	 * @return string
	 * @throws \Exception
	 */
	function meta_item_user_location_distance_html( $html, $key ) {
		$data = [];
		if ( isset( UM()->builtin()->all_user_fields[ $key ] ) ) {
			$data = UM()->builtin()->all_user_fields[ $key ];
		}

		if ( isset( $data['type'] ) && $data['type'] == 'distance' ) {

			$html = '';

			$location_data = UM()->fields()->get_field( $data['location_source'] );

			$lat_value = UM()->fields()->field_value( $data['location_source'] . '_lat', '', $location_data );
			$lng_value = UM()->fields()->field_value( $data['location_source'] . '_lng', '', $location_data );

			if ( $lat_value == '' || $lng_value == '' ) {
				return $html;
			}

			wp_enqueue_script( 'um-user-location-distance' );

			$current_location = '';
			if ( ! $this->current_distance_visible ) {
				ob_start(); ?>

				<input id="um-user-location-current-denied" type="hidden" value="" />
				<input id="um-user-location-current-lat" type="hidden" value="" />
				<input id="um-user-location-current-lng" type="hidden" value="" />

				<?php $current_location = ob_get_clean();

				$this->current_distance_visible = true;
			}

			$html .= $current_location;

			ob_start(); ?>

			<span class="um-distance-meta">
				<input id="um-user-location-distance-<?php echo esc_attr( $data['location_source'] ) ?>-lat" type="hidden" value="<?php echo esc_attr( $lat_value ) ?>">
				<input id="um-user-location-distance-<?php echo esc_attr( $data['location_source'] ) ?>-lng" type="hidden" value="<?php echo esc_attr( $lng_value ) ?>">

				<span class="um-user-location-distance-calculation-result"
					 data-location_source="<?php echo esc_attr( $data['location_source'] ) ?>"
					 data-distance_unit="<?php echo esc_attr( $data['location_unit'] ) ?>">
					<?php _e( '--', 'um-user-locations' ) ?>
				</span>
			</span>

			<?php $html .= ob_get_clean();
		}

		return $html;
	}



	function add_distance_field( $fields ) {
		$fields[] = 'distance';
		return $fields;
	}

	/**
	 * Do not require a metakey
	 *
	 * @param $array
	 *
	 * @return array
	 */
	function remove_distance_field( $array ) {
		unset( $array[ array_search( 'distance', $array ) ] );
		return $array;
	}


	/**
	 * @param $output
	 * @param $data
	 *
	 * @return string
	 */
	function distance_field_html( $output, $data ) {
		return '';
	}


	/**
	 * Customize distance in profile view
	 *
	 * @param  string $output
	 * @param  array $data
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function view_field_distance( $output, $data ) {

		$location_data = UM()->fields()->get_field( $data['location_source'] );

		$lat_value = UM()->fields()->field_value( $data['location_source'] . '_lat', '', $location_data );
		$lng_value = UM()->fields()->field_value( $data['location_source'] . '_lng', '', $location_data );

		if ( $lat_value == '' || $lng_value == '' ) {
			return $output;
		}

		wp_enqueue_script( 'um-user-location-distance' );

		$current_location = '';
		if ( ! $this->current_distance_visible ) {
			ob_start(); ?>

			<input id="um-user-location-current-denied" type="hidden" value="" />
			<input id="um-user-location-current-lat" type="hidden" value="" />
			<input id="um-user-location-current-lng" type="hidden" value="" />

			<?php $current_location = ob_get_clean();

			$this->current_distance_visible = true;
		}

		$output .= $current_location;

		ob_start(); ?>

		<input id="um-user-location-distance-<?php echo esc_attr( $data['location_source'] ) ?>-lat" type="hidden" value="<?php echo esc_attr( $lat_value ) ?>">
		<input id="um-user-location-distance-<?php echo esc_attr( $data['location_source'] ) ?>-lng" type="hidden" value="<?php echo esc_attr( $lng_value ) ?>">

		<div class="um-user-location-distance-calculation-result"
			 data-location_source="<?php echo esc_attr( $data['location_source'] ) ?>"
			 data-distance_unit="<?php echo esc_attr( $data['location_unit'] ) ?>">
			<?php _e( '--', 'um-user-locations' ) ?>
		</div>

		<?php $output .= ob_get_clean();

		return $output;
	}


	/**
	 *
	 * @param string $key
	 * @param array $args
	 */
	function save_location_fields( $key, $args ) {
		if ( $args['type'] == 'user_location' ) {
			$store = get_option( 'um_map_user_fields', array() );

			$store[] = $key;
			$store = array_unique( $store );
			update_option( 'um_map_user_fields', $store );
		}
	}


	/**
	 *
	 * @param string $key
	 * @param array $args
	 */
	function remove_location_field( $key, $args ) {
		if ( $args['type'] == 'user_location' ) {
			$store = get_option( 'um_map_user_fields', array() );

			unset( $store[ array_search( $key, $store ) ] );

			global $wpdb;

			$metadata = $wpdb->get_results( $wpdb->prepare(
				"SELECT post_id, meta_value
				  FROM {$wpdb->postmeta}
				  WHERE meta_key = '_um_user_location_fields' AND
						meta_value LIKE %s",
				'%' . $key . '%'
			) ,ARRAY_A );

			if ( ! empty( $metadata ) ) {
				foreach ( $metadata as $row ) {
					$fields = maybe_unserialize( $row['meta_value'] );
					$as_string = false;
					if ( is_string( $fields ) ) {
						$fields = array( $fields );
						$as_string = true;
					}

					unset( $fields[ array_search( $key, $fields ) ] );

					if ( $as_string && empty( $fields ) ) {
						update_post_meta( $row['post_id'], '_um_user_location_fields', '' );
					} else {
						update_post_meta( $row['post_id'], '_um_user_location_fields', $fields );
					}
				}
			}

			$store = array_unique( array_values( $store ) );
			update_option( 'um_map_user_fields', $store );
		}
	}


	/**
	 * Set location fields variable
	 */
	function init_variables() {
		$map_fields = get_option( 'um_map_user_fields', array() );

		if ( ! empty( $map_fields ) ) {
			$map_fields = array_map( function( $item ) use ( $map_fields ) {
				$field_title = UM()->fields()->get_field_title( $map_fields[ $item ] );

				if ( ! empty( $field_title ) ) {
					$item = $field_title;
				} else {
					$item = $map_fields[ $item ];
				}
				return $item;
			}, array_flip( $map_fields ) );
		}

		$this->location_fields = $map_fields;
	}


	/**
	 * @param $fields
	 *
	 * @return mixed
	 */
	function add_location_field( $fields ) {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return $fields;
		}

		$fields['user_location'] = array(
			'name' => __( 'User Location', 'um-user-locations' ),
			'col1' => array( '_title', '_metakey', '_help', '_default', '_visibility' ),
			'col2' => array( '_label', '_icon', '_placeholder', '_public', '_roles' ),
			'col3' => array( '_required', '_editable' ),
			'validate' => array(
				'_title' => array(
					'mode' => 'required',
					'error' => __( 'You must provide a title', 'um-user-locations' )
				),
				'_metakey' => array(
					'mode' => 'unique',
				),
			)
		);

		// run to initialize location fields
		$this->init_variables();

		if ( count( $this->location_fields ) ) {

			$fields['distance'] = array(
				'name'      => __( 'Distance', 'um-user-locations' ),
				'col1'      => array( '_title', '_location_source', '_location_unit' ),
				'col2'      => array( '_label', '_public', '_roles' ),
				'validate'  => array(
					'_title'            => array(
						'mode'  => 'required',
						'error' => __( 'You must provide a title', 'um-user-locations' )
					),
					'_location_source'  => array(
						'mode'  => 'required',
						'error' => __( 'You must provide a location source', 'um-user-locations' )
					),
					'_location_unit'  => array(
						'mode'  => 'required',
						'error' => __( 'You must provide a distance unit', 'um-user-locations' )
					),
				)
			);

		}

		return $fields;
	}

	/**
	 * Modal field settings
	 *
	 * @param $val
	 */
	function location_source_field( $val ) {
		?>

		<p>
			<label for="_location_source">
				<?php _e( 'Select a location source', 'um-user-locations' ); ?>
				<?php UM()->tooltip( __( 'Choose the location source for getting the distance for it', 'um-user-locations' ) ); ?>
			</label>
			<select name="_location_source" id="_location_source" style="width: 100%;">
				<?php foreach ( $this->location_fields as $key => $value ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $val ); ?>>
						<?php echo $value; ?>
					</option>
				<?php } ?>
			</select>
		</p>

		<?php
	}


	/**
	 * Modal field settings
	 *
	 * @param $val
	 */
	function location_unit_field( $val ) {
		?>

		<p>
			<label for="_location_unit">
				<?php _e( 'Select a distance unit', 'um-user-locations' ); ?>
				<?php UM()->tooltip( __( 'Miles or kilometers', 'um-user-locations' ) ); ?>
			</label>
			<select name="_location_unit" id="_location_unit" style="width: 100%;">
				<option value="miles" <?php selected( 'miles', $val ); ?>><?php _e( 'Miles', 'um-user-locations' ) ?></option>
				<option value="km" <?php selected( 'km', $val ); ?>><?php _e( 'Kilometers', 'um-user-locations' ) ?></option>
			</select>
		</p>

		<?php
	}


	/**
	 * Enqueue assets on Social Login overlay form
	 */
	function add_location_assets() {
		UM()->User_Locations()->enqueue()->enqueue_gmap();
		wp_enqueue_script('um-user-location-field');
	}

	/**
	 * @param $output
	 * @param $data
	 *
	 * @return string
	 */
	public function location_field_html( $output, $data ) {
		if ( ! array_key_exists( 'metakey', $data ) ) {
			return $output;
		}

		$classes = '';
		if ( array_key_exists( 'classes', $data ) ) {
			$classes = explode( ' ', $data['classes'] );
		}

		$conditional = '';
		if ( ! empty( $data['conditional'] ) ) {
			$conditional = $data['conditional'];
		}

		$metakey     = $data['metakey'];
		$default     = array_key_exists( 'default', $data ) ? $data['default'] : false;
		$placeholder = array_key_exists( 'placeholder', $data ) ? $data['placeholder'] : '';
		$field_name  = $metakey . UM()->form()->form_suffix;
		$field_value = UM()->fields()->field_value( $metakey, $default, $data );
		$lat_value   = UM()->fields()->field_value( $metakey . '_lat', '', $data );
		$lng_value   = UM()->fields()->field_value( $metakey . '_lng', '', $data );
		$url_value   = UM()->fields()->field_value( $metakey . '_url', '', $data );
		$show_map    = apply_filters( 'um_user_locations_field_show_map', true, $data );

		wp_enqueue_script( 'um-user-location-field' );

		ob_start();
		?>
		<div <?php echo UM()->fields()->get_atts( $metakey, $classes, $conditional, $data ); ?>>
			<?php if ( isset( $data['label'] ) ) { ?>
				<?php echo UM()->fields()->field_label( $data['label'], $metakey, $data ); ?>
			<?php } ?>
			<div class="um-field-area">
				<?php if ( ! empty( $data['icon'] ) && isset( UM()->fields()->field_icons ) && 'field' === UM()->fields()->field_icons ) { ?>
					<div class="um-field-icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></div>
				<?php } ?>
				<input class="<?php echo esc_attr( UM()->fields()->get_class( $metakey, $data ) ); ?> um_user_location_g_autocomplete" type="text"
					name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>" data-key="<?php echo esc_attr( $metakey ); ?>" <?php echo esc_html( UM()->fields()->aria_valid_attributes( UM()->fields()->is_error( $metakey ), $field_name ) ); ?>/>
				<a href="javascript:void(0);" class="um_current_user_location"><i class="um-faicon-map-marker" aria-hidden="true"></i></a>
				<input type="hidden" name="<?php echo esc_attr( $metakey . '_lat' . UM()->form()->form_suffix ); ?>" class="um_lat_param" data-key="<?php echo esc_attr( $metakey ); ?>_lat" value="<?php echo esc_attr( $lat_value ); ?>" />
				<input type="hidden" name="<?php echo esc_attr( $metakey . '_lng' . UM()->form()->form_suffix ); ?>" class="um_lng_param" data-key="<?php echo esc_attr( $metakey ); ?>_lng" value="<?php echo esc_attr( $lng_value ); ?>" />
				<input type="hidden" name="<?php echo esc_attr( $metakey . '_url' . UM()->form()->form_suffix ); ?>" class="um_url_param" data-key="<?php echo esc_attr( $metakey ); ?>_url" value="<?php echo esc_attr( $url_value ); ?>" />

				<?php if ( $show_map ) { ?>
					<div class="um_user_location_g_autocomplete_map"></div>
				<?php } ?>
			</div>
		</div>
		<?php
		$output .= ob_get_clean();

		if ( UM()->fields()->is_error( $metakey ) ) {
			$output .= UM()->fields()->field_error( UM()->fields()->show_error( $metakey ), $field_name );
		} elseif ( UM()->fields()->is_notice( $metakey ) ) {
			$output .= UM()->fields()->field_notice( UM()->fields()->show_notice( $metakey ), $field_name );
		}

		return $output;
	}


	/**
	 * Customize instagram photo in profile view
	 * @param  string $output
	 * @param  array $data
	 * @return string
	 */
	public function view_field_user_location( $output, $data ) {
		wp_enqueue_script( 'um-user-location-field' );

		$lat_value = UM()->fields()->field_value( $data['metakey'] . '_lat', '', $data );
		$lng_value = UM()->fields()->field_value( $data['metakey'] . '_lng', '', $data );
		$url_value = UM()->fields()->field_value( $data['metakey'] . '_url', '', $data );

		if ( '' !== $lat_value && '' !== $lng_value ) {
			if ( '' !== $url_value ) {
				$output = '<a href="' . esc_url( $url_value ) . '" target="_blank">' . $output . '</a>';
			}

			$show_map = apply_filters( 'um_user_locations_field_show_map', true, $data );

			$output = '<div class="um-user-locations-map-field-text">' . $output . '</div>';
			if ( $show_map ) {
				$output .= '<div class="um-user-locations-map-field-view" data-lat="' . esc_attr( $lat_value ) . '" data-lng="' . esc_attr( $lng_value ) . '"></div>';
			}
		}

		return $output;
	}


}
