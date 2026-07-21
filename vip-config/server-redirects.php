<?php
/**
 * Server redirects owned by @prc/taxonomies.
 *
 * Pure PHP only — loaded from vip-config before WordPress boots.
 * No WordPress APIs, Composer autoload, or plugin bootstrap.
 *
 * @package PRC\Platform\Taxonomies
 */

if ( ! function_exists( 'prc_taxonomies_server_redirects' ) ) {
	/**
	 * Redirect legacy /category/ URLs to /topic/.
	 *
	 * @param string $http_host   Request host.
	 * @param string $request_uri Full request URI (path + query).
	 * @param string $full_url     Absolute URL built from host + URI.
	 * @param string $request_path Request path without query string.
	 * @return void
	 */
	function prc_taxonomies_server_redirects( $http_host, $request_uri, $full_url, $request_path = '' ) {
		unset( $http_host, $full_url, $request_path );

		if ( false === strpos( $request_uri, '/category/' ) ) {
			return;
		}

		$new_request = str_replace( '/category/', '/topic/', $request_uri );
		header( 'Location: ' . $new_request, true, 301 );
		exit;
	}
}
