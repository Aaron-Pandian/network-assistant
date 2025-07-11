<?php
namespace um_ext\um_profile_tabs\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Profile
 *
 * @package um_ext\um_profile_tabs\core
 */
class Profile {

	/**
	 * @var array
	 */
	protected $tabs = array();

	private $inited = false;

	/**
	 * Profile constructor.
	 */
	public function __construct() {
		// init the custom tabs until the using
		add_filter( 'um_profile_tabs', array( $this, 'predefine_tabs' ), 1, 1 );
		add_filter( 'um_profile_tabs', array( &$this, 'add_tabs' ), 9999, 1 );

		add_filter( 'um_user_profile_tabs', array( &$this, 'profile_tab_display_settings' ), 10, 1 );

		add_filter( 'um_user_profile_tabs', array( &$this, 'profile_tabs_order' ), 9999999, 1 );
		add_filter( 'um_user_profile_tabs', array( &$this, 'profile_tabs_titles' ), 9999999, 1 );

		add_action( 'um_user_after_updating_profile', array( $this, 'redirect_to_current_tab' ), 100, 3 );
		add_filter( 'um_edit_profile_cancel_uri', array( $this, 'redirect_to_current_tab_after_cancel' ), 10, 1 );

		// cf7 support
		add_filter( 'wpcf7_form_hidden_fields', array( $this, 'add_profile_id_on_cf7' ) );
		add_filter( 'wpcf7_mail_components', array( $this, 'change_email_to_profile_owner_email' ), 10, 3 );
		add_filter( 'wpcf7_special_mail_tags', array( $this, 'wpcf7_um_related_smt' ), 10, 4 );
	}

	/**
	 * @param $tab
	 *
	 * @return mixed
	 */
	public function get_slug( $tab ) {
		$slug = get_post_meta( $tab->ID, 'um_tab_slug', true );
		if ( UM()->external_integrations()->is_wpml_active() ) {
			global $sitepress;

			$tab_id = $sitepress->get_object_id( $tab->ID, 'um_profile_tabs', true, $sitepress->get_default_language() );
			if ( $tab_id && $tab_id !== $tab->ID ) {
				$slug = get_post_meta( $tab_id, 'um_tab_slug', true );
			}
		}

		return $slug;
	}

