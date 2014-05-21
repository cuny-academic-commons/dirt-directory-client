<?php
/*
Plugin Name: DiRT Directory Client
Version: 1.0
Description: Interface with the DiRT Directory API http://dirt.projectbamboo.org
Author: Boone B Gorges
Author URI: http://boone.gorg.es
Text Domain: dirt-directory-client
Domain Path: /languages
*/

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
	public $api_base = 'http://dev.bamboodirt.gotpantheon.com/services/';

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
		$request_uri = trailingslashit( $this->api_base ) . trailingslashit( $this->endpoint );
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
		$response = wp_remote_get( $uri );
		$response_body = wp_remote_retrieve_body( $response );
		return json_decode( $response_body );
	}

	/** Specific fetchers ************************************************/

	/**
	 * Get a list of taxonomies.
	 */
	public function get_taxonomies() {
		return $this->set_endpoint( 'taxonomy_vocabulary' )->request();
	}

	/**
	 * Get a list of terms for a given taxonomy.
	 *
	 * @todo This seems to be broken - it's returning items from all
	 *       taxonomies instead of just 'vid'
	 */
	public function get_taxonomy_terms( $taxonomy_id ) {
		return $this->set_endpoint( 'entity_taxonomy_term' )->add_query_var( 'vid', intval( $taxonomy_id ) )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given taxonomy term.
	 *
	 * @param int $taxonomy_term_id
	 */
	public function get_items_for_taxonomy_term( $taxonomy_term_id ) {
		return $this->set_endpoint( 'entity_node' )->add_query_var( 'tid', intval( $taxonomy_term_id ) )->request();
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

