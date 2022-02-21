<?php
/**
 * The template for users header
 *
 * This template can be overridden by copying it to yourtheme/buddypress/members/single/member-header.php.
 *
 * @since   BuddyPress 1.0.0
 * @version 1.0.0
 */

remove_filter( 'bp_get_add_follow_button', 'buddyboss_theme_bp_get_add_follow_button' );

if ( ! bp_is_user_messages() && ! bp_is_user_settings() && ! bp_is_user_notifications() && ! bp_is_user_profile_edit() && ! bp_is_user_change_avatar() && ! bp_is_user_change_cover_image() ) :

	$profile_header_layout_style = bb_get_profile_header_layout_style();
	$is_enabled_online_status    = bb_enabled_profile_header_layout_element( 'online-status' );
	$is_enabled_profile_type     = bb_enabled_profile_header_layout_element( 'profile-type' );
	$is_enabled_member_handle    = bb_enabled_profile_header_layout_element( 'member-handle' );
	$is_enabled_joined_date      = bb_enabled_profile_header_layout_element( 'joined-date' );
	$is_enabled_last_active      = bb_enabled_profile_header_layout_element( 'last-active' );
	$is_enabled_followers        = bb_enabled_profile_header_layout_element( 'followers' );
	$is_enabled_following        = bb_enabled_profile_header_layout_element( 'following' );
	?>

	<div id="cover-image-container" class="item-header-wrap <?php echo esc_attr( $profile_header_layout_style ); ?>">

		<?php $class = bp_disable_cover_image_uploads() ? 'bb-disable-cover-img' : 'bb-enable-cover-img'; ?>
		<div id="item-header-cover-image" class="<?php echo esc_attr( $class ); ?>">

			<div id="item-header-avatar">
				<?php if ( bp_is_my_profile() && ! bp_disable_avatar_uploads() ) { ?>
					<?php
					if ( $is_enabled_online_status ) {
						bb_current_user_status( bp_displayed_user_id() );
					}
					?>
					<a href="<?php bp_members_component_link( 'profile', 'change-avatar' ); ?>" class="link-change-profile-image bp-tooltip" data-balloon-pos="down" data-balloon="<?php esc_attr_e( 'Change Profile Photo', 'buddyboss' ); ?>">
						<i class="bb-icon-edit-thin"></i>
					</a>
				<?php } ?>
				<?php bp_displayed_user_avatar( 'type=full' ); ?>
			</div><!-- #item-header-avatar -->

			<div id="item-header-content">
				<div class="flex">
					<div class="bb-user-content-wrap">
						<div class="flex align-items-center member-title-wrap">
							<h2 class="user-nicename"><?php echo wp_kses_post( bp_core_get_user_displayname( bp_displayed_user_id() ) ); ?></h2>

							<?php
							if ( true === bp_member_type_enable_disable() && true === bp_member_type_display_on_profile() && $is_enabled_profile_type ) {
								echo wp_kses_post( bp_get_user_member_type( bp_displayed_user_id() ) );
							}
							?>
						</div>

						<?php bp_nouveau_member_hook( 'before', 'header_meta' ); ?>

						<div class="header-meta-wrap">

							<?php if ( ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) || bp_get_last_activity() || bb_get_member_joined_date() ) : ?>
								<div class="item-meta">
									<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() && $is_enabled_member_handle ) : ?>
										<span class="mention-name">@<?php bp_displayed_user_mentionname(); ?></span>
									<?php endif; ?>

									<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() && bp_get_last_activity() && $is_enabled_member_handle && ( $is_enabled_last_active || $is_enabled_joined_date ) ) : ?>
										<span class="separator">&bull;</span>
									<?php endif; ?>

									<?php
									bp_nouveau_member_hook( 'before', 'header_meta' );

									if ( bp_get_last_activity() && $is_enabled_last_active ) :
										echo wp_kses_post( bp_get_last_activity() );
									endif;
									?>

									<?php if ( bp_get_last_activity() && bb_get_member_joined_date() && $is_enabled_joined_date ) : ?>
										<span class="separator">&bull;</span>
									<?php endif; ?>

									<?php
									if ( bb_get_member_joined_date() && $is_enabled_joined_date ) :
										echo wp_kses_post( bb_get_member_joined_date() );
									endif;
									?>
								</div>
							<?php endif; ?>

							<?php if ( function_exists( 'bp_is_activity_follow_active' ) && bp_is_active( 'activity' ) && bp_is_activity_follow_active() && ( $is_enabled_followers || $is_enabled_following ) ) { ?>
								<div class="flex align-items-top member-social">
									<div class="flex align-items-center">
										<?php
										if ( $is_enabled_followers ) {
											bb_get_followers_count();
										}

										if ( $is_enabled_following ) {
											bb_get_following_count();
										}
										?>
									</div>
									<?php echo wp_kses_post( bp_get_user_social_networks_urls() ); ?>
								</div>
							<?php } else { ?>
								<div class="flex align-items-center">
									<?php echo wp_kses_post( bp_get_user_social_networks_urls() ); ?>
								</div>
							<?php } ?>
						
						</div><!-- .header-meta-wrap -->

					</div><!-- .bb-user-content-wrap -->

					<div class="member-header-actions-wrap">

						<?php
							remove_filter( 'bp_get_add_friend_button', 'buddyboss_theme_bp_get_add_friend_button' );
							bp_nouveau_member_header_buttons( array( 'container_classes' => array( 'member-header-actions' ) ) );
							bp_nouveau_member_header_bubble_buttons( array( 'container_classes' => array( 'bb_more_options' ) ) );
							add_filter( 'bp_get_add_friend_button', 'buddyboss_theme_bp_get_add_friend_button' );
						?>
					</div><!-- .member-header-actions-wrap -->

					<?php
						bp_nouveau_member_header_buttons( array( 'container_classes' => array( 'member-header-actions' ) ) );
						bp_nouveau_member_header_bubble_buttons( array( 'container_classes' => array( 'bb_more_options' ) ) );
					?>
				</div>

			</div><!-- #item-header-content -->

		</div><!-- #item-header-cover-image -->
	</div><!-- #cover-image-container -->
	<?php
	add_filter( 'bp_get_add_follow_button', 'buddyboss_theme_bp_get_add_follow_button' );

endif; ?>

<!-- Remove Connection confirmation popup -->
<div class="bb-remove-connection bb-action-popup" style="display: none">
	<transition name="modal">
		<div class="modal-mask bb-white bbm-model-wrap">
			<div class="modal-wrapper">
				<div class="modal-container">
					<header class="bb-model-header">
						<h4><span class="target_name"><?php echo esc_html__( 'Remove Connection', 'buddyboss' ); ?></span></h4>
						<a class="bb-close-remove-connection bb-model-close-button" href="#">
							<span class="bb-icon bb-icon-close"></span>
						</a>
					</header>
					<div class="bb-remove-connection-content bb-action-popup-content">
						<p><?php echo _e( 'Are you sure you want to remove <span class="bb-group-name"></span> from your connections? ', 'buddyboss' ); ?></p>
					</div>
					<footer class="bb-model-footer flex align-items-center">
						<a class="bb-close-remove-connection" href="#"><?php echo esc_html__( 'Cancel', 'buddyboss' ); ?></a>
						<a class="button push-right bb-confirm-remove-connection" href="#"><?php echo esc_html__( 'Confirm', 'buddyboss' ); ?></a>
					</footer>
				</div>
			</div>
		</div>
	</transition>
</div> <!-- .bb-remove-connection -->
