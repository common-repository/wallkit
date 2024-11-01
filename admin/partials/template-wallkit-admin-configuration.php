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
$wk_content_access_html = isset($settings) ? $settings->get_option("wk_content_access_html") : null;
$wk_paywall_styles      = isset($settings) ? $settings->get_option("wk_paywall_styles") : null;
$wk_my_account_html     = isset($settings) ? $settings->get_option("wk_my_account_html") : null;
$wk_my_account_styles   = isset($settings) ? $settings->get_option("wk_my_account_styles") : null;
$nonce                  = wp_create_nonce( 'wk-nonce' );
?>

<div class="wrap">
    <h1>Appearance</h1>

    <div class="wk-content">
        <div class="wk-configuration-page">
            <div id="tabs">
                <ul class="nav-tab-wrapper">
                    <li><a href="?page=wallkit-Appearance#tab-1" class="nav-tab"><span>Subscribe Box html</span></a></li>
                    <li><a href="?page=wallkit-Appearance#tab-2" class="nav-tab"><span>My account (Sign-in)</span></a></li>
                </ul>
                <div id="tab-1" class="tab-content">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="wallkit_configuration_page" />
                        <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                        <input type="hidden" name="template" value="paywall" />

                        <div class="paywall-custom-html wallkit-custom-html">
                            <h2>This template appears to the reader when content access is blocked.</h2>
                            <?php wp_editor(base64_decode($wk_content_access_html), "wk_content_access_html"); ?>
                        </div>

                        <div class="paywall-custom-css wallkit-custom-css">
                            <h2>Paywall styles:</h2>
                            <label for="wk_paywall_styles"></label>
                            <textarea id="wk_paywall_styles" name="wk_paywall_styles"><?php echo esc_textarea(base64_decode($wk_paywall_styles)); ?></textarea>
                        </div>

                        <div class="wk-button wk-configuration-bottom">
                            <button class="wk-save-button">Save changes</button>
                            <input class="wk-restore-button" type="submit" name="restore" value="Restore to defaults">
                            <div style="clear: both;"></div>
                        </div>
                    </form>
                </div>

                <div id="tab-2" class="tab-content">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="wallkit_configuration_page" />
                        <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                        <input type="hidden" name="template" value="my_account" />

                        <div class="my-account-custom-html wallkit-custom-html">
                            <h2>This template appears for the user if checked Display Sign-in button on the settings page.
                                You can use it to display the user Sign-in/My Account button.</h2>
                            <p>You can use this template by shortcode <b>[wk_my_account]</b></p>
                            <p>If you want to display the Logo, you should use the shortcode <b>[wk_site_logo class="my-class"]</b></p>
                            <p>If you want to display the Sign-in button, you should use the shortcode <b>[wk_my_account_button text="Sign in" class="my-class"]</b></p>
                            <p>If you want to display the user logo, you should use the shortcode <b>[wk_my_account_img class="my-class"]</b></p>
                            <p>OR, you can add just a class in any link - <b>wkwp-user-my-account-button</b></p>
                            <?php wp_editor(base64_decode($wk_my_account_html), "wk_my_account_html"); ?>
                        </div>

                        <div class="my-account-custom-css wallkit-custom-css">
                            <h2>My Account button styles:</h2>
                            <label for="wk_my_account_styles"></label>
                            <textarea id="wk_my_account_styles" name="wk_my_account_styles"><?php echo esc_textarea(base64_decode($wk_my_account_styles)); ?></textarea>
                        </div>

                        <div class="wk-button wk-configuration-bottom">
                            <button class="wk-save-button">Save changes</button>
                            <input class="wk-restore-button" type="submit" name="restore" value="Restore to defaults">
                            <div style="clear: both;"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>