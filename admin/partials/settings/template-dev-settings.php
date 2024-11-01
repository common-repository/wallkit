
<?php
/**
 * Wallkit dev page
 *
 * @since      3.2.4
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>
<?php
$settings                   = isset($settings) ? $settings : null;
$wk_server                  = isset($settings) ? $settings->get_option("wk_server") : 'prod';
$wk_wilmode                 = isset($settings) ? $settings->get_option("wk_wilmode") : 'prod';
$wk_debug                   = isset($settings) ? $settings->get_option("wk_debug") : false;
$nonce                      = wp_create_nonce( 'wk-nonce' );
?>
<div class="wrap">
    <h1>Wallkit Dev Settings</h1>
    <div class="wk-content postbox">

        <div class="wk-main-page">

            <div class="form-wrap">
                <form method="post" action="">
                    <input type="hidden" name="action" value="wallkit_wp_settings" />
                    <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                    <div class="wk-settings">
                        <div class="form-field">
                            <label for="wk_settings[wk_server]"><b>Choose environment type:</b></label>
                            <input type="hidden" name="wk_settings[wk_server]" value="prod" />
                            <select name="wk_settings[wk_server]" id="wk_settings[wk_server]">
                                <option value="prod" <?php selected( $wk_server, 'prod' ); ?>>Production</option>
                                <option value="dev"  <?php selected( $wk_server, 'dev' ); ?>>Dev</option>
                            </select>
                            <p>Wallkit plugin would make api calls and load assets from selected environment.</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_wilmode]"><b>Choose WIL type:</b></label>
                            <input type="hidden" name="wk_settings[wk_wilmode]" value="prod" />
                            <select name="wk_settings[wk_wilmode]" id="wk_settings[wk_wilmode]">
                                <option value="prod" <?php selected( $wk_wilmode, 'prod' ); ?>>Production</option>
                                <option value="dev"  <?php selected( $wk_wilmode, 'dev' ); ?>>Dev</option>
                            </select>
                            <p>Wallkit plugin would load js library from selected environment.</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_debug]">
                                <input type="hidden" name="wk_settings[wk_debug]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_debug]" name="wk_settings[wk_debug]" value="1" <?php echo esc_attr($wk_debug? "checked" : ""); ?>>
                                <b>Enable debug mode</b>
                            </label>
                            <p>Wallkit plugin would make api calls and load assets from selected environment.</p>
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
