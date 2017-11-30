<?php

/**
 * @group user
 */
class DiRT_Directory_Client_Tests_Users extends BP_UnitTestCase {
	protected $user_id;

	public function setUp() {
		parent::setUp();
		$this->user_id = $this->factory->user->create();
	}

	/**
	 * @group ddc_create_tool
	 */
	public function test_ddc_create_tool() {
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		$this->assertNotEmpty( $t );

		$post = get_post( $t );
		$this->assertSame( 'Foo', $post->post_title );
		$this->assertSame( 'ddc_tool', $post->post_type );
		$this->assertSame( 'http://example.com/foo', get_post_meta( $t, 'dirt_link', true ) );
		$this->assertSame( '345', get_post_meta( $t, 'dirt_node_id', true ) );
	}

	/**
	 * @group ddc_get_tool
	 */
	public function test_ddc_get_tool_by_node_id() {
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		$tool = ddc_get_tool( 'node_id', 345 );
		$this->assertSame( $t, $tool->ID );

		$this->assertSame( null, ddc_get_tool( 'node_id', 123 ) );
	}

	/**
	 * @group ddc_get_tool
	 */
	public function test_ddc_get_tool_by_link() {
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		$tool = ddc_get_tool( 'link', 'http://example.com/foo' );
		$this->assertSame( $t, $tool->ID );

		$this->assertSame( null, ddc_get_tool( 'link', 'http://example.com/bar' ) );
	}

	/**
	 * @group ddc_get_tool
	 */
	public function test_ddc_get_tool_by_title() {
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );

		$tool = ddc_get_tool( 'title', 'Foo' );
		$this->assertSame( $t, $tool->ID );

		$this->assertSame( null, ddc_get_tool( 'title', 'Bar' ) );
	}

	/**
	 * @group ddc_associate_tool_with_user
	 */
	public function test_ddc_associate_tool_with_user() {
		$t = $this->create_tool();

		$a = ddc_associate_tool_with_user( $t, $this->user_id );

		$this->assertNotEmpty( $a );

		$terms = wp_get_object_terms( $t, 'ddc_tool_is_used_by_user' );
		$this->assertNotEmpty( $terms );
		$this->assertSame( ddc_get_user_term( $this->user_id ), $terms[0]->slug );
	}

	/**
	 * @group ddc_get_user_id_from_usedby_term_slug
	 */
	public function test_ddc_get_user_id_from_usedby_term_slug() {
		$this->assertSame( 0, ddc_get_user_id_from_usedby_term_slug( 'foo' ) );
		$this->assertSame( 5, ddc_get_user_id_from_usedby_term_slug( 'ddc_tool_is_used_by_user_5' ) );
		$this->assertSame( 5, ddc_get_user_id_from_usedby_term_slug( 'ddc_tool_is_used_by_user_5_ad' ) );
	}

	/**
	 * @group ddc_get_users_of_tool
	 */
	public function test_ddc_get_users_of_tool() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();

		$t1 = $this->create_tool();
		$t2 = $this->create_tool();

		ddc_associate_tool_with_user( $t1, $u1 );
		ddc_associate_tool_with_user( $t1, $u2 );
		ddc_associate_tool_with_user( $t2, $u2 );
		ddc_associate_tool_with_user( $t2, $u3 );

		$users_of_t1 = ddc_get_users_of_tool( $t1 );
		$this->assertEqualSets( array( $u1, $u2 ), $users_of_t1 );

		$users_of_t2 = ddc_get_users_of_tool( $t2 );
		$this->assertEqualSets( array( $u2, $u3 ), $users_of_t2 );
	}

	/**
	 * @group ddc_get_users_of_tool
	 */
	public function test_ddc_get_users_of_tool_with_exclude() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();

		$t1 = $this->create_tool();
		$t2 = $this->create_tool();

		ddc_associate_tool_with_user( $t1, $u1 );
		ddc_associate_tool_with_user( $t1, $u2 );
		ddc_associate_tool_with_user( $t2, $u2 );
		ddc_associate_tool_with_user( $t2, $u3 );

		$users_of_t1 = ddc_get_users_of_tool( $t1, array(
			'exclude' => array( $u2 ),
		) );
		$this->assertEqualSets( array( $u1 ), $users_of_t1 );
	}

	/**
	 * @group ddc_get_users_of_tool
	 */
	public function test_ddc_get_users_of_tool_with_group_id() {
		$g = $this->factory->group->create();

		// In group, uses tool
		$u1 = $this->factory->user->create();

		// In group, doesn't use tool
		$u2 = $this->factory->user->create();

		// Not in group, uses tool
		$u3 = $this->factory->user->create();

		// Not in group, doesn't use tool
		$u4 = $this->factory->user->create();

		$this->add_user_to_group( $u1, $g );
		$this->add_user_to_group( $u2, $g );

		$t1 = $this->create_tool();

		ddc_associate_tool_with_user( $t1, $u1 );
		ddc_associate_tool_with_user( $t1, $u3 );

		$users_of_t1 = ddc_get_users_of_tool( $t1, array(
			'group_id' => array( $g ),
		) );
		$this->assertEqualSets( array( $u1 ), $users_of_t1 );
	}

	/**
	 * @group ddc_get_tools_of_user
	 */
	public function test_ddc_get_tools_of_user() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();

		$t1 = $this->create_tool();
		$t2 = $this->create_tool();

		ddc_associate_tool_with_user( $t1, $u1 );
		ddc_associate_tool_with_user( $t1, $u2 );
		ddc_associate_tool_with_user( $t2, $u2 );
		ddc_associate_tool_with_user( $t2, $u3 );

		$tools_of_u1 = ddc_get_tools_of_user( $u1 );
		$this->assertEqualSets( array( $t1 ), wp_list_pluck( $tools_of_u1, 'ID' ) );

		$tools_of_u2 = ddc_get_tools_of_user( $u2 );
		$this->assertEqualSets( array( $t1, $t2 ), wp_list_pluck( $tools_of_u2, 'ID' ) );
	}

	protected function create_tool() {
		$t = ddc_create_tool( array(
			'title' => 'Foo',
			'link' => 'http://example.com/foo',
			'node_id' => 345,
		) );
		return $t;
	}
}
