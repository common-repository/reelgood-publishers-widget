<?php
function reelgood_feedback() {	
	$args = array(
    'body' => json_encode($_POST['feedback']),
    'timeout' => '30',
    'redirection' => '10',
    'httpversion' => '1.0',
    'blocking' => true,
    'headers' => array(
			'cache-control' => 'no-cache',
			'x-api-key' => REELGOOD_PUBLISHERS_WIDGET_BUNDLE_API_KEY,
			'origin' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
			'Content-Type' => 'application/json',
			'x-platform' => 'Publishers Widget - WordPress - WP:' . REELGOOD_PUBLISHERS_WIDGET_VERSION . ' - JS:' . REELGOOD_JS_BUNDLE_VERSION
		),
    'cookies' => array()
);

	$request = wp_remote_post(REELGOOD_PUBLISHERS_GATEWAY_API_URL . "/v1/feedback", $args);

	$response = wp_remote_retrieve_body( $request );
	$response_code = wp_remote_retrieve_response_code( $request );

	echo wp_send_json_success();
}

add_action( 'wp_ajax_reelgood_feedback', 'reelgood_feedback' );
?>
