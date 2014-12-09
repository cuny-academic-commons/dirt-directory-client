<?php

/**
 * API client class.
 *
 * @since 1.0
 */
class DiRT_Directory_Client {
	/**
	 * Base URI for the API
	 *
	 * @var string
	 */
	public $api_base = DDC_ENDPOINT_URL;

	/**
	 * Endpoint URL chunk.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Query vars.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Parsed API response.
	 *
	 * @var mixed
	 */
	protected $parsed_response;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->query_vars = array(
			'page'	    => 0,
			'pagesize'  => 100,
			'direction' => 'ASC',
		);
	}

	/**
	 * Set the endpoint (chunk of URL after the API base
	 *
	 * @since 1.0
	 */
	public function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
		return $this;
	}

	/**
	 * Add a query var.
	 *
	 * @param key
	 * @param value
	 */
	public function add_query_var( $key, $value ) {
		// Will overwrite existing
		$this->query_vars[ $key ] = $value;
		return $this;
	}

	/**
	 * Build the request URI out of the params.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_request_uri() {
		$request_uri = trailingslashit( $this->api_base ) . $this->endpoint;
		$request_uri = add_query_arg( $this->query_vars, $request_uri );
		return $request_uri;
	}

	/**
	 * Perform an API request.
	 *
	 * @since 1.0
	 *
	 * @return mixed
	 */
	public function request() {
		$uri = $this->get_request_uri();

		$response = wp_remote_get( $uri, array(
			'timeout' => 30,
		) );
		$response_body = wp_remote_retrieve_body( $response );
		return json_decode( $response_body );
	}

	/** Specific fetchers ************************************************/

	/**
	 * Get a list of taxonomies.
	 */
	public function get_taxonomies() {
		return $this->set_endpoint( 'taxonomy_vocabulary.json' )->request();
	}

	/**
	 * Get a list of terms for a given taxonomy.
	 */
	public function get_taxonomy_terms( $taxonomy_id ) {
		return $this->set_endpoint( 'entity_taxonomy_term.json' )->add_query_var( 'parameters[vid]', intval( $taxonomy_id ) )->request();
	}

	/**
	 * Get an item by node ID.
	 *
	 * @param int $node_id
	 */
	public function get_item_by_node_id( $node_id ) {
		return $this->set_endpoint( 'node/' . $node_id . '.json' )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given taxonomy term.
	 *
	 * @param int $taxonomy_term_id
	 *
	 * @todo Is broken
	 */
	public function get_items_for_taxonomy_term( $taxonomy_term_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[tid]', intval( $taxonomy_term_id ) )->request();
	}

	/**
	 * Get a list of tools that match a search term.
	 *
	 * @param string $search_term
	 */
	public function get_items_by_search_term( $search_term ) {
		return $this->set_endpoint( 'search_node/retrieve.json' )->add_query_var( 'keys', $search_term )->request();
	}
}

/**
 * Procedural wrapper for making API queries.
 *
 * @since 1.0
 *
 * @param array $args {
 *     @type string $type Query type. 'search'.
 *     @type string $search_terms Terms to search.
 * }
 * @return array
 */
function ddc_query_tools( $args ) {
	$tools = array();

	if ( empty( $args['type'] ) ) {
		return $tools;
	}

	$c = new DiRT_Directory_Client();

	switch ( $args['type'] ) {
		case 'search' :
			if ( empty( $args['search_terms'] ) ) {
				return $tools;
			}

			$tools = $c->get_items_by_search_term( $args['search_terms'] );
	}

	return $tools;
}

