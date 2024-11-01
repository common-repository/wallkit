
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
$wk_is_active               = isset($settings) ? $settings->get_option("wk_is_active") : false;
$wk_r_key                   = isset($settings) ? $settings->get_option("wk_r_key") : null;
$wk_rs_key                  = isset($settings) ? $settings->get_option("wk_rs_key") : null;
$wk_is_auto_sync            = isset($settings) ? $settings->get_option("wk_is_auto_sync") : false;
$wk_check_taxonomies_sync   = isset($settings) ? $settings->getOptionSyncTaxonomies() : [];
$wk_check_post_access       = isset($settings) ? $settings->get_option("wk_check_post_access") : false;
$wk_check_post_type_access  = isset($settings) ? $settings->get_option("wk_check_post_type_access") : [];
$wk_admin_paywall           = isset($settings) ? $settings->get_option("wk_admin_paywall") : false;
$wk_free_paragraph          = isset($settings) ? $settings->get_option("wk_free_paragraph") : null;
$wk_show_blur               = isset($settings) ? $settings->get_option("wk_show_blur") : null;
$wk_paywall_display_type    = isset($settings) ? $settings->get_option("wk_paywall_display_type") : null;
$wk_content_key_prefix      = isset($settings) ? $settings->get_option("wk_content_key_prefix") : null;
$wk_content_class_selector  = isset($settings) && !empty($settings->get_option("wk_content_class_selector")) ? $settings->get_option("wk_content_class_selector") : 'wkwp-post-content';
$wk_custom_content_selector = isset($settings) && !empty($settings->get_option("wk_custom_content_selector")) ? $settings->get_option("wk_custom_content_selector") : null;
$wk_custom_integration      = isset($settings) ? $settings->get_option("wk_custom_integration") : false;
$wk_analytics               = isset($settings) ? $settings->get_option("wk_analytics") : null;
$wk_sign_in_button          = isset($settings) ? $settings->get_option("wk_sign_in_button") : null;
$wk_reload_on_logout        = isset($settings) ? $settings->get_option("wk_reload_on_logout") : true;
$wk_nav_menu_sign_in_button = isset($settings) ? $settings->get_option("wk_nav_menu_sign_in_button") : [];
$nonce                      = wp_create_nonce( 'wk-nonce' );
?>
<div class="wrap">
    <h1>Settings</h1>

    <div class="wk-content postbox">

        <div class="wk-main-page">

            <div class="form-wrap">
                <form method="post" action="">
                    <input type="hidden" name="action" value="wallkit_wp_settings" />
                    <input type="hidden" name="wpnonce" value="<?php echo esc_attr($nonce);?>" />
                    <div class="wk-settings">
                        <div class="form-field">
                            <label for="wk_settings[wk_is_active]">
                                <input type="hidden" name="wk_settings[wk_is_active]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_is_active]" name="wk_settings[wk_is_active]" value="1" <?php echo esc_attr($wk_is_active ? "checked" : "");?>>
                                <strong>Wallkit Plugin Active</strong>
                            </label>
                            <p>Allows to temporarily disable the plugin while still keeping the plugin settings</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_r_key]">Public API Key</label>
                            <input type="text" id="wk_settings[wk_r_key]" name="wk_settings[wk_r_key]" value="<?php echo esc_attr($wk_r_key); ?>">
                            <p>You can get these in Wallkit > Resource settings screen</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_rs_key]">Private API Key</label>
                            <input type="text" id="wk_settings[wk_rs_key]" name="wk_settings[wk_rs_key]" value="<?php echo esc_attr($wk_rs_key); ?>">
                            <p>You can get these in Wallkit > Resource settings screen</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_is_auto_sync]">
                                <input type="hidden" name="wk_settings[wk_is_auto_sync]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_is_auto_sync]" name="wk_settings[wk_is_auto_sync]" value="1" <?php echo esc_attr($wk_is_auto_sync ? "checked" : "");?>>
                                Sync when content gets updated
                            </label>
                            <p>If enabled, the Wallkit plugin would automatically push data to Wallkit about any changes & updates in the website content</p>

                            <p>Select taxonomies to be synchronized to Wallkit.
                                <br>For successful sync, once you enable taxonomies, please save posts related to this taxonomy.
                                <br>The new posts will automatically be synced between your site and Wallkit.</p>
                            <ul class="wk-post-taxonomies-list" style="columns: 2; -webkit-columns: 2; -moz-columns: 2; ">
                                <?php
                                $taxonomies = get_taxonomies([], 'objects');

                                foreach ($taxonomies as $taxonomy) : ?>
                                    <li>
                                        <label for="wk_settings[wk_check_taxonomies_sync][<?php esc_attr_e($taxonomy->name); ?>]">
                                            <input type="hidden" name="wk_settings[wk_check_taxonomies_sync][<?php esc_attr_e($taxonomy->name); ?>]" value="0" />
                                            <input type="checkbox" id="wk_settings[wk_check_taxonomies_sync][<?php esc_attr_e($taxonomy->name); ?>]" name="wk_settings[wk_check_taxonomies_sync][<?php esc_attr_e($taxonomy->name); ?>]" value="1" <?php echo (isset($wk_check_taxonomies_sync[$taxonomy->name]) ? esc_attr($wk_check_taxonomies_sync[$taxonomy->name] ? "checked" : "") : ""); ?> >
                                            <?php _e($taxonomy->label . ' (<b>' . $taxonomy->name . '</b>)'); ?>
                                        </label>
                                    </li>
                                <?php endforeach ?>
                            </ul>

                        </div>

                        <div class="form-field">
                            <br>
                            <label for="wk_settings[wk_check_post_access]">
                                <input type="hidden" name="wk_settings[wk_check_post_access]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_check_post_access]" name="wk_settings[wk_check_post_access]" value="1" <?php echo esc_attr($wk_check_post_access ? "checked" : ""); ?>>
                                <b>Access restriction on post types</b>
                            </label>
                            <p>If checked — access rules to selected post types can be defined in Wallkit > Plans & Pricing. If you want to avoid applying access rules for some posts, please keep them unchecked. <br>Additional settings will be displayed after saving.</p>

                            <?php if($wk_check_post_access) : ?>
                                <ul class="wk-post-types-list" style="columns: 2; -webkit-columns: 2; -moz-columns: 2; ">
                                    <?php
                                    $post_types = get_post_types();

                                    foreach ($post_types as $post_type) : ?>
                                        <li>
                                            <label for="wk_settings[wk_check_post_type_access][<?php esc_attr_e($post_type); ?>]">
                                                <input type="hidden" name="wk_settings[wk_check_post_type_access][<?php esc_attr_e($post_type); ?>]" value="0" />
                                                <input type="checkbox" id="wk_settings[wk_check_post_type_access][<?php esc_attr_e($post_type); ?>]" name="wk_settings[wk_check_post_type_access][<?php esc_attr_e($post_type); ?>]" value="1" <?php echo (isset($wk_check_post_type_access[$post_type]) ? esc_attr($wk_check_post_type_access[$post_type] ? "checked" : "") : ""); ?> >
                                                <?php _e($post_type); ?>
                                            </label>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <?php if($wk_check_post_access) : ?>
                            <div class="form-field">
                                <label for="wk_settings[wk_content_key_prefix]">Content key prefix</label>
                                <input type="text" id="wk_settings[wk_content_key_prefix]" name="wk_settings[wk_content_key_prefix]" placeholder="" value="<?php echo esc_attr($wk_content_key_prefix); ?>">
                                <p>Prefix for content ID for sync with wallkit.</p>
                            </div>

                            <div class="form-field">
                                <label for="wk_settings[wk_admin_paywall]">
                                    <input type="hidden" name="wk_settings[wk_admin_paywall]" value="0" />
                                    <input type="checkbox" id="wk_settings[wk_admin_paywall]" name="wk_settings[wk_admin_paywall]" value="1" <?php echo esc_attr($wk_admin_paywall ? "checked" : "");?>>
                                    <b>Show paywall for admin</b>
                                </label>
                                <p>Show paywall for logged in users in admin panel</p>
                            </div>

                            <div class="form-field">
                                <label for="wk_settings[wk_show_blur]">
                                    <input type="hidden" name="wk_settings[wk_show_blur]" value="0" />
                                    <input type="checkbox" id="wk_settings[wk_show_blur]" name="wk_settings[wk_show_blur]" value="1" <?php echo esc_attr($wk_show_blur ? "checked" : ""); ?>>
                                    Blur content
                                </label>
                                <p>Use the “blurred content” effect underneath the “Access denied” message <br />
                                    If "Use custom integration" is checked, it will add a class to the content.
                                </p>
                            </div>



                            <div class="form-field">
                                <b>Choose the way to block content on the website:</b>
                                <p></p>
                                <input type="hidden" name="wk_settings[wk_paywall_display_type]" value="0" />
                                <div>
                                    <label for="wk_paywall_display_type_css">
                                        <input type="radio" id="wk_paywall_display_type_css" name="wk_settings[wk_paywall_display_type]" value="0" <?php checked($wk_paywall_display_type, 0);?> >
                                        Frontend (by CSS)</label>
                                </div>

                                <div>
                                    <label for="wk_paywall_display_type_frontend">
                                        <input type="radio" id="wk_paywall_display_type_frontend" name="wk_settings[wk_paywall_display_type]" value="1" <?php checked($wk_paywall_display_type, 1);?> >
                                        Frontend (by JavaScript)</label>
                                </div>

                                <div>
                                    <label for="wk_paywall_display_type_backend">
                                        <input type="radio" id="wk_paywall_display_type_backend" name="wk_settings[wk_paywall_display_type]" value="3" <?php checked($wk_paywall_display_type, 3);?> >
                                        Backend</label>
                                </div>

                                <div>
                                    <label for="wk_paywall_display_type_disable">
                                        <input type="radio" id="wk_paywall_display_type_disable" name="wk_settings[wk_paywall_display_type]" value="2" <?php checked($wk_paywall_display_type, 2);?> >
                                        Disabled</label>
                                </div>
                            </div>

                            <?php if((int) $wk_paywall_display_type === 1) : ?>
                            <div class="form-field">
                                <label for="wk_settings[wk_content_class_selector]">Frontend content container class</label>
                                <input type="text" id="wk_settings[wk_content_class_selector]" name="wk_settings[wk_content_class_selector]" placeholder="wkwp-post-content" value="<?php echo esc_attr($wk_content_class_selector); ?>">
                                <p>You can replace default class (wkwp-post-content) on your own.</p>
                            </div>
                            <div class="form-field">
                                <label for="wk_settings[wk_custom_content_selector]">Frontend custom content container selector</label>
                                <input type="text" id="wk_settings[wk_custom_content_selector]" name="wk_settings[wk_custom_content_selector]" placeholder="" value="<?php echo esc_attr($wk_custom_content_selector); ?>">
                                <p>If class from the field above not pushed to the page, set the content container selector that exist on the page.</p>
                            </div>
                            <?php endif; ?>

                        <?php endif; ?>

                        <div class="form-field">
                            <label for="wk_settings[wk_free_paragraph]">Show the number of paragraphs</label>
                            <input type="number" id="wk_settings[wk_free_paragraph]" name="wk_settings[wk_free_paragraph]" value="<?php echo esc_attr($wk_free_paragraph); ?>">
                            <p>Number of paragraphs accessible before paywalled content.</p>
                        </div>

                        <div class="form-field">
                            <label for="wk_settings[wk_custom_integration]">
                                <input type="hidden" name="wk_settings[wk_custom_integration]" value="0" />
                                <input type="checkbox" id="wk_settings[wk_custom_integration]" name="wk_settings[wk_custom_integration]" value="1" <?php echo esc_attr($wk_custom_integration ? "checked" : ""); ?>>
                                Use custom integration
                            </label>
                            <p>If you initialize Wallkit integration from your code, check this option. Default initialization will be disabled.</p>
                        </div>

                        <?php if( !$wk_custom_integration ) : ?>
                            <div class="form-field">
                                <label for="wk_settings[wk_analytics]">
                                    <input type="hidden" name="wk_settings[wk_analytics]" value="0" />
                                    <input type="checkbox" id="wk_settings[wk_analytics]" name="wk_settings[wk_analytics]" value="1" <?php echo esc_attr($wk_analytics ? "checked" : ""); ?>>
                                    Analytics
                                </label>
                                <p>Enable analytics parse UTM</p>
                            </div>

                            <div class="form-field">
                                <label for="wk_settings[wk_sign_in_button]">
                                    <input type="hidden" name="wk_settings[wk_sign_in_button]" value="0" />
                                    <input type="checkbox" id="wk_settings[wk_sign_in_button]" name="wk_settings[wk_sign_in_button]" value="1" <?php echo esc_attr($wk_sign_in_button ? "checked" : ""); ?>>
                                    Display Sign-in button
                                </label>
                                <p>Will display a sign-in button template <a href="?page=wallkit-Appearance#tab-2">configured here</a> that allows users to sign in/sign up on-site.</p>
                            </div>

                            <div class="form-field">
                                <label for="wk_settings[wk_reload_on_logout]">
                                    <input type="hidden" name="wk_settings[wk_reload_on_logout]" value="0" />
                                    <input type="checkbox" id="wk_settings[wk_reload_on_logout]" name="wk_settings[wk_reload_on_logout]" value="1" <?php echo esc_attr($wk_reload_on_logout ? "checked" : ""); ?>>
                                    Reload the page after logging out
                                </label>
                                <p>If checked — the page will automatically reload once the user logs out.</p>
                            </div>

                            <div class="form-field">
                                <div><p>Select menus where display <b>Sign In</b> button:</p></div>
                                <ul class="wk-account-button-menu-list" style="columns: 2; -webkit-columns: 2; -moz-columns: 2; ">
                                    <?php
                                    $menus = get_terms( 'nav_menu' );

                                    foreach ($menus as $menu) : ?>
                                        <li>
                                            <label for="wk_settings[wk_nav_menu_sign_in_button][<?php esc_attr_e($menu->slug); ?>]">
                                                <input type="hidden" name="wk_settings[wk_nav_menu_sign_in_button][<?php esc_attr_e($menu->slug); ?>]" value="0" />
                                                <input type="checkbox" id="wk_settings[wk_nav_menu_sign_in_button][<?php esc_attr_e($menu->slug); ?>]" name="wk_settings[wk_nav_menu_sign_in_button][<?php esc_attr_e($menu->slug); ?>]" value="1" <?php echo (isset($wk_nav_menu_sign_in_button[$menu->slug]) ? esc_attr($wk_nav_menu_sign_in_button[$menu->slug] ? "checked" : "") : ""); ?> >
                                                <?php _e($menu->name . ' (<b>' . $menu->slug . '</b>)'); ?>
                                            </label>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif; ?>

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
