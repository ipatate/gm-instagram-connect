<?php

namespace GMInstagramConnect\inc;

require dirname(__FILE__) . "/../vendor/autoload.php";

use Gilbitron\Util\SimpleCache;

add_action("rest_api_init", function () {
  // get news notifications
  register_rest_route("gm-instagram-connect", "/code", [
    "methods" => "GET",
    "callback" => __NAMESPACE__ . "\saveCode",
    "permission_callback" => "__return_true",
  ]);
});

add_action("rest_api_init", function () {
  // get news notifications
  register_rest_route("gm-instagram-connect", "/feed", [
    "methods" => "GET",
    "callback" => __NAMESPACE__ . "\getFeed",
    "permission_callback" => "__return_true",
  ]);
});

/**
 * simple get call
 *
 * @param string $url
 *
 * @return string
 */
function getGraph(string $url)
{
  $response = wp_remote_get($url);

  if (is_wp_error($response)) {
    if (WP_ENV === "development") {
      throw new Error($response);
    }
  }

  return json_decode($response["body"]);
}

/**
 * exchange token with long token
 *
 * @param string $access_token
 *
 * @return string | null
 */
function getLongToken(string $access_token): string
{
  // exchange with long term token
  $urlLongToken =
    "https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=" .
    get_option("gm_instagram_connect_option_client_secret") .
    "&access_token=" .
    $access_token;

  $decoded_body = getGraph($urlLongToken);

  if ($decoded_body && property_exists($decoded_body, "access_token")) {
    // save long term token
    update_option(
      "gm_instagram_connect_option_token",
      $decoded_body->access_token
    );
    return $decoded_body->access_token;
  }
  return null;
}

/**
 * renew the long term token
 *
 * @return string | null
 */
function renewToken($token)
{
  $decoded_body = getGraph(
    "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=" .
      $token
  );
  if ($decoded_body && property_exists($decoded_body, "access_token")) {
    // save long term token
    update_option(
      "gm_instagram_connect_option_token",
      $decoded_body->access_token
    );
    // time update token
    update_option(
      "gm_instagram_connect_option_token_date",
      date("Y-m-d H:i:s")
    );
    return $decoded_body->access_token;
  }
  return null;
}

/**
 * get feed insta
 *
 * @return json object
 */
function getFeed()
{
  // id auth
  $token = get_option("gm_instagram_connect_option_token");
  $user_id = get_option("gm_instagram_connect_option_user_id");
  $tokenDate = get_option("gm_instagram_connect_option_token_date");

  if (!$token) {
    return ["error" => "no token"];
  }

  // cache delay
  $EXPIRE = 60;

  // init cache
  $cache = new SimpleCache();
  $cache->cache_path = dirname(__FILE__) . "/../cache/";
  $cache->cache_time = $EXPIRE;

  // add 1 week to last date of update token
  $timestamp = date("Y-m-d H:i:s", strtotime("+1 week", strtotime($tokenDate)));
  $dt = date("Y-m-d H:i:s");

  // if last update is expired, renew the long term token
  if ($timestamp < $dt || !$tokenDate) {
    $token = renewToken($token);
  }

  // if cache exist
  if ($data = $cache->get_cache("instagram_feed_" . $user_id)) {
    return json_decode($data);

    // cache not exist
  } else {
    // url for get feed
    $urlFeed =
      "https://graph.instagram.com/me/media?fields=media_type,caption,media_url,permalink,thumbnail_url,username&access_token=" .
      $token;

    $decoded_body = getGraph($urlFeed);

    $cache->set_cache("instagram_feed_" . $user_id, json_encode($decoded_body));

    return $decoded_body;
  }
}

/**
 * used in admin for connect instagram API
 *
 * @param $request request from route
 *
 * @return void
 */
function saveCode(\WP_REST_Request $request)
{
  $code = $request->get_param("code");
  if ($code) {
    update_option("gm_instagram_connect_option_code", $code);
    // call instagram for get token from admin
    $response = wp_remote_post("https://api.instagram.com/oauth/access_token", [
      "method" => "POST",
      "body" => [
        "client_id" => get_option("gm_instagram_connect_option_client_id"),
        "client_secret" => get_option(
          "gm_instagram_connect_option_client_secret"
        ),
        "grant_type" => "authorization_code",
        "redirect_uri" => get_option(
          "gm_instagram_connect_option_redirect_uri"
        ),
        "code" => $code,
      ],
    ]);
    // if error
    if (is_wp_error($response)) {
      throw new Error($response);
    }
    $res = json_decode($response["body"]);

    // save token and user id
    if ($res && property_exists($res, "access_token")) {
      update_option("gm_instagram_connect_option_token", $res->access_token);
      update_option(
        "gm_instagram_connect_option_token_date",
        date("Y-m-d H:i:s")
      );
      update_option("gm_instagram_connect_option_user_id", $res->user_id);

      // exchange with long token 60 days
      getLongToken($res->access_token);
    }
  }
  wp_redirect("/wp-admin/options-general.php?page=gm-instagram-connect");
  exit();
}
