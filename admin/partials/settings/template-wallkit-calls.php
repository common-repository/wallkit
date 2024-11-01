
<?php
/**
 * Wallkit settings page
 *
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>
<?php
$settings                   = isset($settings) ? $settings : null;
$wk_calls_use               = isset($settings) ? $settings->get_option("wk_calls_use") : true;
$wk_calls_debug             = isset($settings) ? $settings->get_option("wk_calls_debug") : false;
$wk_calls_handle_click      = isset($settings) ? $settings->get_option("wk_calls_handle_click") : null;
$wk_calls_users_status      = isset($settings) ? $settings->get_option("wk_calls_users_status") : null;
$wk_calls_users_status_body = isset($settings) ? $settings->get_option("wk_calls_users_status_body") : null;
$wk_calls_users_plans       = isset($settings) ? $settings->get_option("wk_calls_users_plans") : null;
$wk_calls_users_plans_body  = isset($settings) ? $settings->get_option("wk_calls_users_plans_body") : null;
$wk_calls_users_events      = isset($settings) ? $settings->get_option("wk_calls_users_events") : null;
$wk_calls_users_events_body = isset($settings) ? $settings->get_option("wk_calls_users_events_body") : null;
$nonce                      = wp_create_nonce( 'wk-nonce' );
?>
<div class="wrap">
    <h1>Wallkit Calls Settings</h1>
    <p>These settings require javascript integration.</p>

    <div class="wk-content postbox">

        <div class="wk-main-page">

            <div class="form-wrap">
                <form method="post" action="">
                    <input type="hidden" name="action" value="wallkit_wp_settings" />
                    <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                    <div class="wk-settings">
                        <div class="form-field">
                            <label for="wk_settings[wk_calls_use]">
                                <input type="hidden" name="wk_settings[wk_calls_use]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_calls_use]" name="wk_settings[wk_calls_use]" value="1" <?php echo esc_attr($wk_calls_use ? "checked" : "");?>>
                                <strong>Use Wallkit Calls</strong>
                            </label>
                            <p>Will enable Wallkit default configuration Sign-in/Call pop-ups and push data-attributes to content.</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_calls_debug]">
                                <input type="hidden" name="wk_settings[wk_calls_debug]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_calls_debug]" name="wk_settings[wk_calls_debug]" value="1" <?php echo esc_attr($wk_calls_debug ? "checked" : "");?>>
                                <strong>Debug Wallkit Calls</strong>
                            </label>
                            <p>Allows to debug clicks and actions</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_calls_handle_click]">Click event handler</label>
                            <input type="text" id="wk_settings[wk_calls_handle_click]" name="wk_settings[wk_calls_handle_click]" value="<?php echo esc_attr($wk_calls_handle_click); ?>">
                            <p>Class for handling user clicks for call pop-ups</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_calls_users_status]">Class that reacts to the user's status</label>
                            <input type="text" id="wk_settings[wk_calls_users_status]" name="wk_settings[wk_calls_users_status]" value="<?php echo esc_attr($wk_calls_users_status); ?>">
                            <p>Will be replaced by data-attributes according to the user's status</p>

                            <label for="wk_settings[wk_calls_users_status_body]">
                                <input type="hidden" name="wk_settings[wk_calls_users_status_body]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_calls_users_status_body]" name="wk_settings[wk_calls_users_status_body]" value="1" <?php echo esc_attr($wk_calls_users_status_body ? "checked" : ""); ?>>
                                Add in <?php esc_html_e('<body>')?>
                            </label>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_calls_users_plans]">Class that reacts to the user plans</label>
                            <input type="text" id="wk_settings[wk_calls_users_plans]" name="wk_settings[wk_calls_users_plans]" value="<?php echo esc_attr($wk_calls_users_plans); ?>">
                            <p>Will be replaced by data-attributes according to the user's plans</p>

                            <label for="wk_settings[wk_calls_users_plans_body]">
                                <input type="hidden" name="wk_settings[wk_calls_users_plans_body]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_calls_users_plans_body]" name="wk_settings[wk_calls_users_plans_body]" value="1" <?php echo esc_attr($wk_calls_users_plans_body ? "checked" : ""); ?>>
                                Add in <?php esc_html_e('<body>')?>
                            </label>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_calls_users_events]">Class that reacts to the user's events</label>
                            <input type="text" id="wk_settings[wk_calls_users_events]" name="wk_settings[wk_calls_users_events]" value="<?php echo esc_attr($wk_calls_users_events); ?>">
                            <p>Will be replaced by data-attributes according to the user's events</p>

                            <label for="wk_settings[wk_calls_users_events_body]">
                                <input type="hidden" name="wk_settings[wk_calls_users_events_body]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_calls_users_events_body]" name="wk_settings[wk_calls_users_events_body]" value="1" <?php echo esc_attr($wk_calls_users_events_body ? "checked" : ""); ?>>
                                Add in <?php esc_html_e('<body>')?>
                            </label>
                        </div>



                        <div>
                            <div class="wk-button">
                                <button class="wk-save-button">Save changes</button>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
