<?php

/**
 * Fired during plugin activation.
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Activator extends Wallkit_Wp_Settings {

    public function __construct() {


    }

	public static function activate() {

        $settings = (array) get_option( parent::SETTINGS_SLUG );

        if(!$settings || count($settings) === 0)
        {
            update_option( parent::SETTINGS_SLUG, (array) parent::default_settings );
        }

        if(!function_exists("curl_init")) {
            throw new \WallkitSDK\Exceptions\WallkitApiException("Curl not installed", [
                "error" => "curl_init"
            ]);
        }

    }

}
