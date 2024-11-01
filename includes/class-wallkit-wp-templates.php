<?php
/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */

class Wallkit_Wp_Templates {

    /**
     * @var Wallkit_Wp_Collection
     */
    protected $collection;
    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * @array pages
     */
    const WALLKIT_PAGES = [
        "main"                  => "main_wallkit_page",
        "wallkit-settings"      => "wallkit_setup_page",
        "wallkit-Appearance"    => "wallkit_configuration_page",
        "wallkit-advanced"      => "wallkit_advanced_page",
    ];


    /**
     * Wallkit_Wp_Templates constructor.
     *
     * @param Wallkit_Wp_Collection|NULL $wallkit_Wp_Collection
     */
    public function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection = NULL) {
        $this->collection = $wallkit_Wp_Collection;
        $this->wallkitSDK = $this->collection->get_settings()->get_sdk();
    }

    /**
     *
     */
    public function main_wallkit_page() {
        include_once  WPWKP_TEMPLATE_DIR . '/template-wallkit-admin-main.php';
    }

    /**
     * Configuration buttons, style, access text
     */
    public function wallkit_configuration_page() {

        if(wp_verify_nonce( isset($_REQUEST['wpnonce']) ? $_REQUEST['wpnonce'] : null, 'wk-nonce' ) && $_POST && is_admin()) {

            if( isset($_POST['restore']) && !empty($_POST['restore']) ) {
                $defaultSettings = $this->collection->get_settings()->get_default_settings();
                switch($_POST['template']) {
                    case 'paywall':
                        $_POST['wk_content_access_html']    = base64_decode($defaultSettings['wk_content_access_html']);
                        $_POST['wk_paywall_styles']         = base64_decode($defaultSettings['wk_paywall_styles']);
                        break;
                    case 'my_account':
                        $_POST['wk_my_account_html']    = base64_decode($defaultSettings['wk_my_account_html']);
                        $_POST['wk_my_account_styles']  = base64_decode($defaultSettings['wk_my_account_styles']);
                        break;
                }
            }
            if( isset($_POST["wk_content_access_html"]) ) {
                $wk_content_access_html = base64_encode(wp_unslash($_POST["wk_content_access_html"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_content_access_html", $wk_content_access_html);
            }

            if( isset($_POST["wk_paywall_styles"]) ) {
                $wk_paywall_styles = base64_encode(wp_unslash($_POST["wk_paywall_styles"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_paywall_styles", $wk_paywall_styles);
            }

            if( isset($_POST["wk_my_account_html"]) ) {
                $wk_my_account_html = base64_encode(wp_unslash($_POST["wk_my_account_html"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_my_account_html", $wk_my_account_html);
            }

            if( isset($_POST["wk_my_account_styles"]) ) {
                $wk_my_account_styles = base64_encode(wp_unslash($_POST["wk_my_account_styles"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_my_account_styles", $wk_my_account_styles);
            }
        }

        $settings = $this->collection->get_settings();

        include_once  WPWKP_TEMPLATE_DIR . '/template-wallkit-admin-configuration.php';

    }

    /**
     * @return bool
     */
    public function isWallkitPage() {

        $page = isset($_REQUEST["page"]) ? sanitize_text_field($_REQUEST["page"]) : null;
        if($page)
        {
            return (bool) array_key_exists($page, static::WALLKIT_PAGES);
        }

        return false;
    }

    /**
     * main setup plugin. api keys, settings
     */
    public function wallkit_setup_page() {

        if(wp_verify_nonce( isset($_REQUEST['wpnonce']) ? $_REQUEST['wpnonce'] : null, 'wk-nonce' ) && $_POST && is_admin()) {
            $args = [
                "wk_is_active"          => FILTER_VALIDATE_INT,
                "wk_server"             => FILTER_SANITIZE_STRING,
                "wk_wilmode"            => FILTER_SANITIZE_STRING,
                "wk_debug"              => FILTER_VALIDATE_INT,
                "wk_r_key"              => FILTER_SANITIZE_STRING,
                "wk_rs_key"             => FILTER_SANITIZE_STRING,
                "wk_is_auto_sync"       => FILTER_VALIDATE_INT,
                "wk_check_taxonomies_sync"  => array(
                    'filter'    => FILTER_VALIDATE_INT,
                    'flags'     => FILTER_FORCE_ARRAY
                ),
                "wk_check_post_access"  => FILTER_VALIDATE_INT,
                "wk_check_post_type_access"  => array(
                    'filter'    => FILTER_VALIDATE_INT,
                    'flags'     => FILTER_FORCE_ARRAY
                ),
                "wk_admin_paywall"      => FILTER_VALIDATE_INT,
                "wk_free_paragraph"     => FILTER_VALIDATE_INT,
                "wk_show_blur"          => FILTER_VALIDATE_INT,
                "wk_paywall_display_type"=> FILTER_VALIDATE_INT,
                "wk_content_key_prefix" => FILTER_SANITIZE_STRING,
                "wk_content_class_selector"=> FILTER_SANITIZE_STRING,
                "wk_custom_content_selector"=> FILTER_SANITIZE_STRING,
                "wk_custom_integration" => FILTER_VALIDATE_INT,
                "wk_analytics"          => FILTER_VALIDATE_INT,
                "wk_sign_in_button"     => FILTER_VALIDATE_INT,
                "wk_reload_on_logout"   => FILTER_VALIDATE_INT,
                "wk_nav_menu_sign_in_button"  => array(
                    'filter'    => FILTER_VALIDATE_INT,
                    'flags'     => FILTER_FORCE_ARRAY
                ),
                "wk_calls_use"          => FILTER_VALIDATE_INT,
                "wk_calls_debug"        => FILTER_VALIDATE_INT,
                "wk_calls_handle_click" => FILTER_SANITIZE_STRING,
                "wk_calls_users_status" => FILTER_SANITIZE_STRING,
                "wk_calls_users_status_body" => FILTER_VALIDATE_INT,
                "wk_calls_users_plans"  => FILTER_SANITIZE_STRING,
                "wk_calls_users_plans_body"  => FILTER_VALIDATE_INT,
                "wk_calls_users_events" => FILTER_SANITIZE_STRING,
                "wk_calls_users_events_body" => FILTER_VALIDATE_INT,
                "wk_additional_script_place" => FILTER_VALIDATE_INT,
            ];

            if(isset($_POST["wk_additional_options"])) {
                $wk_additional_options = base64_encode(wp_unslash($_POST["wk_additional_options"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_additional_options", $wk_additional_options);
            }

            if(isset($_POST["wk_additional_script"])) {
                $wk_additional_script = base64_encode(wp_unslash($_POST["wk_additional_script"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_additional_script", $wk_additional_script);
            }

            $wk_settings = isset($_POST["wk_settings"]) && is_array($_POST["wk_settings"]) ? filter_var_array($_POST["wk_settings"],$args) : null;

            foreach($this->collection->get_settings()->get_default_settings() AS $key => $value)
            {
                if(isset($wk_settings[$key]) && !is_array($wk_settings[$key]))
                {
                    $this->collection->get_settings()->update_option($key, sanitize_text_field($wk_settings[$key]));
                }
                elseif(isset($wk_settings[$key]) && is_array($wk_settings[$key]))
                {
                    $this->collection->get_settings()->update_option($key, (array) $wk_settings[$key]);
                }
            }

        }

        $settings = $this->collection->get_settings();
        include_once  WPWKP_TEMPLATE_DIR . '/template-wallkit-admin-setup.php';
    }

    /**
     * main setup plugin. api keys, settings
     */
    public function wallkit_advanced_page() {

        if(wp_verify_nonce( isset($_REQUEST['wpnonce']) ? $_REQUEST['wpnonce'] : null, 'wk-nonce' ) && $_POST && is_admin()) {
            $args = [
                "wk_auth_migrated_users"         => FILTER_VALIDATE_INT,
                "wk_auth_allow_empty_pass"       => FILTER_VALIDATE_INT,
                "wk_modals_inline_selector"      => FILTER_SANITIZE_STRING,
                "wk_my_account_page_url"         => FILTER_SANITIZE_STRING,
            ];

            $wk_settings = isset($_POST["wk_advanced"]) && is_array($_POST["wk_advanced"]) ? filter_var_array($_POST["wk_advanced"],$args) : null;

            foreach($this->collection->get_settings()->get_default_settings() AS $key => $value)
            {
                if(isset($wk_settings[$key]) && !is_array($wk_settings[$key]))
                {
                    $this->collection->get_settings()->update_option($key, sanitize_text_field($wk_settings[$key]));
                }
                elseif(isset($wk_settings[$key]) && is_array($wk_settings[$key]))
                {
                    $this->collection->get_settings()->update_option($key, (array) $wk_settings[$key]);
                }
            }

            if( isset($_POST["wk_auth_migrated_users_text"]) ) {
                $wk_auth_migrated_users_text = base64_encode(wp_unslash($_POST["wk_auth_migrated_users_text"]));
                $this->collection
                    ->get_settings()
                    ->update_option("wk_auth_migrated_users_text", $wk_auth_migrated_users_text);
            }
        }

        $settings = $this->collection->get_settings();
        include_once  WPWKP_TEMPLATE_DIR . '/template-wallkit-admin-advanced.php';
    }
}
