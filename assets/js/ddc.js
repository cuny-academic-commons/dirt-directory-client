window.wp = window.wp || {};

( function( $ ) {
	var tool_id,
		$clicked,
		$current_checkbox,
		$current_tool,
		$tools,
		$tool_description_toggles,
		$tool_users_toggles;

	$( document ).ready( function() {
		// Add 'js' class - ugh
		$( 'body' ).removeClass( 'no-js' ).addClass( 'js' );

		$tools = $( '.dirt-tools' );

		init_tool_users_toggle();
		init_tool_description_toggle();

		init_tool_checkboxes();
	} );

	/**
	 * Initialize "users of this tool" toggles
	 */
	function init_tool_users_toggle() {
		$tools.find( '.dirt-tool-users-toggle-link-hide' ).hide();
		$tools.find( '.dirt-tool-users' ).hide();

		$tool_users_toggles = $( '.dirt-tool-users-toggle a' );

		$tool_users_toggles.on( 'click', function() {
			$clicked = $( this );
			$clicked.closest( '.dirt-tools > li' ).find( '.dirt-tool-users' ).toggle();
			$clicked.siblings( '.dirt-tool-users-toggle-link' ).show();
			$clicked.hide();
			return false;
		} );
	}

	/**
	 * Initialize "descritpion" toggles
	 */
	function init_tool_description_toggle() {
		$tools.find( '.dirt-tool-description-toggle-link-hide' ).hide();
		$tools.find( '.dirt-tool-description' ).hide();

		$tool_description_toggles = $( '.dirt-tool-description-toggle a' );

		$tool_description_toggles.on( 'click', function() {
			$clicked = $( this );
			$clicked.closest( '.dirt-tools > li' ).find( '.dirt-tool-description' ).toggle();
			$clicked.siblings( '.dirt-tool-description-toggle-link' ).show();
			$clicked.hide();
			return false;
		} );
	}
	/**
	 * Initialize the "I use this" checkbox toggles.
	 */
	function init_tool_checkboxes() {
		$tools.find( '.dirt-tool-action' ).each( function() {
			$current_tool = $(this);
			$current_tool.find( 'input[type="checkbox"], label' ).on( 'click', function( e ) {
				e.preventDefault();

				$current_checkbox = $(this).closest( '.dirt-tool-action' ).find( 'input[type="checkbox"]' );
				tool_id = $current_checkbox.data( 'tool-id' );

				$.ajax( {
					url: ajaxurl,
					method: 'GET',
					data: {
						'tool_id': tool_id,
						'action': 'ddc_tool_use_toggle',
						'tool_node_id': $current_checkbox.data( 'tool-node-id' ),
						'nonce': $current_checkbox.data( 'nonce' ),
						'toggle': $current_checkbox.is( ':checked' ) ? 'remove' : 'add'
					},
					success: function( response ) {
						if ( response.success ) {
							if ( 'add' == response.data.toggle ) {
								$current_checkbox.attr( 'checked', true );
								$current_checkbox.closest( '.dirt-tool-action' ).find( '.dirt-tool-action-question' ).html( DDC.remove_gloss );
							} else {
								$current_checkbox.removeAttr( 'checked' );
								$current_checkbox.closest( '.dirt-tool-action' ).find( '.dirt-tool-action-question' ).html( DDC.add_gloss );
							}
						}
					}
				} );
			} );
		} );
	}
} )( jQuery );
