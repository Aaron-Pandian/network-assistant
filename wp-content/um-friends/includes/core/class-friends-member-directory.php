<?php
namespace um_ext\um_friends\core;

use um\core\Member_Directory_Meta;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Friends_Member_Directory
 *
 * @package um_ext\um_friends\core
 */
class Friends_Member_Directory {

	/**
	 * Friends_Member_Directory constructor.
	 */
	public function __construct() {
		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'um_friends_admin_directory_options_profile' ) );
		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_friends_sort_user_option' ) );
		add_action( 'um_pre_directory_shortcode', array( &$this, 'um_friends_directory_enqueue_scripts' ) );

		// for grid
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'friends_stats' ), 2 );
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'friends_button' ), 102 );

		//for list
		add_action( 'um_members_list_after_user_name_tmpl', array( &$this, 'friends_stats' ), 2 );
		add_action( 'um_members_list_just_after_actions_tmpl', array( &$this, 'friends_button' ), 102 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'um_friends_ajax_get_members_data' ), 50, 2 );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'um_friends_sortby_friends' ), 100, 2 );
		add_filter( 'pre_user_query', array( &$this, 'um_wp_user_filter_by_friends' ), 100 );

		// UM metatable integration
		add_action( 'um_pre_users_query', array( &$this, 'add_sortby_custom' ), 10, 3 );
	}

	/**
	 * Admin options for directory filtering.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function um_friends_admin_directory_options_profile( $fields ) {
		$global_friends_show_stats  = UM()->options()->get( 'friends_show_stats' );
		$global_friends_show_button = UM()->options()->get( 'friends_show_button' );

		if ( $global_friends_show_stats ) {
			$fields = array_merge(
				array_slice( $fields, 0, 3 ),
				array(
					array(
						'id'    => '_um_friends_hide_stats',
						'type'  => 'checkbox',
						'label' => __( 'Hide friends stats', 'um-friends' ),
						'value' => UM()->query()->get_meta_value( '_um_friends_hide_stats', null, 'na' ),
					),
				),
				array_slice( $fields, 3, count( $fields ) - 1 )
			);
		}
		if ( $global_friends_show_button ) {
			$fields = array_merge(
				array_slice( $fields, 0, 3 ),
				array(
					array(
						'id'    => '_um_friends_hide_button',
						'type'  => 'checkbox',
						'label' => __( 'Hide friend button', 'um-friends' ),
						'value' => UM()->query()->get_meta_value( '_um_friends_hide_button', null, 'na' ),
					),
				),
				array_slice( $fields, 3, count( $fields ) - 1 )
			);
		}

		return $fields;
	}

	/**
	 * Sort by Friends.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function um_friends_sort_user_option( $options ) {
		$options['most_friends']  = __( 'Most friends', 'um-friends' );
		$options['least_friends'] = __( 'Least friends', 'um-friends' );
		return $options;
	}

	/**
	 * Enqueue scripts on member directory.
	 *
	 * @param array $args
	 */
	public function um_friends_directory_enqueue_scripts( $args ) {
		$global_friends_show_stats  = UM()->options()->get( 'friends_show_stats' );
		$global_friends_show_button = UM()->options()->get( 'friends_show_button' );

		if ( ( empty( $args['friends_hide_stats'] ) && ! empty( $global_friends_show_stats ) ) || ( empty( $args['friends_hide_button'] ) && ! empty( $global_friends_show_button ) ) ) {
			wp_enqueue_style( 'um_friends' );
			wp_enqueue_script( 'um_friends' );
		}
	}

	/**
	 * Add button to member directory.
	 *
	 * @param array $args
	 */
	public function friends_button( $args ) {
		$hide_friends_button = ! empty( $args['friends_hide_button'] ) ? $args['friends_hide_button'] : ! UM()->options()->get( 'friends_show_button' );

		if ( empty( $hide_friends_button ) ) {
			?>
			<# if ( user.friends_button ) { #>
				<div class="um-members-friend-btn um-members-list-footer-button-wrapper">{{{user.friends_button}}}</div>
			<# } #>
			<?php
		}
	}

	/**
	 * Add stats to member directory.
	 *
	 * @param array $args
	 */
	public function friends_stats( $args ) {
		$hide_friends_stats = ! empty( $args['friends_hide_stats'] ) ? $args['friends_hide_stats'] : ! UM()->options()->get( 'friends_show_stats' );

		if ( empty( $hide_friends_stats ) ) {
			?>
			<div class="um-members-friend-stats">
				<div>{{{user.count_friends}}} <?php esc_attr_e( 'friends', 'um-friends' ); ?></div>
			</div>
			<?php
		}
	}

	/**
	 * Expand AJAX member directory data.
	 *
	 * @param array $data_array
	 * @param int   $user_id
	 *
	 * @return array
	 */
	public function um_friends_ajax_get_members_data( $data_array, $user_id ) {
		$data_array['friends_button'] = UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() );

		um_fetch_user( $user_id );

		$can_view = true;
		if ( ! is_user_logged_in() || get_current_user_id() !== absint( $user_id ) ) {
			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Friends only', 'um-friends' ) );
			$is_private_case     = UM()->user()->is_private_case( $user_id, 'friends' );
			if ( $is_private_case || $is_private_case_old ) { // only friends can view my profile
				$can_view = false;
			}
		}

		$data_array['count_friends'] = false;
		if ( $can_view ) {
			$data_array['count_friends'] = UM()->Friends_API()->api()->count_friends( $user_id );
		}

		return $data_array;
	}

	/**
	 * Adding sort directories by friends.
	 *
	 * @param array  $query_args
	 * @param string $sortby
	 *
	 * @return array
	 */
	public function um_friends_sortby_friends( $query_args, $sortby ) {
		if ( 'most_friends' !== $sortby && 'least_friends' !== $sortby ) {
			return $query_args;
		}

		$query_args['orderby'] = 'friends';
		$query_args['order']   = 'most_friends' === $sortby ? 'DESC' : 'ASC';

		return $query_args;
	}

	/**
	 * Adding sort directories by friends.
	 *
	 * @param WP_Query $query
	 *
	 * @return WP_Query
	 */
	public function um_wp_user_filter_by_friends( $query ) {
		global $wpdb;

		if ( isset( $query->query_vars['orderby'] ) && 'friends' === $query->query_vars['orderby'] ) {
			$friends_table = UM()->Friends_API()->api()->table_name;
			$order         = isset( $query->query_vars['order'] ) ? $query->query_vars['order'] : 'DESC';

			$query->query_orderby = sprintf( "ORDER BY ( SELECT COUNT(*) FROM {$friends_table} WHERE ( {$friends_table}.user_id1 = {$wpdb->users}.ID OR {$friends_table}.user_id2 = {$wpdb->users}.ID ) AND {$friends_table}.status = 1 ) %s", $order );
		}

		return $query;
	}

	/**
	 * Adding sort directories by friends.
	 *
	 * @param Member_Directory_Meta $query
	 * @param array                 $directory_data
	 * @param string                $sortby
	 */
	public function add_sortby_custom( $query, $directory_data, $sortby ) {
		if ( 'most_friends' !== $sortby && 'least_friends' !== $sortby ) {
			return;
		}

		$order = 'most_friends' === $sortby ? 'DESC' : 'ASC';

		$friends_table    = UM()->Friends_API()->api()->table_name;
		$query->sql_order = sprintf( "ORDER BY ( SELECT COUNT(*) FROM {$friends_table} WHERE ( {$friends_table}.user_id1 = u.ID OR {$friends_table}.user_id2 = u.ID ) AND {$friends_table}.status = 1 ) %s", $order );
	}
}
