<?php
/**
 * Wallkit resource settings.
 *
 * @author     Wallkit <dev@wallkit.net>
 */

class Wallkit_Wp_Resource_Settings {

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * @var null|object
     */
    private $resourceSettings;
    /**
     * Wallkit_Wp_Resource constructor.
     *
     * @param \WallkitSDK\WallkitSDK $wallkitSDK
     */
    public function __construct($wallkitSDK) {

        $this->wallkitSDK = $wallkitSDK;

        $this->init_resource_settings();
    }

    /**
     * @param $wallkitSDK
     * @return Wallkit_Wp_Resource_Settings|null
     */
    public static function getInstance($wallkitSDK)
    {
        if (null === self::$instance)
        {
            self::$instance = new self($wallkitSDK);
        }
        return self::$instance;
    }

    /**
     * Setup wallkit resource settings for guest
     *
     */
    private function init_resource_settings() {
        try {
            $cacheFlag = get_transient( 'WALLKIT_resource_settings_10mins' );
            $this->resourceSettings = get_option( 'WALLKIT_resource_settings', NULL );

            if( (empty( $cacheFlag ) || empty( $this->resourceSettings ))
                && $this->wallkitSDK instanceof \WallkitSDK\WallkitSDK) {
                $this->resourceSettings = $this
                    ->wallkitSDK
                    ->get("/integrations/resource-settings")
                    ->toObject();

                set_transient( 'WALLKIT_resource_settings_10mins', true, 10 * MINUTE_IN_SECONDS );
                update_option( 'WALLKIT_resource_settings', $this->resourceSettings, false );

            }
        }
        catch (\Exception $exception) {
        }
    }

    /**
     * @return object|null
     */
    public function get_resource_settings() {
        return $this->resourceSettings;
    }

    /**
     * @return string|null
     */
    public function get_logo() {
        return apply_filters( 'wallkit_custom_resource_logo', $this->resourceSettings->logo ?? null);
    }
}