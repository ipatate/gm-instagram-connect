<?php

/**
 * Plugin Name:       Gm Instagram Connect
 * Description:       Instagram connect account for render feed media.
 * Requires at least: 5.7
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Faramaz Patrick <infos@goodmotion.fr>
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gm-instagram-connect
 *
 * @package           goodmotion
 */

require_once dirname(__FILE__) . '/inc/main.php';
require_once dirname(__FILE__) . '/inc/routes.php';

GMInstagramConnect\inc\main();


function block_init()
{
    register_block_type_from_metadata(__DIR__, [
        "render_callback" => __NAMESPACE__ . '\render_callback'
    ]);
}
add_action('init', __NAMESPACE__ . '\block_init');


function render_callback($attributes, $content)
{
    $feed =
        GMInstagramConnect\inc\getFeed();
    $b = '<div class="gm-instagram-feed"><div class="gm-instagram-feed-container">';
    foreach ($feed->data as $key => $val) {
        $type = $val->media_type;
        $media = $type === 'VIDEO' ? $val->thumbnail_url : $val->media_url;
        $b .= '<div class="gm-instagram-feed-element">'
            . '<a href="' . $val->permalink . '" rel="nofollow" target="_blank" title="' . $val->caption  . '">'
            . '<img src="' . $media . '" alt="' . $val->caption  . '" loading="lazy" /></a></div>';
    }
    $b .= '</div></div>';
    return $b;
}

// <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;display:block;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
// <g>
//   <animateTransform attributeName="transform" type="rotate" values="0 50 50;90 50 50" keyTimes="0;1" dur="1s" repeatCount="indefinite"></animateTransform><circle cx="50" cy="50" r="30" stroke="#450b40" fill="none" stroke-dasharray="23.561944901923447 188.49555921538757" stroke-linecap="round" stroke-width="10" transform="rotate(0 50 50)">
//   <animate attributeName="stroke" values="#450b40;#f47e60" keyTimes="0;1" dur="1s" repeatCount="indefinite"></animate>
// </circle><circle cx="50" cy="50" r="30" stroke="#f47e60" fill="none" stroke-dasharray="23.561944901923447 188.49555921538757" stroke-linecap="round" stroke-width="10" transform="rotate(90 50 50)">
//   <animate attributeName="stroke" values="#f47e60;#f8b26a" keyTimes="0;1" dur="1s" repeatCount="indefinite"></animate>
// </circle><circle cx="50" cy="50" r="30" stroke="#f8b26a" fill="none" stroke-dasharray="23.561944901923447 188.49555921538757" stroke-linecap="round" stroke-width="10" transform="rotate(180 50 50)">
//   <animate attributeName="stroke" values="#f8b26a;#abbd81" keyTimes="0;1" dur="1s" repeatCount="indefinite"></animate>
// </circle><circle cx="50" cy="50" r="30" stroke="#abbd81" fill="none" stroke-dasharray="23.561944901923447 188.49555921538757" stroke-linecap="round" stroke-width="10" transform="rotate(270 50 50)">
//   <animate attributeName="stroke" values="#abbd81;#450b40" keyTimes="0;1" dur="1s" repeatCount="indefinite"></animate>
// </circle></g>
// </svg>