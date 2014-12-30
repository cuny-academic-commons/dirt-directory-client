<div class="dirt-tool">
	<?php $tool_avatar = ddc_get_tool_avatar_url( get_the_ID() ); ?>
	<?php if ( $tool_avatar ) : ?>
		<div class="dirt-tool-avatar">
			<img src="<?php echo esc_url( $tool_avatar ) ?>" />
		</div>
	<?php endif; ?>

	<div class="dirt-tool-data">
		<p class="dirt-tool-link">
			<?php printf(
				__( 'On the DiRT Directory: %s', 'dirt-directory-client' ),
				sprintf( '<a href="%1$s">%1$s</a>', get_post_meta( get_the_ID(), 'dirt_link', true ) )
			); ?>
		</p>

		<?php the_content() ?>
	</div>

	<div class="dirt-tool-all-users">
		<h3><?php printf( __( 'Users on %s', 'dirt-directory-client' ), get_option( 'blogname' ) ) ?></h3>
		<?php $tool_users = ddc_get_users_of_tool( get_the_ID(), array( 'count' => false, ) ); ?>

		<ul class="dirt-tool-all-users-list">
		<?php foreach ( $tool_users['users'] as $tool_user ) : ?>
			<li>
				<?php printf(
					'%s <a href="%s">%s</a>',
					bp_core_fetch_avatar( array( 'item_id' => $tool_user->ID, 'width' => 25, 'height' => 25, ) ),
					bp_core_get_user_domain( $tool_user->ID ),
					bp_core_get_user_displayname( $tool_user->ID )
				); ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
</div>
