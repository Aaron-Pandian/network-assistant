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
 * @var int   $max
 * @var int   $user_id
 * @var array $friends
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-friends-m" data-max="<?php echo esc_attr( $max ); ?>">
	<?php
	$total_friends_count = 0;
	if ( $friends ) {
		foreach ( $friends as $arr ) {

			$total_friends_count++;

			$user_id2 = absint( $arr['user_id2'] );
			if ( $user_id2 === $user_id ) {
				$user_id2 = absint( $arr['user_id1'] );
			}

			um_fetch_user( $user_id2 );
			?>

			<div class="um-friends-m-user">
				<div class="um-friends-m-pic">
					<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-tip-n" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
						<?php echo get_avatar( um_user( 'ID' ), 40 ); ?>
					</a>
				</div>
			</div>

			<?php
		}
		um_reset_user();
	} else {
		?>

		<p>
			<?php echo ( $user_id == get_current_user_id() ) ? __( 'You do not have any friends yet.', 'um-friends' ) : __( 'This user does not have any friends yet.', 'um-friends' ); ?>
		</p>

		<?php
	}
	?>
</div>
<div class="um-clear"></div>