	/**
	 * Init tabs before the first using UM()->profile()->tabs()
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function predefine_tabs( $tabs ) {
		if ( $this->inited ) {
			return $tabs;
		}

		$profile_tabs = get_posts(
			array(
				'post_type'      => 'um_profile_tabs',
				'orderby'        => 'menu_order',
				'posts_per_page' => -1,
			)
		);

		foreach ( $profile_tabs as $tab ) {

			$slug = $this->get_slug( $tab );
			if ( isset( $this->tabs[ $slug ] ) ) {
				continue;
			}

			$icon = get_post_meta( $tab->ID, 'um_icon', true );
			if ( ! $icon ) {
				$icon = 'fas fa-check';
			}

			$form = get_post_meta( $tab->ID, 'um_form', true );

			$link_type = (int) get_post_meta( $tab->ID, 'um_link_type', true );
			$tab_link  = '';
			if ( ! empty( $link_type ) ) {
				$tab_link = get_post_meta( $tab->ID, 'um_tab_link', true );
			}

			if ( UM()->external_integrations()->is_wpml_active() ) {
				global $sitepress;

				$tab_id = $sitepress->get_object_id( $tab->ID, 'um_profile_tabs', true, $sitepress->get_current_language() );
				if ( $tab_id && $tab_id !== $tab->ID ) {
					$tab = get_post( $tab_id );
				}
			}

			$tab = array(
				'tabid'     => $slug,
				'id'        => $tab->ID,
				'icon'      => $icon,
				'title'     => $tab->post_title,
				'content'   => $tab->post_content,
				'form'      => $form,
				'link_type' => $link_type,
				'tab_link'  => $tab_link,
			);

			$this->tabs[ $slug ] = $tab;

			// Show content
			add_action(
				'um_profile_content_' . $tab['tabid'],
				function( $args ) use ( $tab ) {
					$userdata = get_userdata( um_profile_id() );

					$tab_content = wpautop( $tab['content'] );

					$placeholders = array(
						'{profile_id}'             => um_profile_id(),
						'{first_name}'             => $userdata->first_name,
						'{last_name}'              => $userdata->last_name,
						'{user_email}'             => $userdata->user_email,
						'{display_name}'           => $userdata->display_name,
					);
					$tab_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $tab_content );

					if ( ! empty( $tab['form'] ) ) {
						add_filter( 'um_force_shortcode_render', array( &$this, 'force_break_form_shortcode' ), 10, 2 );
					}

					// Fix conflict that may appear if the tab contains Elementor template
					if ( class_exists( '\Elementor\Plugin' ) ) {
						\Elementor\Plugin::instance()->frontend->remove_content_filter();
						echo apply_filters( 'the_content', $tab_content );
						\Elementor\Plugin::instance()->frontend->add_content_filter();
					} else {
						echo apply_filters( 'the_content', $tab_content );
					}

					if ( ! empty( $tab['form'] ) ) {
						remove_filter( 'um_force_shortcode_render', array( &$this, 'force_break_form_shortcode' ), 10, 2 );
					}

					echo '<div class="um-clear"></div>';
					echo $this->um_custom_tab_form( $tab['tabid'], $tab['form'] );
				}
			);

			add_filter(
				"um_profile_menu_link_{$tab['tabid']}",
				function( $nav_link ) use ( $tab_link ) {
					if ( ! empty( $tab_link ) ) {
						$nav_link = $tab_link;
					}

					return $nav_link;
				}
			);

			add_filter(
				"um_profile_menu_link_{$tab['tabid']}_attrs",
				function( $profile_nav_attrs ) use ( $tab_link ) {
					if ( ! empty( $tab_link ) ) {
						$profile_nav_attrs = 'target="_blank"';
					}

					return $profile_nav_attrs;
				}
			);
		}

		$this->inited = true;

		return $tabs;
	}

	public function force_break_form_shortcode( $content, $args ) {
		if ( ! empty( $args['form_id'] ) ) {
			$post_obj = get_post( $args['form_id'] );
			if ( ! empty( $post_obj ) && 'um_form' === $post_obj->post_type ) {
				return '';
			}
		}

		return $content;
	}

	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_tabs( $tabs ) {
		foreach ( $this->tabs as $tab ) {
			$tabs[ $tab['tabid'] ] = array(
				'ID'              => $tab['id'],
				'name'            => $tab['title'],
				'icon'            => $tab['icon'],
				'is_custom_added' => true, // it's data for wp-admin Profile Tabs order to make not editable title
			);
		}
		return $tabs;
	}

	/**
	 * Check access for the tabs
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function profile_tab_display_settings( $tabs ) {
		foreach ( $this->tabs as $tab ) {
			if ( empty( $tabs[ $tab['tabid'] ] ) ) {
				continue;
			}

			$tab_id = $tab['id'];
			if ( UM()->external_integrations()->is_wpml_active() ) {
				global $sitepress;

				$default_lang_tab_id = $sitepress->get_object_id( $tab_id, 'um_profile_tabs', true, $sitepress->get_default_language() );
				if ( $default_lang_tab_id && $default_lang_tab_id !== $tab_id ) {
					$tab_id = $default_lang_tab_id;
				}
			}

			$forms_ids = get_post_meta( $tab_id, '_can_have_this_tab_forms', true );
			// check by the form ID
			if ( ! empty( $forms_ids ) && isset( UM()->shortcodes()->form_id ) && ! in_array( UM()->shortcodes()->form_id, $forms_ids ) ) {
				unset( $tabs[ $tab['tabid'] ] );
				continue;
			}
			// check by the profile owner role
			if ( ! $this->can_have_tab( $tab_id ) ) {
				unset( $tabs[ $tab['tabid'] ] );
				continue;
			}
		}

		return $tabs;
	}

	/**
	 * Check if user has the current tab by role
	 *
	 * @param string $tab_id
	 * @param int|bool|null $profile_id
	 *
	 * @return bool
	 */
	public function can_have_tab( $tab_id, $profile_id = null ) {
		if ( null === $profile_id ) {
			$profile_id = um_profile_id();
		}

		$can_have = get_post_meta( $tab_id, '_can_have_this_tab_roles', true );
		if ( empty( $can_have ) ) {
			return true;
		}

		$current_user_roles = UM()->roles()->get_all_user_roles( $profile_id );

		if ( ! is_array( $current_user_roles ) ) {
			$current_user_roles = array();
		}

		if ( array_intersect( $current_user_roles, $can_have ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Change profile tabs order
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function profile_tabs_order( $tabs ) {
		$custom_order = UM()->options()->get( 'profile_tabs_order' );

		if ( empty( $custom_order ) ) {
			return $tabs;
		}

		$items_ordered = explode( ',', $custom_order );

		if ( ! is_array( $items_ordered ) ) {
			return $tabs;
		}

		$items_ordered = array_flip( $items_ordered );

		$invisible_tabs = array();
		foreach ( $tabs as $k => $data ) {
			if ( ! array_key_exists( $k, $items_ordered ) ) {
				$invisible_tabs[ $k ] = $data;
				unset( $tabs[ $k ] );
			}
		}

		uksort(
			$tabs,
			function( $a, $b ) use ( $items_ordered ) {
				if ( ! isset( $items_ordered[ $a ] ) || ! isset( $items_ordered[ $b ] ) ) {
					return -1;
				}
				if ( $items_ordered[ $a ] === $items_ordered[ $b ] ) {
					return 0;
				}
				return ( $items_ordered[ $a ] < $items_ordered[ $b ] ) ? -1 : 1;
			}
		);

		$tabs += $invisible_tabs;
		return $tabs;
	}

	/**
	 * Customize profile tabs names
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function profile_tabs_titles( $tabs ) {
		$custom_titles = UM()->options()->get( 'tabs_custom_titles' );

		if ( empty( $custom_titles ) ) {
			return $tabs;
		}

		foreach ( $tabs as $tab_id => &$tab_data ) {
			if ( ! empty( $custom_titles[ $tab_id ] ) ) {
				$tab_data['name'] = stripslashes( $custom_titles[ $tab_id ] );
			}
		}

		return $tabs;
	}

	/**
	 * Generate content for custom tabs
	 *
	 * @param  string   $tab_id
	 * @param  int|null $form
	 *
	 * @return string
	 */
	public function um_custom_tab_form( $tab_id, $form = null ) {
		if ( empty( $form ) ) {
			return '';
		}

		$tab         = $tab_id;
		$edit_action = 'edit_' . $tab;
		$profile_url = um_user_profile_url( um_profile_id() );

		$edit_url = add_query_arg( array( 'profiletab' => $tab, 'um_action' => $edit_action ), $profile_url );
		$tab_url  = add_query_arg( array( 'profiletab' => $tab ), $profile_url );

		$edit_mode = false;
		if ( isset( $_GET['um_action'] ) && $_GET['um_action'] === $edit_action ) {
			if ( UM()->roles()->um_current_user_can( 'edit', um_profile_id() ) ) {
				$edit_mode = true;
			}
		}

		// save profile settings
		$set_id   = UM()->fields()->set_id;
		$set_mode = UM()->fields()->set_mode;
		$editing  = UM()->fields()->editing;
		$viewing  = UM()->fields()->viewing;

		// set profile settings
		$form_id                 = absint( $form );
		UM()->fields()->set_id   = $form_id;
		UM()->fields()->set_mode = get_post_meta( $form_id, '_um_mode', true );
		UM()->fields()->editing  = $edit_mode;
		UM()->fields()->viewing  = ! $edit_mode;

		$contents = '';
		ob_start();

		if ( $edit_mode ) { ?>
			<form method="post" action="">
			<?php
		}

		$args['form_id']          = absint( $form );
		$args['mode']             = 'profile';
		$args['primary_btn_word'] = __( 'Update', 'um-profile-tabs' );

		UM()->fields()->global_args = $args;

		/** This action is documented in includes/core/um-actions-profile.php */
		do_action( 'um_before_form', $args );
		/** This action is documented in includes/core/um-actions-profile.php */
		do_action( 'um_before_profile_fields', $args );
		/** This action is documented in includes/core/um-actions-profile.php */
		do_action( 'um_main_profile_fields', $args );
		/** This action is documented in includes/core/um-actions-profile.php */
		do_action( 'um_after_form_fields', $args );
		/** This action is documented in includes/core/um-actions-profile.php */
		do_action( 'um_after_profile_fields', $args );

		if ( $edit_mode ) {
			?>
				<input type="hidden" name="redirect_tab" value="<?php echo esc_url( $tab_url ); ?>'"/>
			</form>
		<?php } elseif ( UM()->roles()->um_current_user_can( 'edit', um_profile_id() ) ) { ?>

			<a href="<?php echo esc_url( $edit_url ); ?>" class="um-modal-btn"><i class="fas fa-pencil"></i> <?php esc_html_e( 'Edit', 'um-profile-tabs' ); ?></a>

			<?php
		}
		$contents .= ob_get_clean();

		// restore default profile settings
		UM()->fields()->set_id   = $set_id;
		UM()->fields()->set_mode = $set_mode;
		UM()->fields()->editing  = $editing;
		UM()->fields()->viewing  = $viewing;

		return $contents;
	}

	/**
	 * Redirect to current tab after form update
	 *
	 * @param int $user_id
	 * @param array $args
	 * @param array $to_update
	 */
	public function redirect_to_current_tab( $to_update, $user_id, $args ) {
		if ( isset( $args['submitted']['redirect_tab'] ) ) {
			um_safe_redirect( $args['submitted']['redirect_tab'] );
		}
	}

	/**
	 * Redirect to current tab after cancel form update
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function redirect_to_current_tab_after_cancel( $url ) {
		if ( isset( $_GET['profiletab'], $_GET['um_action'] ) && 'main' !== $_GET['profiletab'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$url = add_query_arg( array( 'profiletab' => $_GET['profiletab'] ), um_user_profile_url( um_profile_id() ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		return $url;
	}

	/**
	 * Add profile id on cf7 form
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function add_profile_id_on_cf7( $fields ) {
		if ( um_is_core_page( 'user' ) ) {
			$fields['_wpcf7_um_profile_id'] = um_profile_id();
		}

		return $fields;
	}

	/**
	 * @param $args
	 * @param $contact_form
	 * @param $class
	 *
	 * @return mixed
	 */
	public function change_email_to_profile_owner_email( $args, $contact_form, $class ) {
		if ( class_exists( '\WPCF7_Submission' ) ) {
			$submission = \WPCF7_Submission::get_instance();
			$page       = $submission->get_meta( 'container_post_id' );

			// getting User Profile predefined page ID, use WPML for getting proper ID
			$page_id = UM()->options()->get( 'core_user' );
			if ( UM()->external_integrations()->is_wpml_active() ) {
				global $sitepress;

				$current_lang_page_id = $sitepress->get_object_id( $page_id, 'page', true, $sitepress->get_current_language() );
				if ( $current_lang_page_id && $current_lang_page_id !== $page_id ) {
					$page_id = $current_lang_page_id;
				}
			}

			if ( (int) $page_id === (int) $page ) {
				if ( ! empty( $_REQUEST['_wpcf7_um_profile_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$user = get_user_by( 'ID', absint( $_REQUEST['_wpcf7_um_profile_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					if ( ! is_wp_error( $user ) && isset( $user->user_email ) && is_email( $user->user_email ) ) {
						$args['recipient'] = $user->user_email;
					}
				}
			}
		}

		return $args;
	}


	/**
	 * Returns output string of a special mail-tag.
	 *
	 * @link https://contactform7.com/special-mail-tags/
	 * @see  wp-content/plugins/contact-form-7/includes/special-mail-tags.php
	 *
	 * @since 1.1.3
	 *
	 * @param string        $output   The string to be output.
	 * @param string        $name     The tag name of the special mail-tag.
	 * @param bool          $html     Whether the mail-tag is used in an HTML content.
	 * @param WPCF7_MailTag $mail_tag An object representation of the mail-tag.
	 * @return string Output of the given special mail-tag.
	 */
	public function wpcf7_um_related_smt( $output, $name, $html, $mail_tag = null ) {
		if ( ! $mail_tag instanceof WPCF7_MailTag ) {
			wpcf7_doing_it_wrong(
				sprintf( '%s()', __FUNCTION__ ),
				__( 'The fourth parameter ($mail_tag) must be an instance of the WPCF7_MailTag class.', 'contact-form-7' ),
				'5.2.2'
			);
		}

		if ( ! str_starts_with( $name, '_um_' ) ) {
			return $output;
		}

		$submission = \WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return $output;
		}

		$page_id      = (int) $submission->get_meta( 'container_post_id' );
		$user_page_id = um_get_predefined_page_id( 'user' );
		if ( $page_id !== $user_page_id ) {
			return '';
		}

		if ( ! empty( $_REQUEST['_wpcf7_um_profile_id'] ) ) {
			$profile_id = absint( $_REQUEST['_wpcf7_um_profile_id'] );
			if ( $profile_id !== um_user('ID') ) {
				um_fetch_user( $profile_id );
			}
			$opt = str_replace( '_um_', '', $name );
			if ( um_user( $opt ) ) {
				return um_user( $opt );
			}
		}

		return '';
	}
}
