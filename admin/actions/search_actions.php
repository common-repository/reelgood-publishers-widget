<?php
function reelgoodSearch() {
	$request = wp_remote_get(REELGOOD_PUBLISHERS_WIDGET_API_URL . "search?term=" . urlencode(sanitize_text_field( $_POST['query'] )), array(
    'headers' => array(
			'cache-control' => 'no-cache',
			'x-api-key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
			'origin' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"
    ),
	));

	$response = wp_remote_retrieve_body( $request );
	$response_code = wp_remote_retrieve_response_code( $request );

	echo wp_send_json_success(array(
		'results' => $response,
		'query' => sanitize_text_field( $_POST['query'] )
	));
}

add_action( 'wp_ajax_reelgood_search', 'reelgoodSearch' );
?>
