<h2><?php _e( 'DiRT Directory', 'dirt-directory-client' ) ?></h2>

<p><?php _e( 'The DiRT Directory is a registry of digital research tools for scholarly use.', 'dirt-directory-client' ) ?></p>

<?php /* Tools in use by the group */ ?>
<?php $group_tools = ddc_get_tools_used_by_group(); ?>
<?php if ( ! empty( $group_tools ) ) : ?>

<?php endif; ?>

<?php bp_get_template_part( 'dirt/explore' ); ?>


