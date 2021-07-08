<?php

namespace GMInstagramConnect\inc;


/**
 * Register the JavaScript for the public-facing side of the site.
 */
function enqueue_scripts()
{
	$PLUGIN_NAME = 'gm-instagram-connect';
	add_action('wp_enqueue_scripts', function () use ($PLUGIN_NAME) {
		$path = plugins_url() . '/' . $PLUGIN_NAME;

		wp_enqueue_script($PLUGIN_NAME, $path . '/assets/js/script.js', array(), '0.0.1', true);
	});

	// add async and defer attributes to enqueued scripts
	add_filter(
		'script_loader_tag',
		function ($tag, $handle, $src) use ($PLUGIN_NAME) {

			if ($handle === $PLUGIN_NAME) {

				if (false === stripos($tag, 'defer')) {

					$tag = str_replace(' src', ' defer src', $tag);
				}
			}

			return $tag;
		},
		10,
		3
	);
}
