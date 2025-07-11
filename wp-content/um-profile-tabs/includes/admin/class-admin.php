<?php
namespace um_ext\um_profile_tabs\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_profile_tabs\admin\Admin' ) ) {

	/**
	 * Class Admin
	 * @package um_ext\um_profile_tabs\admin
	 */
	class Admin {

		/**
		 * Admin constructor.
		 */
		public function __construct() {
			add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ) );

			add_action( 'admin_menu', [ $this, 'create_admin_submenu' ], 1001 );

			add_action( 'load-post.php', [ &$this, 'add_metabox' ], 9 );
			add_action( 'load-post-new.php', [ &$this, 'add_metabox' ], 9 );

			add_filter( 'um_is_ultimatememeber_admin_screen', [ &$this, 'is_um_screen' ], 10, 1 );
			add_filter( 'um_render_sortable_items_item_html', [ &$this, 'customize_pre_defined_titles_html' ], 10, 3 );

			add_filter( 'um_get_outdated_icons_result', array( &$this, 'check_site_health' ), 10, 2 );
		}

		public function admin_enqueue_scripts() {
			$suffix = UM()->admin()->enqueue()::get_suffix();
			wp_register_script( 'um_profile_tabs_admin', um_profile_tabs_url . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'wp-url' ), um_profile_tabs_version, true );
			wp_enqueue_script( 'um_profile_tabs_admin' );
		}

		/**
		 * Additional Settings for Profile Tabs extension
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		public function extend_settings( $settings ) {
			$settings['licenses']['fields'][] = array(
				'id'        => 'um_profile_tabs_license_key',
				'label'     => __( 'Profile tabs License Key', 'um-profile-tabs' ),
				'item_name' => 'Profile tabs',
				'author'    => 'Ultimate Member',
				'version'   => um_profile_tabs_version,
			);

			$tabs = UM()->profile()->tabs();

			$tabs_items     = array();
			$tabs_condition = array();
			foreach ( $tabs as $id => $tab ) {
				if ( ! empty( $tab['hidden'] ) ) {
					continue;
				}

				if ( isset( $tab['name'] ) ) {
					$tabs_items[ $id ] = $tab['name'];
					$tabs_condition[]  = 'profile_tab_' . $id;
				}

				foreach ( $settings['appearance']['sections']['profile_menu']['fields'] as $k => &$data ) {
					if ( 'profile_tab_' . $id === $data['id'] ) {
						$data['data']['fill_profile_tabs_order'] = $id;
						break;
					}
				}
			}

			$settings['appearance']['sections']['profile_menu']['fields'][] = array(
				'id'          => 'profile_tabs_order',
				'type'        => 'sortable_items',
				'label'       => __( 'Profile Tabs Order', 'um-profile-tabs' ),
				'items'       => $tabs_items,
				'conditional' => array( implode( '|', $tabs_condition ), '~', 1 ),
				'size'        => 'small',
				'tooltip'     => __( 'Pay an attention that default tab ignore the order and will be displayed the first', 'um-profile-tabs' ),
			);

			return $settings;
		}

		/**
		 * Add UM submenu for Profile Tabs
		 */
		public function create_admin_submenu() {
			add_submenu_page( 'ultimatemember', __( 'Profile Tabs', 'um-profile-tabs' ), __( 'Profile Tabs', 'um-profile-tabs' ), 'manage_options', 'edit.php?post_type=um_profile_tabs' );
		}

		/**
		 * Extends UM admin pages for enqueue scripts
		 *
		 * @param $is_um
		 *
		 * @return bool
		 */
		public function is_um_screen( $is_um ) {
			global $current_screen;
			if ( ! empty( $current_screen ) && false !== strpos( $current_screen->id, 'um_profile_tabs' ) ) {
				$is_um = true;
			}

			return $is_um;
		}

		/**
		 * Render the settings field for changing profile tabs order
		 *
		 * @param string $content
		 * @param string $tab_id
		 * @param array $field_data
		 *
		 * @return string
		 */
		public function customize_pre_defined_titles_html( $content, $tab_id, $field_data ) {
			if ( 'profile_tabs_order' === $field_data['id'] ) {
				$tabs = UM()->profile()->tabs();

				if ( empty( $tabs[ $tab_id ] ) ) {
					return $content;
				}

				if ( ! empty( $tabs[ $tab_id ]['is_custom_added'] ) ) {
					return $content;
				}

				$custom_titles = UM()->options()->get( 'tabs_custom_titles' );

				$value = $content;
				if ( ! empty( $custom_titles[ $tab_id ] ) ) {
					$value = stripslashes( $custom_titles[ $tab_id ] );
				}

				$content = '<input type="text" name="um_options[tabs_custom_titles][' . $tab_id . ']" value="' . esc_attr( $value ) . '"/>';
			}

			return $content;
		}

		/**
		 * Add metaboxes with options to Add/Edit Profile Tab screen
		 */
		public function add_metabox() {
			global $current_screen;

			if ( 'um_profile_tabs' === $current_screen->id ) {
				add_action( 'add_meta_boxes', [ &$this, 'profile_tab_metaboxes' ], 1 );
				add_action( 'save_post', [ &$this, 'save_meta_data' ], 10, 3 );


				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
			}
		}

		/**
		 * Add metaboxes
		 */
		public function profile_tab_metaboxes() {
			// don't show metaboxes for translations
			if ( UM()->external_integrations()->is_wpml_active() ) {
				global $post, $sitepress;

				$tab_id = $sitepress->get_object_id( $post->ID, 'um_profile_tabs', true, $sitepress->get_default_language() );
				if ( $tab_id && $tab_id !== $post->ID ) {
					return;
				}
			}

			add_meta_box(
				'um-admin-custom-profile-tab/access{' . um_profile_tabs_path . '}',
				__( 'Display Settings', 'um-profile-tabs' ),
				array( UM()->metabox(), 'load_metabox_custom' ),
				'um_profile_tabs',
				'side'
			);

			add_meta_box(
				'um-admin-custom-profile-tab/icon{' . um_profile_tabs_path . '}',
				__( 'Customize this tab', 'um-profile-tabs' ),
				array( UM()->metabox(), 'load_metabox_custom' ),
				'um_profile_tabs',
				'side'
			);

			add_meta_box(
				'um-admin-custom-profile-tab/um-form{' . um_profile_tabs_path . '}',
				__( 'Pre-defined content', 'um-profile-tabs' ),
				array( UM()->metabox(), 'load_metabox_custom' ),
				'um_profile_tabs',
				'side'
			);
		}

		/**
		 * Save Profile Tab metabox settings
		 *
		 * @param int $post_id
		 * @param \WP_Post $post
		 * @param bool $update
		 */
		public function save_meta_data( $post_id, $post, $update ) {
			//make this handler only on product form submit
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'update-post_' . $post_id ) ) {
				return;
			}

			if ( empty( $post->post_type ) || 'um_profile_tabs' !== $post->post_type ) {
				return;
			}

			if ( UM()->external_integrations()->is_wpml_active() ) {
				global $sitepress;

				$tab_id = $sitepress->get_object_id( $post_id, 'um_profile_tabs', true, $sitepress->get_default_language() );
				if ( $tab_id && $tab_id !== $post_id ) {
					return;
				}
			}

			if ( empty( $_POST['um_profile_tab'] ) ) {
				return;
			}

			if ( ! empty( $_POST['um_profile_tab']['_can_have_this_tab_roles'] ) ) {
				update_post_meta( $post_id, '_can_have_this_tab_roles', $_POST['um_profile_tab']['_can_have_this_tab_roles'] );
			} else {
				update_post_meta( $post_id, '_can_have_this_tab_roles', array() );
			}

			if ( ! empty( $_POST['um_profile_tab']['_can_have_this_tab_forms'] ) ) {
				update_post_meta( $post_id, '_can_have_this_tab_forms', $_POST['um_profile_tab']['_can_have_this_tab_forms'] );
			} else {
				update_post_meta( $post_id, '_can_have_this_tab_forms', array() );
			}

			$icon = '';
			if ( isset( $_POST['um_profile_tab']['_icon'] ) ) {
				$icon = sanitize_text_field( $_POST['um_profile_tab']['_icon'] );
			}
			update_post_meta( $post_id, 'um_icon', $icon );

			$link_type = 0; // means internal
			if ( isset( $_POST['um_profile_tab']['_link_type'] ) ) {
				$link_type = absint( $_POST['um_profile_tab']['_link_type'] );
			}
			update_post_meta( $post_id, 'um_link_type', $link_type );

			$slug = preg_replace( '/[^a-zA-Z0-9-]/', '', $post->post_name );
			if ( 0 === $link_type ) {
				update_post_meta( $post_id, 'um_tab_link', '' );

				if ( '' !== sanitize_title( $_POST['um_profile_tab']['_tab_slug'] ) ) {
					$slug = preg_replace( '/[^a-zA-Z0-9-]/', '', $_POST['um_profile_tab']['_tab_slug'] );
				}
			} elseif ( 1 === $link_type ) {
				$tab_link = '';
				if ( isset( $_POST['um_profile_tab']['_tab_link'] ) ) {
					$tab_link = esc_url_raw( $_POST['um_profile_tab']['_tab_link'] );
				}
				update_post_meta( $post_id, 'um_tab_link', $tab_link );
			}

			// sanitize slug, avoid not latin|numeric symbols in slugs
			if ( '' !== sanitize_title( $slug ) ) {
				$tab_slug = sanitize_title( $slug );
			} else {
				// otherwise use autoincrement and slug generator
				$auto_increment = UM()->options()->get( 'custom_profiletab_increment' );
				$auto_increment = ! empty( $auto_increment ) ? $auto_increment : 1;
				$tab_slug       = "custom_profiletab_{$auto_increment}";
			}

			$old_slug = get_post_meta( $post_id, 'um_tab_slug', true );

			// slug is unique for all languages, use 1 for all langs
			// UM options UM Appearances > Profile Tabs are based on these slugs
			// update autoincrement option if slug generator has been used
			// make these action only on create profile tab post or if there isn't post meta
			if ( $old_slug !== $tab_slug ) {
				if ( UM()->external_integrations()->is_wpml_active() ) {
					global $sitepress;

					$tab_id = $sitepress->get_object_id( $post_id, 'um_profile_tabs', true, $sitepress->get_default_language() );
					if ( $tab_id && $tab_id === $post_id ) {
						update_post_meta( $post_id, 'um_tab_slug', $tab_slug );

						if ( isset( $auto_increment ) ) {
							$auto_increment++;
							UM()->options()->update( 'custom_profiletab_increment', $auto_increment );
						}

						// show new profile tab by default - update UM Appearances > Profile Tabs settings
						if ( UM()->options()->get( 'profile_tab_' . $tab_slug ) === '' ) {
							UM()->options()->update( 'profile_tab_' . $tab_slug, '1' );
							UM()->options()->update( 'profile_tab_' . $tab_slug . '_privacy', '0' );
						}
					}
				} else {
					update_post_meta( $post_id, 'um_tab_slug', $tab_slug );

					if ( isset( $auto_increment ) ) {
						$auto_increment++;
						UM()->options()->update( 'custom_profiletab_increment', $auto_increment );
					}

					// show new profile tab by default - update UM Appearances > Profile Tabs settings
					if ( UM()->options()->get( 'profile_tab_' . $tab_slug ) === '' ) {
						UM()->options()->update( 'profile_tab_' . $tab_slug, '1' );
						UM()->options()->update( 'profile_tab_' . $tab_slug . '_privacy', '0' );
					}
				}
			}

			$form = '';
			if ( isset( $_POST['um_profile_tab']['_um_form'] ) ) {
				$form = absint( $_POST['um_profile_tab']['_um_form'] );
			}
			update_post_meta( $post_id, 'um_form', $form );
		}

		public function check_site_health( $result, $old_icons ) {
			$profile_tabs = get_posts(
				array(
					'post_type'      => 'um_profile_tabs',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			$profile_tabs_count = 0;
			$break_profile_tabs = array();
			if ( ! empty( $profile_tabs ) ) {
				foreach ( $profile_tabs as $profile_tab_id ) {
					$icon = get_post_meta( $profile_tab_id, 'um_icon', true );
					if ( empty( $icon ) ) {
						continue;
					}
					if ( in_array( $icon, $old_icons, true ) ) {
						$break_profile_tabs[] = array(
							'id'    => $profile_tab_id,
							'title' => get_the_title( $profile_tab_id ),
							'link'  => get_edit_post_link( $profile_tab_id ),
						);
						$profile_tabs_count++;
					}
				}
			}

			if ( 0 < $profile_tabs_count ) {
				$result['description'] .= sprintf(
					'<p>%s</p>',
					__( 'Your icons in the Ultimate Member Profile Tabs are out of date.', 'um-profile-tabs' )
				);

				if ( ! empty( $break_profile_tabs ) ) {
					$result['description'] .= sprintf(
						'<p>%s',
						__( 'Related to Ultimate Member Profile Tabs: ', 'um-profile-tabs' )
					);

					$profile_tab_links = array();
					foreach ( $break_profile_tabs as $break_profile_tab ) {
						$profile_tab_links[] = sprintf(
							'<a href="%s" target="_blank">%s (#ID: %s)</a>',
							esc_url( $break_profile_tab['link'] ),
							esc_html( $break_profile_tab['title'] ),
							esc_html( $break_profile_tab['id'] )
						);
					}

					$result['description'] .= sprintf(
						'%s</p><hr />',
						implode( ', ', $profile_tab_links )
					);
				}

				$result['actions'] .= sprintf(
					'<p><a href="%s">%s</a></p>',
					admin_url( 'edit.php?post_type=um_profile_tabs' ),
					esc_html__( 'Edit profile tabs and update', 'um-profile-tabs' )
				);
			}

			return $result;
		}
	}
}
