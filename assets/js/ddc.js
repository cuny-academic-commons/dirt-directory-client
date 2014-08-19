window.wp = window.wp || {};

( function( $ ) {
	var $clicked,
		$tools,
		$tool_users_toggles;

	$( document ).ready( function() {
		// Add 'js' class - ugh
		$( 'body' ).removeClass( 'no-js' ).addClass( 'js' );

		$tools = $( '.dirt-tools' );

		init_tool_users_toggle();
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
} )( jQuery );
