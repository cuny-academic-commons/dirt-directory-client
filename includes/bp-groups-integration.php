<?php

/**
 * Integration into the BuddyPress Groups component.
 *
 * @since 1.0.0
 */

/**
 * Implementation of BP_Group_Extension.
 *
 * @since 1.0.0
 */
class DDC_Group_Extension extends BP_Group_Extension {

	/**
	 * Current group ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $current_group_id;

	/**
	 * Current group object.
	 *
	 * @since 1.0.0
	 * @var BP_Groups_Group
	 */
	protected $current_group;

	/**
	 * Group settings.
         *
	 * @since 1.0.0
	 * @var array
	 */
	protected $current_group_settings = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::init( array(
			'name'   => __( 'Digital Research Tools', 'dirt-directory-client' ),
			'slug'   => 'dirt',
			'access' => $this->access_setting(),
			'screens' => array(
				'edit' => array(
					'enabled' => true,
				),
			),
		) );
	}

	/**
	 * Determine access setting for the main plugin tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function access_setting() {
		// Has the admin enabled?
		$settings = $this->get_current_group_settings();

		if ( empty( $settings['enabled'] ) ) {
			return 'noone';
		}

		// If it's enabled, return a value based on group status
		$group = $this->get_current_group();

		if ( 'public' === $group->status ) {
			return 'anyone';
		} else {
			return 'member';
		}
	}

	/**
	 * Main tab display.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function display( $group_id = null ) {
		wp_enqueue_style( 'dirt-directory-client' );
		wp_enqueue_script( 'dirt-directory-client' );

		bp_get_template_part( 'dirt/group' );
	}

	/**
	 * Settings screen markup.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function settings_screen( $group_id = null ) {
		$settings = $this->get_current_group_settings();

		?>

		<label for="dirt-enabled"><?php _e( 'Enable "Digital Research Tools" tab for this group?', 'dirt-directory-client' ) ?></label>
		<select id="dirt-enabled" name="dirt-enabled">
			<option value="1" <?php selected( '1', $settings['enabled'] ) ?>><?php _e( 'Enabled', 'dirt-directory-client' ) ?></option>
			<option value="0" <?php selected( '0', $settings['enabled'] ) ?>><?php _e( 'Disabled', 'dirt-directory-client' ) ?></option>
		</select>

		<br /><br />

		<?php
	}

	/**
	 * Settings screen save callback.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function settings_screen_save( $group_id = null ) {
		$old_settings = $new_settings = $this->get_current_group_settings();

		if ( isset( $_POST['dirt-enabled'] ) ) {
			if ( '0' == $_POST['dirt-enabled'] ) {
				$new_settings['enabled'] = '0';
			} else {
				$new_settings['enabled'] = '1';
			}
		}

		if ( $old_settings !== $new_settings ) {
			groups_update_groupmeta( $group_id, 'ddc_settings', $new_settings );
			bp_core_add_message( __( 'Settings updated!', 'dirt-directory-client' ) );
		}
	}

	/**
	 * Get the current group ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int ID of the current group.
	 */
	protected function get_current_group_id() {
		if ( is_null( $this->current_group_id ) ) {
			$this->current_group_id = bp_get_current_group_id();
		}

		return $this->current_group_id;
	}

	/**
	 * Get the current group.
	 *
	 * @since 1.0.0
	 *
	 * @return BP_Groups_Group Group object.
	 */
	protected function get_current_group() {
		if ( is_null( $this->current_group ) ) {
			$this->current_group = groups_get_group( array(
				'group_id' => $this->get_current_group_id(),
			) );
		}

		return $this->current_group;
	}

	/**
	 * Get settings for the current group.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_current_group_settings() {
		if ( empty( $this->current_group_settings ) ) {
			$saved_settings = groups_get_groupmeta( $this->get_current_group_id(), 'ddc_settings' );

			if ( ! is_array( $saved_settings ) ) {
				$saved_settings = array();
			}

			$this->current_group_settings = array_merge( array(
				'enabled' => '0',
			), $saved_settings );
		}

		return $this->current_group_settings;
	}
}

/**
 * Get tools in use by a group.
 *
 * @param int $group_id Optional. Group ID. Default: current group ID.
 * @return array
 */
function ddc_get_tools_used_by_group( $group_id = null ) {
	if ( is_null( $group_id ) && bp_is_group() ) {
		$group_id = bp_get_current_group_id();
	}

	$group_members = wp_cache_get( $group_id, 'ddc_bp_group_members' );
	if ( false === $group_members ) {
		$group_member_query = new BP_Group_Member_Query( array(
			'group_id' => $group_id,
			'type' => 'alphabetical',
			'group_role' => array( 'admin', 'mod', 'member' ),
		) );
		$group_members = $group_member_query->results;

		wp_cache_add( $group_id, $group_members, 'ddc_bp_group_members' );
	}

	$group_member_tools = array();
	foreach ( $group_members as $group_member ) {
		$gm_tools = ddc_get_tools_of_user( $group_member->ID );
		foreach ( $gm_tools as $gm_tool ) {
			if ( ! isset( $group_member_tools[ $gm_tool->ID ] ) ) {
				$group_member_tools[ $gm_tool->ID ] = $gm_tool;
			}

			if ( ! isset( $group_member_tools[ $gm_tool->ID ]->users_of_tool ) ) {
				$group_member_tools[ $gm_tool->ID ]->users_of_tool = array();
			}

			$group_member_tools[ $gm_tool->ID ]->users_of_tool[] = $group_member->ID;
		}
	}

	return $group_member_tools;
}
