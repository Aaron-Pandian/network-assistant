<?php
/**
 * Template for the UM Friends. The list of user friends
 *
 * Page: Profile > Friends > My Friends
 * Call: UM()->Friends_API()->shortcode()->ultimatemember_friends()
 * Shortcode: [ultimatemember_friends]
 *
 * Call: UM()->Friends_API()->shortcode()->ultimatemember_friends_online()
 * Shortcode: [ultimatemember_friends_online]
 *
 * Page: Profile > Friends > Friends Reguests
 * Call: UM()->Friends_API()->shortcode()->ultimatemember_friend_reqs()
 * Shortcode: [ultimatemember_friend_reqs]
 *
 * Page: Profile > Friends > Friend Requests Sent
 * Call: UM()->Friends_API()->shortcode()->ultimatemember_friend_reqs_sent()
 * Shortcode: [ultimatemember_friend_reqs_sent]
 *
 * @version 2.2.6
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-friends/friends.php
 * @var string $note
 * @var array  $friends
 * @var int    $user_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $note ) ) {
	?>
	<div class="um-profile-note">
		<span><?php echo esc_html( $note ); ?></span>
	</div>
	<?php
}

if ( $friends ) {
	foreach ( $friends as $arr ) {
		if ( isset( $_sent ) ) {
			$user_id2 = absint( $arr['user_id1'] );
		} else {
			$user_id2 = absint( $arr['user_id2'] );
		}

		if ( $user_id2 === $user_id ) {
			$user_id2 = absint( $arr['user_id1'] );
		}

		um_fetch_user( $user_id2 );
		?>

		<div class="um-friends-user">
			<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-friends-user-photo" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
				<?php echo get_avatar( um_user('ID'), 50 ); ?>
			</a>

			<div class="um-friends-user-btn">
				<?php if ( $user_id2 == get_current_user_id() ) {
					echo '<a href="' . esc_url( um_edit_profile_url() ) . '" class="um-friend-edit um-button um-alt">' . __( 'Edit profile', 'um-friends' ) . '</a>';
				} else {
					echo UM()->Friends_API()->api()->friend_button( $user_id2, get_current_user_id(), true );
				} ?>
			</div>

			<div class="um-friends-user-name">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>"><?php echo um_user( 'display_name' ); ?></a>

				<?php
				do_action( 'um_friends_list_post_user_name', $user_id, $user_id2 );
				do_action( 'um_friends_list_after_user_name', $user_id, $user_id2 );
				?>
			</div>

			<?php do_action( 'um_friends_list_pre_user_bio', $user_id, $user_id2 ); ?>

			<div class="um-friends-user-bio">
				<?php echo um_filtered_value( 'description' ); ?>
			</div>

			<?php do_action( 'um_friends_list_post_user_bio', $user_id, $user_id2 ); ?>
		</div>
		<?php
	}
	um_reset_user();
}
