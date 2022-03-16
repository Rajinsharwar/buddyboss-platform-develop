<?php
/**
 * The template for displaying the activity loop in the ajaxified search result.
 *
 * This template can be overridden by copying it to yourtheme/buddypress/search/loop/activity-ajax.php.
 *
 * @package BuddyBoss\Core
 * @version 1.0.0
 */

?>
<div class="bp-search-ajax-item bp-search-ajax-item_activity">
	<a href='<?php echo esc_url( add_query_arg( array( 'no_frame' => '1' ), bp_activity_thread_permalink() ) ); ?>'>
		<div class="item-avatar">
			<?php
			bp_activity_avatar(
				array(
					'type'   => 'thumb',
					'height' => 50,
					'width'  => 50,
				)
			);
			?>
		</div>

		<div class="item">
			<h3 class="entry-title item-title">
				<a href="<?php bp_activity_user_link(); ?>"><?php echo wp_kses_post( bp_core_get_user_displayname( bp_get_activity_user_id() ) ); ?></a>
			</h3>
			<?php esc_html_e( 'posted an update', 'buddyboss' ); ?>
			<?php esc_html_e( 'replied to a post', 'buddyboss' ); ?>
			<?php if ( bp_activity_has_content() ) : ?>
				<div class="item-title">
					<?php echo wp_kses_post( bp_search_activity_intro( 30 ) ); ?>
				</div>
			<?php endif; ?>
			<div class="item-meta activity-header">
				<time>
					<?php echo wp_kses_post( human_time_diff( bp_nouveau_get_activity_timestamp() ) ) . esc_html__( ' ago', 'buddyboss' ); ?>
				</time>
			</div>
		</div>
	</a>
</div>
