<?php

/**
 * API client class.
 *
 * @since 1.0.0
 */
class DiRT_Directory_Client {
	/**
	 * Base URI for the API
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $api_base = DDC_ENDPOINT_URL;

	/**
	 * Endpoint URL chunk.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $endpoint;

	/**
	 * Query vars.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $query_vars = array();

	/**
	 * Status code.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $status_code;

	/**
	 * Parsed API response.
	 *
	 * @var mixed
	 * @since 1.0.0
	 */
	protected $parsed_response;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param string $endpoint Endpoint URI.
	 * @return DiRT_Directory_Client
	 */
	public function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
		return $this;
	}

	/**
	 * Add a query var.
	 *
	 * @param string $key   Key for the query var.
	 * @param mixed  $value Value for the query var.
	 * @return DiRT_Directory_Client
	 */
	public function add_query_var( $key, $value ) {
		// Will overwrite existing
		$this->query_vars[ $key ] = $value;
		return $this;
	}

	/**
	 * Build the request URI out of the params.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_taxonomies() {
		return $this->set_endpoint( 'taxonomy_vocabulary.json' )->request();
	}

	/**
	 * Get a list of terms for a given taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @param int $taxonomy_id ID of the taxonomy.
	 * @return mixed
	 */
	public function get_taxonomy_terms( $taxonomy_id ) {
		return $this->set_endpoint( 'entity_taxonomy_term.json' )->add_query_var( 'parameters[vid]', intval( $taxonomy_id ) )->request();
	}

	/**
	 * Get an item by node ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $node_id ID of the node.
	 * @return mixed
	 */
	public function get_item_by_node_id( $node_id ) {
		return $this->set_endpoint( 'node/' . $node_id . '.json' )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given taxonomy term.
	 *
	 * @todo Is broken
	 *
	 * @since 1.0.0
	 *
	 * @param int $taxonomy_term_id Term ID.
	 * @return mixed
	 */
	public function get_items_for_taxonomy_term( $taxonomy_term_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[tid]', intval( $taxonomy_term_id ) )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given category.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category.
	 * @return mixed
	 */
	public function get_items_for_category( $category_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[field_categories]', intval( $category_id ) )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given TaDiRAH term.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id TaDiRAH category ID.
	 * @return mixed
	 */
	public function get_items_for_tadirah_term( $category_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[field_tadirah_goals_methods]', intval( $category_id ) )->request();
	}

	/**
	 * Get a list of tools that match a search term.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_term Search term.
	 * @return mixed
	 */
	public function get_items_by_search_term( $search_term ) {
		return $this->set_endpoint( 'search_node/retrieve.json' )->add_query_var( 'keys', $search_term )->request();
	}
}

/**
 * Procedural wrapper for making API queries.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $type         Query type. 'search'.
 *     @type string $search_terms Terms to search.
 * }
 * @return array Array of formatted results.
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
			break;

		case 'category' :
			if ( empty( $args['cat_id'] ) ) {
				return $tools;
			}

			$tools = $c->get_items_for_tadirah_term( $args['cat_id'] );
			break;
	}

	// Normalize. This is awful.
	$parsed_tools = array();
	if ( ! empty( $tools ) ) {
		foreach ( $tools as $tool ) {
			$parsed_tools[] = ddc_parse_tool( $tool );
		}
	}

	return $parsed_tools;
}

