<?php

/**
 * @group activity
 */
class DiRT_Directory_Client_Tests_Activity extends BP_UnitTestCase {
	public function test_should_create_activity_when_marking_tool_as_used() {
		$u = $this->factory->user->create();
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		ddc_associate_tool_with_user( $t, $u );

		$found = bp_activity_get( array(
			'filter' => array(
				'object' => 'dirt',
				'user_id' => $u,
				'action' => 'tool_marked_used',
				'primary_id' => $t,
			),
		) );

		$this->assertSame( 1, count( $found['activities'] ) );

		$a = $found['activities'][0];
		$expected = sprintf( '<a href="%s">%s</a> uses the digital research tool Foo', bp_core_get_user_domain( $u ) . 'dirt/', bp_core_get_user_displayname( $u ) );
		$this->assertSame( $expected, $a->action );
		$this->assertSame( 'dirt', $a->component );
		$this->assertSame( 'tool_marked_used', $a->type );
		$this->assertEquals( $u, $a->user_id );
		$this->assertEquals( $t, $a->item_id );
	}

	public function test_should_delete_activity_when_dissociating_tool() {
		$u = $this->factory->user->create();
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		ddc_associate_tool_with_user( $t, $u );

		$found = bp_activity_get( array(
			'filter' => array(
				'object' => 'dirt',
				'user_id' => $u,
				'action' => 'tool_marked_used',
				'primary_id' => $t,
			),
		) );

		$this->assertSame( 1, count( $found['activities'] ) );

		ddc_dissociate_tool_from_user( $t, $u );

		$found = bp_activity_get( array(
			'filter' => array(
				'object' => 'dirt',
				'user_id' => $u,
				'action' => 'tool_marked_used',
				'primary_id' => $t,
			),
		) );

		$this->assertSame( 0, count( $found['activities'] ) );
	}
}
