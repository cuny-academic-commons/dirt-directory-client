<?php

/**
 * @group functions
 */
class DiRT_Directory_Client_Tests_Functions extends BP_UnitTestCase {

	public function test_ddc_get_tools_search_terms() {
		$t1 = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'description' => 'Lorem ipsem',
			'node_id' => 345,
		) );

		$t2 = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'description' => 'Foo bar',
			'node_id' => 346,
		) );

		$t3 = ddc_create_tool( array(
			'title' => 'Lorem',
			'link' => 'http://example.com/foo',
			'description' => 'Foo bar',
			'node_id' => 347,
		) );

		$tools = ddc_get_tools( array(
			'search_terms' => 'Lorem',
		) );

		$this->assertEqualSets( array( $t1, $t3 ), wp_list_pluck( $tools, 'ID' ) );
	}
}
