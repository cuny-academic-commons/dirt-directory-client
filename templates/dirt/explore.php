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
