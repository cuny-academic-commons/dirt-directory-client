<p><?php _e( 'The DiRT Directory is a registry of digital research tools for scholarly use.', 'dirt-directory-client' ) ?></p>

<?php bp_get_template_part( 'dirt/explore' ); ?>

<hr />

<h3><?php _e( 'On this site', 'dirt-directory-client' ) ?></h3>

<?php $search_terms = isset( $_GET['dirt-search'] ) ? urldecode( $_GET['dirt-search'] ) : ''; ?>
<?php $cat_id = isset( $_GET['dirt-category'] ) ? intval( $_GET['dirt-category'] ) : ''; ?>

<?php
if ( isset( $_GET['orderby'] ) && 'newest' === $_GET['orderby'] ) {
	$tool_orderby = 'date';
	$tool_order   = 'DESC';
} else {
	$tool_orderby = 'name';
	$tool_order   = 'ASC';
}

$cat_name = '';
if ( $cat_id ) {
	$cats = ddc_categories();
	$cat_name = '';
	foreach ( $cats as $cat ) {
		if ( $cat['tid'] == $cat_id ) {
			$cat_name = $cat['name'];
			break;
		}
	}
}

$used_tool_args = array(
	'search_terms'   => $search_terms,
	'orderby'        => $tool_orderby,
	'order'          => $tool_order,
	'posts_per_page' => -1,
);

if ( $cat_name ) {
	$used_tool_args['categories'] = $cat_name;
}

$used_tools = ddc_get_tools( $used_tool_args );

?>

<p><?php printf( __( 'The following tools are in use by members on %s.', 'dirt-directory-client' ), esc_html( get_option( 'blogname' ) ) ) ?>

<?php if ( ! empty( $used_tools ) ) : ?>
	<a name="local-results"> </a>
	<ul class="dirt-tools">
	<?php foreach ( $used_tools as $used_tool ) : ?>
		<li><?php echo ddc_tool_markup( array(
			'link' => $used_tool->dirt_link,
			'title' => $used_tool->post_title,
			'node_id' => $used_tool->dirt_node_id,
			'description' => $used_tool->post_content,
			'thumbnail' => $used_tool->dirt_thumbnail,
			'image' => $used_tool->dirt_image,
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p><?php _e( 'No tools found.', 'dirt-directory-client' ) ?></p>
<?php endif ?>

<?php if ( $search_terms || $cat_id ) : ?>
	<a name="dirt-results"> </a>
	<h3><?php _e( 'On the DiRT Directory', 'dirt-directory-client' ) ?></h3>
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
