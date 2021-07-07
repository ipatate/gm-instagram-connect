<?php

namespace GMInstagramConnect\inc;


function register_settings()
{
    add_option('gm_instagram_connect_option_client_id', '');
    add_option('gm_instagram_connect_option_client_secret', '');
    add_option('gm_instagram_connect_option_redirect_uri', '');
    add_option('gm_instagram_connect_option_user_id', '');
    add_option('gm_instagram_connect_option_token', '');
    add_option('gm_instagram_connect_option_code', 'https://' .  $_SERVER['SERVER_NAME'] . '/wp-json/gm-instagram-connect/code');

    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_client_id');
    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_client_secret');
    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_redirect_uri');
    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_code');
    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_user_id');
    register_setting('gm_instagram_connect_options_group', 'gm_instagram_connect_option_token');
}


function register_options_page()
{
    add_options_page('Instagram Connect', 'Instagram Connect', 'manage_options', 'gm-instagram-connect', __NAMESPACE__ . '\options_page');
}


function options_page()
{
?>
    <div>
        <h2>Instagram Connect</h2>
        <h3>1 - Create application on <a href="https://developers.facebook.com/apps">https://developers.facebook.com/apps</a></h3>
        <form method="post" action="options.php">
            <?php settings_fields('gm_instagram_connect_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="gm_instagram_connect_option_client_id">Client ID</label></th>
                    <td><input type="text" id="gm_instagram_connect_option_client_id" name="gm_instagram_connect_option_client_id" value="<?php echo get_option('gm_instagram_connect_option_client_id'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="gm_instagram_connect_option_client_secret">Client Secret</label></th>
                    <td><input type="password" id="gm_instagram_connect_option_client_secret" name="gm_instagram_connect_option_client_secret" value="<?php echo get_option('gm_instagram_connect_option_client_secret'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="gm_instagram_connect_option_redirect_uri">Redirect URI</label></th>
                    <td><input type="text" id="gm_instagram_connect_option_redirect_uri" name="gm_instagram_connect_option_redirect_uri" value="<?php echo get_option('gm_instagram_connect_option_redirect_uri'); ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2">Default Redirect URI : https://<?php echo $_SERVER['SERVER_NAME']; ?>/wp-json/gm-instagram-connect/code</td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <hr />
        <div style="word-break: break-all;">
            <?php if (
                get_option('gm_instagram_connect_option_redirect_uri') !== '' &&
                get_option('gm_instagram_connect_option_client_id') !== ''
            ) : ?>
                <div>
                    <h3>2 - Get the token</h3><a class="button button-primary" href="<?php echo 'https://api.instagram.com/oauth/authorize?client_id='
                                                                                            . get_option('gm_instagram_connect_option_client_id')
                                                                                            . '&redirect_uri='
                                                                                            . get_option('gm_instagram_connect_option_redirect_uri')
                                                                                            . '&scope=user_profile,user_media&response_type=code' ?>">Authorize Instagram Connect</a>
                </div><br /><br />
            <?php endif; ?>

            <strong>Code : <?php echo get_option('gm_instagram_connect_option_code'); ?></strong><br /><br />
            <strong>User ID : <?php echo get_option('gm_instagram_connect_option_user_id'); ?></strong><br /><br />
            <strong>Token : <?php echo get_option('gm_instagram_connect_option_token'); ?></strong><br /><br />
        </div>
        <div>
            <h3>3 - Test feed url </h3>
            <a href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/wp-json/gm-instagram-connect/feed' target="_blank">Instagram feed url</a>
        </div>
    </div>
<?php
}

add_action('admin_menu', __NAMESPACE__ . '\register_options_page');
add_action('admin_init', __NAMESPACE__ . '\register_settings');
