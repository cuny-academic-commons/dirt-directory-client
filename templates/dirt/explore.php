<h4><?php _e( 'Explore', 'dirt-directory-client' ) ?></h4>

<form method="get" action="">
	<?php $search_terms = isset( $_GET['dirt-search'] ) ? urldecode( $_GET['dirt-search'] ) : ''; ?>
	<p>
		<label for="dirt-search"><?php _e( 'Find tools from the DiRT Directory', 'dirt-directory-client' ) ?></label>
		<input type="text" name="dirt-search" id="dirt-search" value="<?php echo esc_attr( $search_terms ) ?>" />
	</p>

	<?php $search_results = ddc_query_tools( array(
		'search_terms' => $search_terms,
		'type' => 'search',
	) ); ?>

	<?php if ( ! empty( $search_results ) ) : ?>
		<ul>
		<?php foreach ( $search_results as $search_result ) : ?>
			<li><?php echo ddc_tool_markup( $search_result ) ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</form>
