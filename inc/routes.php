<?php

namespace GMInstagramConnect\inc;

require dirname(__FILE__) . '/../vendor/autoload.php';

use \Gilbitron\Util\SimpleCache;

add_action('rest_api_init', function () {
	// get news notifications
	register_rest_route('gm-instagram-connect', '/code', array(
		'methods' => 'GET',
		'callback' => __NAMESPACE__ . '\saveCode',
		'permission_callback' => '__return_true'
	));
});


add_action('rest_api_init', function () {
	// get news notifications
	register_rest_route('gm-instagram-connect', '/feed', array(
		'methods' => 'GET',
		'callback' => __NAMESPACE__ . '\getFeed',
		'permission_callback' => '__return_true'
	));
});


function getFeed()
{
	$token = get_option('gm_instagram_connect_option_token');
	$user_id = get_option('gm_instagram_connect_option_user_id');
	if (!$token) {
		return ['error' => 'no token'];
	}
	// cache delay
	$EXPIRE = 60;

	// init cache
	$cache = new SimpleCache();
	$cache->cache_path = dirname(__FILE__) . '/../cache/';
	$cache->cache_time = $EXPIRE;
	// if cache exist
	if ($data = $cache->get_cache('instagram_feed_' . $user_id)) {
		return json_decode($data);
	} else {
		$response = wp_remote_get(
			'https://graph.instagram.com/me/media?fields=media_type,caption,media_url,permalink,thumbnail_url,username&access_token=' . $token
		);
		if (is_wp_error($response)) {
			return $response;
		}
		$decoded_body = json_decode($response['body']);
		if (property_exists($decoded_body, 'error')) {
			$response = wp_remote_get(
				'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $token
			);
			if (is_wp_error($response)) {
				return $response;
			}
			$feed = json_decode($response['body']);
			if ($feed && property_exists($feed, 'access_token')) {
				// save long term token
				update_option('gm_instagram_connect_option_token', $feed->access_token);
			}
			// recall feed
			$response = wp_remote_get(
				'https://graph.instagram.com/me/media?fields=media_type,caption,media_url,permalink,thumbnail_url,username&access_token=' . $token
			);
			if (is_wp_error($response)) {
				return $response;
			}
		}

		$cache->set_cache('instagram_feed_' . $user_id, $response['body']);

		return json_decode($response['body']);
	}
}



function saveCode(\WP_REST_Request $request)
{
	$code = $request->get_param('code');
	if ($code) {
		update_option('gm_instagram_connect_option_code', $code);
		// call instagram for get token
		$response = wp_remote_post('https://api.instagram.com/oauth/access_token', [
			'method' => 'POST',
			'body' => [
				'client_id' => get_option('gm_instagram_connect_option_client_id'),
				'client_secret' =>
				get_option('gm_instagram_connect_option_client_secret'),
				'grant_type' =>
				'authorization_code',
				'redirect_uri' => get_option('gm_instagram_connect_option_redirect_uri'),
				'code' =>
				$code
			]
		]);
		if (is_wp_error($response)) {
			return $response;
		}
		$feed = json_decode($response['body']);
		// save token and user id
		if ($feed && property_exists($feed, 'access_token')) {
			update_option('gm_instagram_connect_option_token', $feed->access_token);
			update_option('gm_instagram_connect_option_user_id', $feed->user_id);
			// exchange with long term token
			$response = wp_remote_get('https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=' . get_option('gm_instagram_connect_option_client_secret') . '&access_token=' . $feed->access_token);

			if (is_wp_error($response)) {
				return $response;
			}
			$feed = json_decode($response['body']);
			if ($feed && property_exists($feed, 'access_token')) {
				// save long term token
				update_option('gm_instagram_connect_option_token', $feed->access_token);
			}
		}
	}
	wp_redirect('/wp-admin/options-general.php?page=gm-instagram-connect');
	exit;
}
