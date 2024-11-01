<?php
/**
 * Wallkit Appearance page
 *
 *
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>

<?php
$settings               = isset($settings) ? $settings : null;
$wk_additional_options  = isset($settings) ? $settings->get_option("wk_additional_options") : null;
$nonce                  = wp_create_nonce( 'wk-nonce' );
?>

<div class="wrap">
    <div class="wk-content postbox">
        <div class="wk-main-page">

            <div class="form-wrap">
            <form method="post" action="">
                <input type="hidden" name="action" value="wallkit_wp_settings" />
                <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                <div class="wk-settings">
                    <div class="wallkit-additional-options">
                        <h2>Additional Integration options</h2>
                        <label for="wk_additional_options"></label>
                        <textarea id="wk_additional_options" name="wk_additional_options"><?php echo esc_textarea(base64_decode($wk_additional_options)); ?></textarea>
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


