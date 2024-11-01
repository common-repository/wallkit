<?php
/**
 * Wallkit Additional script page
 *
 *
 * @since      3.1.1
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>

<?php
$settings               = isset($settings) ? $settings : null;
$wk_additional_script   = isset($settings) ? $settings->get_option("wk_additional_script") : null;
$wk_additional_script_place = isset($settings) ? $settings->get_option("wk_additional_script_place") : false;
$nonce                  = wp_create_nonce( 'wk-nonce' );
?>

<div class="wrap">
    <div class="wk-content postbox">
        <div class="">
            <div class="form-wrap">
            <form method="post" action="">
                <input type="hidden" name="action" value="wallkit_wp_settings" />
                <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                <div class="wk-settings">
                    <div class="wallkit-additional-script">
                        <h2>Additional Integration Script</h2>
                        <p>Available variables <b>wkSettings</b>, <b>wkTranslations</b></p>
                        <label for="wk_additional_script"></label>
                        <textarea id="wk_additional_script" name="wk_additional_script"><?php echo esc_textarea(base64_decode($wk_additional_script)); ?></textarea>
                    </div>

                    <div class="form-field">
                        <p><br></p>
                        <b>Select place where add this code:</b>
                        <p></p>
                        <input type="hidden" name="wk_settings[wk_additional_script_place]" value="0" />
                        <div>
                            <label for="wk_additional_script_place_header">
                                <input type="radio" id="wk_additional_script_place_header" name="wk_settings[wk_additional_script_place]" value="1" <?php echo esc_attr($wk_additional_script_place ? "checked" : "");?> >
                                Header</label>
                        </div>

                        <div>
                            <label for="wk_additional_script_place_footer">
                                <input type="radio" id="wk_additional_script_place_footer" name="wk_settings[wk_additional_script_place]" value="0" <?php echo esc_attr(!$wk_additional_script_place ? "checked" : "");?>>
                                Footer</label>
                        </div>
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


