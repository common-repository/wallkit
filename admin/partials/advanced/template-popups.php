
<?php
/**
 * Wallkit advanced Popups script page
 *
 *
 * @since      3.1.1
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>
<?php
$settings                       = isset($settings) ? $settings : null;
$wk_modals_inline_selector      = isset($settings) && !empty($settings->get_option("wk_modals_inline_selector")) ? $settings->get_option("wk_modals_inline_selector") : '#wk-inline-popup-modal';
$wk_my_account_page_url         = isset($settings) ? $settings->get_option("wk_my_account_page_url") : '';
$nonce                          = wp_create_nonce( 'wk-nonce' );
?>

<div class="wrap">
    <div class="wk-content postbox">

        <div class="wk-content">
            <div class="wk-configuration-page">
                <div class="form-wrap">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="wallkit_advanced_page" />
                        <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                        <div class="wk-settings">
                            <div class="form-field">
                                <label for="wk_advanced[wk_modals_inline_selector]">Inline modals selector<br></label>
                                <input type="text" id="wk_advanced[wk_modals_inline_selector]" name="wk_advanced[wk_modals_inline_selector]" placeholder="#wk-inline-popup-modal" value="<?php echo esc_attr($wk_modals_inline_selector); ?>">
                                <p>You can replace default selector (#wk-inline-popup-modal) on your own.</p>
                                <p>For add inline element to the page you can use this shortcode <b>[wk-account-page id="YOUR_ID_SELECTOR" class="YOUR_CLASS_SELECTOR" modal="DEFAULT_MODAL"]</b></p>
                            </div>

                            <div class="form-field">
                                <label for="wk_advanced[wk_my_account_page_url]">My Account page url<br></label>
                                <input type="text" id="wk_advanced[wk_my_account_page_url]" name="wk_advanced[wk_my_account_page_url]" placeholder="" value="<?php echo esc_attr($wk_my_account_page_url); ?>">
                                <p>If fill user will redirect to this page when click on My Account buttons provided by plugin.</p>
                            </div>

                            <div class="wk-button">
                                <button class="wk-save-button">Save changes</button>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
