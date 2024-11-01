<?php
/**
 * Wallkit advanced Sign-in script page
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
$wk_auth_migrated_users         = isset($settings) ? $settings->get_option("wk_auth_migrated_users") : false;
$wk_auth_allow_empty_pass       = isset($settings) ? $settings->get_option("wk_auth_allow_empty_pass") : false;
$wk_auth_migrated_users_text    = isset($settings) ? $settings->get_option("wk_auth_migrated_users_text") : '';
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
                                <label for="wk_advanced[wk_auth_migrated_users]">
                                    <input type="hidden" name="wk_advanced[wk_auth_migrated_users]" value="0" />
                                    <input type="checkbox" id="wk_advanced[wk_auth_migrated_users]" name="wk_advanced[wk_auth_migrated_users]" value="1" <?php echo esc_attr($wk_auth_migrated_users ? "checked" : ""); ?>>
                                    Custom sign-in process after migration
                                </label>
                                <p>When this option enable, imported users without password will receive the email with reset password.
                                    And after set new password users will be logged in authomaticaly.</p>
                            </div>

                            <div class="form-field">
                                <label for="wk_advanced[wk_auth_allow_empty_pass]">
                                    <input type="hidden" name="wk_advanced[wk_auth_allow_empty_pass]" value="0" />
                                    <input type="checkbox" id="wk_advanced[wk_auth_allow_empty_pass]" name="wk_advanced[wk_auth_allow_empty_pass]" value="1" <?php echo esc_attr($wk_auth_allow_empty_pass ? "checked" : ""); ?>>
                                    Allow empty password
                                </label>
                                <p>Users can try login with empty password. Useful if users imported without password.</p>
                            </div>

                            <div class="sign-in-migration-custom-html wallkit-custom-html">
                                <h2>Description for users</h2>
                                <?php wp_editor(base64_decode($wk_auth_migrated_users_text), "wk_auth_migrated_users_text"); ?>
                            </div>

                            <div class="wk-button wk-configuration-bottom">
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
