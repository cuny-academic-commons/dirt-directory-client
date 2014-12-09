<h3><?php _e( 'Explore', 'dirt-directory-client' ) ?></h3>

<form method="get" action="">
	<?php $search_terms = isset( $_GET['dirt-search'] ) ? urldecode( $_GET['dirt-search'] ) : ''; ?>
	<p>
		<label for="dirt-search"><?php _e( 'Find tools from the DiRT Directory', 'dirt-directory-client' ) ?></label>
		<input type="text" name="dirt-search" id="dirt-search" value="<?php echo esc_attr( $search_terms ) ?>" />
	</p>

	<?php if ( $search_terms ) : ?>
		<?php $search_results = ddc_query_tools( array(
			'search_terms' => $search_terms,
			'type' => 'search',
		) ); ?>

		<?php if ( ! empty( $search_results ) ) : ?>
			<p><?php printf( __( 'We found these tools that match your query: %s', 'dirt-directory-client' ), '<span class="dirt-search-terms">' . esc_html( $search_terms ) . '</span>' ) ?></p>

			<ol class="dirt-tools">
			<?php foreach ( $search_results as $search_result ) : ?>
				<li><?php echo ddc_tool_markup( array(
					'link' => $search_result->link,
					'title' => $search_result->title,
					'node_id' => $search_result->node->nid,
					'snippet' => $search_result->snippet,
					'thumbnail' => $search_result->node->field_logo->und[0]->filename,
					'image' => $search_result->node->field_logo->und[0]->uri,
				) ) ?></li>
			<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p><?php printf( __( 'We couldn&#8217;t find any tools that matched the following query: %s', 'dirt-directory-client' ), '<span class="dirt-search-terms">' . esc_html( $search_terms ) . '</span>') ?></p>
		<?php endif; ?>
	<?php endif ?>
</form>
