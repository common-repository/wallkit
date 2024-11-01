<?php
/**
 * The core plugin class.
 *
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      Wallkit_Wp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

    /**
     * @var string
     */
	protected $plugin_title;

    private static $instance;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
     * @var Wallkit_Wp_Settings
     */
	protected $settings;

    /**
     * @var \WallkitSDK\WallkitSDK
     */
	public $WallkitSDK;

    /**
     * @var \WallkitSDK\Wallkit_Wp_Collection
     */
	protected $wallkit_Wp_Collection;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.1.17
	 */
	public function __construct()
    {

        if(!self::$instance instanceof self)
        {
            self::$instance = $this;
        }

		if ( defined( 'WPWKP_VERSION' ) )
		{
            self::$instance->version = WPWKP_VERSION;
		}
		else
        {
            self::$instance->version = '1.1.17-dev';
		}

        self::$instance->plugin_name = 'wallkit-wp';

        self::$instance->plugin_title = 'Wallkit';

        self::$instance->load_dependencies();

        self::$instance->init_classes();

        self::$instance->define_admin_hooks();

        self::$instance->define_public_hooks();

    }

    /**
     * load loader
     */
	private function init_classes() {

        $this->loader = new Wallkit_Wp_Loader();

        $this->settings = new Wallkit_Wp_Settings($this->plugin_name, $this->version, $this->plugin_title);

        $this->wallkit_Wp_Collection = new Wallkit_Wp_Collection(
            $this->settings,
            $this->loader
        );

        if($this->settings->get_option("wk_is_active"))
        {
            $this->init_wallkit_sdk();
        }
    }

    /**
     * init wallkit sdk
     */
	private function init_wallkit_sdk() {

	    try {

            $this->WallkitSDK = $this->wallkit_Wp_Collection->get_settings()->get_sdk();

        }
        catch (\Wallkit_Wp_Main_Exception $exception)
        {}
    }

    /**
     * @return \WallkitSDK\WallkitSDK
     */
    public function get_wallkit_sdk() {

	    if($this->WallkitSDK instanceof \WallkitSDK\WallkitSDK)
        {
            return $this->WallkitSDK;
        }
        return null;
    }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * - Wallkit_Wp_Loader. Orchestrates the hooks of the plugin.
	 * - Wallkit_Wp_Admin. Defines all hooks for the admin area.
	 * - Wallkit_Wp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.1.17
	 * @access   private
	 */
	private function load_dependencies() {

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'exceptions/exception-wallkit-wp-main.php.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'exceptions/exception-wallkit-wp-sdk.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'exceptions/exception-wallkit-wp-content.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-lorem-ipsum.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-messages.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-collection.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-charts.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-resource-settings.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-healper.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-cache.php';

        /**
         * Wallkit php sdk
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/wallkit-php-sdk/src/autoload.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-settings.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-admin-posts.php';

        /**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-templates.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-wp-access.php';

        /**
         * Class of the REST API routes.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallkit-rest-controller.php';


        /**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wallkit-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wallkit-wp-public.php';

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 */
	private function define_admin_hooks() {

        $plugin_admin = new Wallkit_Wp_Admin($this->get_collection());

		$this->loader->add_action( 'init', $plugin_admin, 'global_init' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_init', $plugin_admin, 'load_admin_dependencies' );

        $this->loader->add_action( 'admin_init', $plugin_admin, 'admin_hooks' );

        $this->loader->add_action( 'admin_init', $plugin_admin, 'add_editor_style' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

        if($this->settings->get_option("wk_is_active") && ($plugin_admin->get_wallkit_sdk() instanceof \WallkitSDK\WallkitSDK) ) {
            if ($this->settings->get_option("wk_is_auto_sync")) {
                $this->loader->add_action('save_post', $plugin_admin, 'action_post_save', 10, 3);
                $this->loader->add_action('before_delete_post', $plugin_admin, 'action_post_delete', 10, 3);
            }

            $this->loader->add_filter('the_content', $plugin_admin, 'filter_content', 7);

            $this->loader->add_filter('the_content', $plugin_admin, 'has_inline_popup', 11);

            $this->loader->add_action("add_meta_boxes", $plugin_admin, 'action_add_meta_box');

            $this->loader->add_action("save_post", $plugin_admin, 'wkwp_meta_box_save');

            $this->loader->add_action('wpwkp_task_create', $plugin_admin, 'action_create_task');

            $this->loader->add_action('wpwkp_task_continue', $plugin_admin, 'action_continue_task');

            $this->loader->add_action('wp_ajax_wk_run_sync_task', $plugin_admin, 'wpwkp_run_sync_task');

            $this->loader->add_action('wp_ajax_wk_continue_sync_task', $plugin_admin, 'wpwkp_continue_sync_task');

            $this->loader->add_action('wp_ajax_wk_stop_sync_task', $plugin_admin, 'wpwkp_stop_sync_task');

            $this->loader->add_action('wp_ajax_wk_pause_sync_task', $plugin_admin, 'wpwkp_pause_sync_task');

            $this->loader->add_action('wp_ajax_wk_check_sync_task', $plugin_admin, 'wpwkp_check_sync_task');

            $this->loader->add_action('wp_ajax_wk_chart_analytic', $plugin_admin, 'wpwkp_chart_analytic');
        }
    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wallkit_Wp_Public( $this->get_collection() );

        if($this->settings->get_option("wk_is_active") && $this->get_collection()->get_settings()->get_sdk())
        {
            $this->loader->add_action( 'rest_api_init', $plugin_public, 'rest_api_init' );
            $this->loader->add_action( 'wp_head', $plugin_public, 'print_post_data' );
            $this->loader->add_filter( 'body_class', $plugin_public, 'add_body_class' );
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_settings');
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
            if( $this->settings->get_option("wk_check_post_access") ) {
                $this->loader->add_action('wp_head', $plugin_public, 'enqueue_paywall_styles');
            }

            $this->loader->add_action('wp_head', $plugin_public, 'enqueue_my_account_styles');
            if( $this->settings->get_option("wk_sign_in_button") ) {
                $this->loader->add_action('wp_footer', $plugin_public, 'add_default_login_part');
            }

            if($this->settings->get_option("wk_nav_menu_sign_in_button"))
            {
                $this->loader->add_filter( 'wp_nav_menu_items', $plugin_public, 'filter_wp_nav_menu_items', 10, 2);
            }

            $this->loader->add_shortcode('wk-account-page', $plugin_public, 'my_account_page');
            $this->loader->add_shortcode('wk_my_account', $plugin_public, 'my_account');
            $this->loader->add_shortcode('wk_site_logo', $plugin_public, 'wk_site_logo');
            $this->loader->add_shortcode('wk_my_account_button', $plugin_public, 'my_account_button');
            $this->loader->add_shortcode('wk_my_account_img', $plugin_public, 'my_account_img');
            $this->loader->add_shortcode('wk_full_name', $plugin_public, 'get_user_full_name');
            $this->loader->add_shortcode('wk_first_name', $plugin_public, 'get_user_first_name');
            $this->loader->add_shortcode('wk_last_name', $plugin_public, 'get_user_last_name');
            $this->loader->add_shortcode('wk_email', $plugin_public, 'get_user_email');
            $this->loader->add_shortcode('wk_id', $plugin_public, 'get_user_id');
            $this->loader->add_shortcode('wk_company', $plugin_public, 'get_user_company');
            $this->loader->add_shortcode('wk_job', $plugin_public, 'get_user_job');

            if( $this->settings->get_option("wk_custom_integration") ) {
                add_filter( 'disable_wallkit_default_setup_integration', '__return_true');
            }
        }

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.1.17
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.1.17
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

    /**
     * @return string
     */
	public function get_plugin_title() {
        return $this->plugin_title;
    }

    /**
     * @return Wallkit_Wp_Collection
     */
    public function get_collection() {
	    return $this->wallkit_Wp_Collection;
    }

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.1.17
	 * @return    Wallkit_Wp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.1.17
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
