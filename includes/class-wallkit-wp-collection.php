<?php
/**
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Collection {
    /**
     * @var Wallkit_Wp_Admin
     */
    private $wallkit_Wp_Admin;

    /**
     * @var Wallkit_Wp_Settings
     */
    private $wallkit_Wp_Settings;

    /**
     * @var Wallkit_Wp_Loader
     */
    private $wallkit_Wp_Loader;

    /**
     * @var Wallkit_Wp_Helper
     */
    private $healper;

    /**
     * Wallkit_Wp_Collection constructor.
     *
     * @param Wallkit_Wp_Settings $wallkit_Wp_Settings
     * @param Wallkit_Wp_Loader $wallkit_Wp_Loader
     */
    public function __construct( Wallkit_Wp_Settings $wallkit_Wp_Settings, Wallkit_Wp_Loader $wallkit_Wp_Loader = NULL )
    {
        $this->wallkit_Wp_Settings = $wallkit_Wp_Settings;

        $this->wallkit_Wp_Loader = $wallkit_Wp_Loader;

        $this->healper = new Wallkit_Wp_Helper($this);
    }

    /**
     * @return mixed
     */
    public function get_version() {
        return $this->wallkit_Wp_Settings->get_version();
    }

    /**
     * @return Wallkit_Wp_Loader
     */
    public function get_loader() {
        return $this->wallkit_Wp_Loader;
    }

    /**
     * @return mixed
     */
    public function get_plugin_name() {
        return $this->wallkit_Wp_Settings->get_plugin_name();
    }

    /**
     * @return mixed
     */
    public function get_plugin_title() {
        return $this->wallkit_Wp_Settings->get_plugin_title();
    }

    /**
     * @return Wallkit_Wp_Settings
     */
    public function get_settings() {
        return $this->wallkit_Wp_Settings;
    }

    /**
     * @return Wallkit_Wp_Admin
     */
    public function get_admin() {
        return $this->wallkit_Wp_Admin;
    }

    /**
     * @return Wallkit_Wp_Helper
     */
    public function get_helper() {
        return $this->healper;
    }

}
