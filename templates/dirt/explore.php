<a name="explore"></a>
<h3><?php _e( 'Explore', 'dirt-directory-client' ) ?></h3>

<?php $url = remove_query_arg( array( 'dirt-category', 'dirt-search' ), bp_get_requested_url() ) ?>
<form method="get" action="<?php echo $url ?>#explore">
	<?php $search_terms = isset( $_GET['dirt-search'] ) ? urldecode( $_GET['dirt-search'] ) : ''; ?>
	<?php $cat_id = isset( $_GET['dirt-category'] ) ? intval( $_GET['dirt-category'] ) : ''; ?>
	<p><?php _e( 'Find tools from the DiRT Directory:', 'dirt-directory-client' ) ?></p>

	<p>
		<label for="dirt-category" class="explore-type-label"><?php _e( 'By category', 'dirt-directory-client' ) ?></label>
		<?php $categories = ddc_categories() ?>
		<select name="dirt-category" id="dirt-category">
			<option value=""></option>
			<?php foreach ( $categories as $cat ) : ?>
				<option value="<?php echo intval( $cat['tid'] ) ?>"><?php echo esc_html( $cat['name'] ) ?></option>
			<?php endforeach ?>
		</select>
		<input class="dirt-explore-button" type="submit" value="<?php _e( 'Go', 'dirt-directory-client' ) ?>" />
	</p>

	<p class="dirt-explore-or"><?php _e( 'or', 'dirt-directory-client' ) ?></p>

	<p>
		<label for="dirt-search" class="explore-type-label"><?php _e( 'By keyword', 'dirt-directory-client' ) ?></label>
		<input type="text" name="dirt-search" id="dirt-search" value="" />
		<input class="dirt-explore-button" type="submit" value="<?php _e( 'Go', 'dirt-directory-client' ) ?>" />
	</p>
</form>

<?php if ( $search_terms || $cat_id ) : ?>
	<?php
	if ( $search_terms ) {
		$args = array(
			'search_terms' => $search_terms,
			'type' => 'search',
		);

		$results_string = sprintf( __( 'We found these tools that match your query: %s', 'dirt-directory-client' ), '<span class="dirt-search-terms">' . esc_html( $search_terms ) . '</span>' );
	} else if ( $cat_id ) {
		$args = array(
			'cat_id' => $cat_id,
			'type' => 'category',
		);

		$cats = ddc_categories();
		$cat_name = '';
		foreach ( $cats as $cat ) {
			if ( $cat['tid'] == $cat_id ) {
				$cat_name = $cat['name'];
				break;
			}
		}

		$results_string = sprintf( __( 'We found these tools in the category: %s', 'dirt-directory-client' ), '<span class="dirt-search-terms">' . esc_html( $cat_name ) . '</span>' );
	}

	$search_results = ddc_query_tools( $args );

	?>

	<?php if ( ! empty( $search_results ) ) : ?>
		<p><?php echo $results_string ?></p>

		<ol class="dirt-tools">
		<?php foreach ( $search_results as $search_result ) : ?>
			<li><?php echo ddc_tool_markup( $search_result ) ?></li>
		<?php endforeach; ?>
		</ol>
	<?php else : ?>
		<p><?php printf( __( 'We couldn&#8217;t find any tools that matched the following query: %s', 'dirt-directory-client' ), '<span class="dirt-search-terms">' . esc_html( $search_terms ) . '</span>') ?></p>
	<?php endif; ?>
<?php endif ?>
