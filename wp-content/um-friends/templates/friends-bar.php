<?php
/**
 * Template for the UM Friends. The list of user friends
 *
 * Page: Profile > Friends > My Friends
 * Call: UM()->Friends_API()->shortcode()->ultimatemember_friends_bar()
 * Shortcode: [ultimatemember_friends_bar]
 *
 * @version 2.2.5
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-friends/friends-bar.php
 * @var bool $can_view
 * @var int  $user_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = ( isset( $_REQUEST['profiletab'] ) && sanitize_key( $_REQUEST['profiletab'] ) == 'friends' ) ? 'current' : ''; ?>


<div class="um-friends-bar">

	<div class="um-friends-rc">
		<?php if ( $can_view ) { ?>
			<a href="<?php echo esc_url( UM()->Friends_API()->api()->friends_link( $user_id ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php _e( 'friends', 'um-friends' ); ?><?php echo UM()->Friends_API()->api()->count_friends( $user_id ); ?>
			</a>
		<?php } ?>
	</div>

	<?php if ( UM()->Friends_API()->api()->can_friend( $user_id, get_current_user_id() ) ) { ?>
		<div class="um-friends-btn">
			<?php echo UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ); ?>
			<?php do_action( 'um_after_friend_button_profile', $user_id ); ?>
		</div>
	<?php } ?>
	<div class="um-clear"></div>
</div>
