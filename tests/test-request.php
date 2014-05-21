<?php

class DiRT_Directory_Client_Tests_Requests extends WP_UnitTestCase {
	/**
	 * @group set_endpoint
	 */
	public function test_set_endpoint() {
		$c = new DiRT_Directory_Client();
		$uri = $c->set_endpoint( 'foo' )->get_request_uri();

		$expected = trailingslashit( $c->api_base ) . 'foo/?page=0&pagesize=100';

		$this->assertSame( $expected, $uri );
	}

	/**
	 * @group add_query_var
	 */
	public function test_add_query_var() {
		$c = new DiRT_Directory_Client();
		$uri = $c->set_endpoint( 'foo' )->add_query_var( 'bar', 'baz' )->get_request_uri();

		$expected = trailingslashit( $c->api_base ) . 'foo/?page=0&pagesize=100&bar=baz';

		$this->assertSame( $expected, $uri );
	}

	/**
	 * @group add_query_var
	 */
	public function test_add_query_var_multiple() {
		$c = new DiRT_Directory_Client();
		$uri = $c->set_endpoint( 'foo' )->add_query_var( 'bar', 'baz' )->add_query_var( 'bar2', 'baz2' )->get_request_uri();

		$expected = trailingslashit( $c->api_base ) . 'foo/?page=0&pagesize=100&bar=baz&bar2=baz2';

		$this->assertSame( $expected, $uri );
	}

	/**
	 * @group add_query_var
	 */
	public function test_add_query_var_override_pag_args() {
		$c = new DiRT_Directory_Client();
		$uri = $c->set_endpoint( 'foo' )->add_query_var( 'page', '3' )->add_query_var( 'pagesize', '200' )->get_request_uri();

		$expected = trailingslashit( $c->api_base ) . 'foo/?page=3&pagesize=200';

		$this->assertSame( $expected, $uri );
	}

	/**
	 * @group request
	 */
	public function test_request() {
		$c = new DiRT_Directory_Client();
//		print_r( $c->get_taxonomies() );
//		print_r( $c->get_taxonomy_terms( 6 ) );
//		print_r( $c->get_items_for_taxonomy_term( 6 ) );
//		print_r( $c->get_items_by_search_term( 'wiki' ) );
//		print_r( $c );
	}
}

