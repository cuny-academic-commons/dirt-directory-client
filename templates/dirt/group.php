<h2><?php _e( 'Digital Research Tools', 'dirt-directory-client' ) ?></h2>

<p><?php _e( 'The DiRT Directory is a registry of digital research tools for scholarly use.', 'dirt-directory-client' ) ?></p>

<?php /* Tools in use by the group */ ?>
<?php $group_tools = ddc_get_tools_used_by_group(); ?>
<?php if ( ! empty( $group_tools ) ) : ?>
	<h3><?php _e( 'This Group&#8217;s Tools', 'dirt-directory-client' ) ?></h3>
	<p><?php printf( _n( 'Members of this group use %s tool from the <a href="http://dirtdirectory.org">DiRT Directory</a>:', 'Members of this group use %s tools from the <a href="http://dirtdirectory.org">DiRT Directory</a>:', count( $group_tools ), 'dirt-directory-client' ), number_format_i18n( count( $group_tools ) ) ) ?></p>
	<ul class="dirt-tools dirt-tools-of-group">
	<?php foreach ( $group_tools as $group_tool ) : ?>
		<li><?php echo ddc_tool_markup( array(
			'link' => $group_tool->dirt_link,
			'title' => $group_tool->post_title,
			'node_id' => $group_tool->dirt_node_id,
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php bp_get_template_part( 'dirt/explore' ); ?>


