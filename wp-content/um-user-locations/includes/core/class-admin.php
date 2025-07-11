<?php
namespace um_ext\um_user_locations\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Admin
 *
 * @package um_ext\um_user_locations\core
 */
class Admin {


	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_filter( 'um_settings_structure', array( &$this, 'extends_settings' ) );
		add_filter( 'um_admin_role_metaboxes', array( &$this, 'add_role_metabox' ), 10, 1 );

		add_action( 'load-post.php', array( &$this, 'add_metabox' ), 9 );
		add_action( 'load-post-new.php', array( &$this, 'add_metabox' ), 9 );

		add_action( 'um_admin_create_notices', array( &$this, 'add_admin_notice' ) );

		add_filter( 'um_override_templates_scan_files', array( &$this, 'um_user_location_extend_scan_files' ), 10, 1 );
		add_filter( 'um_override_templates_get_template_path__um-user-locations', array( &$this, 'um_user_location_get_path_template' ), 10, 2 );
	}


	/**
	 * Init the metaboxes
	 */
	public function add_metabox() {
		global $current_screen;

		if ( 'um_directory' === $current_screen->id ) {
			add_action( 'add_meta_boxes', array( &$this, 'add_metabox_directory' ), 1 );
			//add_action( 'save_post', array( &$this, 'save_metabox_directory' ), 10, 2 );
		}
	}


	/**
	 *
	 */
	public function add_metabox_directory() {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );
		if ( empty( $key ) ) {
			return;
		}

		add_meta_box( 'um-admin-form-user-locations{' . um_user_locations_path . '}', __( 'User Locations', 'um-user-locations' ), array( UM()->metabox(), 'load_metabox_directory' ), 'um_directory', 'normal', 'default' );
	}


	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public function extends_settings( $settings ) {
		$settings['licenses']['fields'][] = array(
			'id'        => 'um_user_locations_license_key',
			'label'     => __( 'User Locations License Key', 'um-user-locations' ),
			'item_name' => 'User Locations',
			'author'    => 'Ultimate Member',
			'version'   => um_user_locations_version,
		);

		$options = UM()->User_Locations()->locales;

		$settings['extensions']['sections']['user_locations'] = array(
			'title'  => __( 'User Locations', 'um-user-locations' ),
			'fields' => array(
				array(
					'id'    => 'um_google_maps_js_api_key',
					'type'  => 'text',
					'label' => __( 'Google Maps Javascript API Key', 'um-user-locations' ),
					'size'  => 'medium',
				),
				array(
					'id'    => 'um_google_lang_as_default',
					'type'  => 'checkbox',
					'label' => __( 'Use site\'s locale as language for Google Maps', 'um-user-locations' ),
				),
				array(
					'id'          => 'um_google_lang',
					'type'        => 'select',
					'label'       => __( 'Google Maps language', 'um-user-locations' ),
					'size'        => 'small',
					'options'     => $options,
					'conditional' => array( 'um_google_lang_as_default', '=', 0 ),
				),
				array(
					'id'    => 'user_location_map_height',
					'type'  => 'number',
					'label' => __( 'Map height (px)', 'um-user-locations' ),
					'size'  => 'small',
				),
				array(
					'id'      => 'um_google_maps_starting_zoom',
					'type'    => 'number',
					'label'   => __( 'User Profile starting map zoom level', 'um-user-locations' ),
					'tooltip' => __( 'Pick a starting zoom level for the map on the user profile page. Eg: 12', 'um-user-locations' ),
					'size'    => 'small',
				),
				array(
					'id'      => 'um_google_maps_starting_coord_lat',
					'type'    => 'text',
					'label'   => __( 'User Profile starting address latitude', 'um-user-locations' ),
					'tooltip' => __( 'Pick a starting position for the map on the user profile page', 'um-user-locations' ),
					'size'    => 'small',
				),
				array(
					'id'      => 'um_google_maps_starting_coord_lng',
					'type'    => 'text',
					'label'   => __( 'User Profile starting address longitude', 'um-user-locations' ),
					'tooltip' => __( 'Pick a starting position for the map on the user profile page', 'um-user-locations' ),
					'size'    => 'small',
				),
			),
		);

		return $settings;
	}


	/**
	 *
	 */
	public function add_admin_notice() {
		$key = UM()->options()->get( 'um_google_maps_js_api_key' );

		if ( ! empty( $key ) ) {
			return;
		}

		ob_start();
		?>
		<?php // translators: %s is the User Locations extension name. ?>
		<p><?php printf( __( '%s is active on your site. However you need to fill in your <strong>Google Maps API key</strong> before the extension can be used.', 'um-user-locations' ), um_user_locations_extension ); ?></p>

		<p>
			<a href="<?php echo admin_url( 'admin.php?page=um_options&tab=extensions&section=user_locations' ) ?>" class="button button-primary"><?php esc_html_e( 'I already have the API key', 'um-user-locations' ) ?></a>&nbsp;
			<a href="https://cloud.google.com/maps-platform/" class="button-secondary" target="_blank"><?php esc_html_e( 'Generate your API key', 'um-user-locations' ) ?></a>
		</p>

		<?php
		$message = ob_get_clean();

		UM()->admin()->notices()->add_notice(
			'um_user_locations_notice',
			array(
				'class'       => 'updated',
				'message'     => $message,
				'dismissible' => true,
			),
			10
		);
	}


	/**
	 * @param array $roles_metaboxes
	 *
	 * @return array
	 */
	public function add_role_metabox( $roles_metaboxes ) {
		$roles_metaboxes[] = array(
			'id'       => 'um-admin-form-locations{' . um_user_locations_path . '}',
			'title'    => __( 'User Locations', 'um-user-locations' ),
			'callback' => array( UM()->metabox(), 'load_metabox_role' ),
			'screen'   => 'um_role_meta',
			'context'  => 'normal',
			'priority' => 'default',
		);

		return $roles_metaboxes;
	}


	/**
	 * Scan templates from extension
	 *
	 * @param $scan_files
	 *
	 * @return array
	 */
	public function um_user_location_extend_scan_files( $scan_files ) {
		$extension_files['um-user-locations'] = UM()->admin_settings()->scan_template_files( um_user_locations_path . '/templates/' );
		$scan_files                           = array_merge( $scan_files, $extension_files );

		return $scan_files;
	}


	/**
	 * Get template paths
	 *
	 * @param $located
	 * @param $file
	 *
	 * @return array
	 */
	public function um_user_location_get_path_template( $located, $file ) {
		if ( file_exists( get_stylesheet_directory() . '/ultimate-member/um-user-locations/' . $file ) ) {
			$located = array(
				'theme' => get_stylesheet_directory() . '/ultimate-member/um-user-locations/' . $file,
				'core'  => um_user_locations_path . 'templates/' . $file,
			);
		}

		return $located;
	}
}
