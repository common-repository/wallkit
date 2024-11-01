<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/public
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * @var Wallkit_Wp_Settings
     */
	private $wallkit_Wp_Collection;


    /**
     * Content Key prefix
     */
    private $content_key_prefix='';

    /**
     * @var null|Wallkit_Wp_Access
     */
    protected $wallkit_Wp_Access = null;

    /**
     * The instance of the REST API controller class used to extend the REST API.
     *
     * @since 3.3.7
     *
     */
    public $wk_rest_api;

    /**
     * Wallkit_Wp_Public constructor.
     *
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     */
	public function __construct( Wallkit_Wp_Collection $wallkit_Wp_Collection ) {

	    $this->wallkit_Wp_Collection = $wallkit_Wp_Collection;

        $this->setContentKeyPrefix();

		$this->plugin_name = $wallkit_Wp_Collection->get_plugin_name();

		$this->version = $wallkit_Wp_Collection->get_version();

        $this->wallkit_Wp_Access = Wallkit_Wp_Access::getInstance($this->wallkit_Wp_Collection);
    }

    /**
     * Set Content Key Prefix
     */
    private function setContentKeyPrefix() {
        if (is_multisite()) {
            global $wpdb;
            $this->content_key_prefix = $wpdb->prefix;
        }

        if( !empty($this->wallkit_Wp_Collection->get_settings()->get_option("wk_content_key_prefix")) ) {
            $this->content_key_prefix = $this->wallkit_Wp_Collection->get_settings()->get_option("wk_content_key_prefix") . '_' . $this->content_key_prefix;
        }
    }

    /**
     * Initialize the REST API routes.
     *
     * @since 3.3.7
     *
     */
    public function rest_api_init() {
        $this->wk_rest_api = new Wallkit_REST_Controller();
        $this->wk_rest_api->register_routes();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.1.17
     */
    public function enqueue_styles() {
        if( !apply_filters( 'disable_wallkit_default_setup_integration', false) ) {
            wp_enqueue_style(
                $this->plugin_name . '-google-fonts',
                'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap',
                array(), null
            );
            wp_enqueue_style($this->plugin_name, WPWKP_plugin_url() . '/public/css/wallkit-wp-public.min.css', array(), $this->version, 'all');
        }
    }

    /**
     *  Add paywall custom styles to head
     *
     */
	public function enqueue_paywall_styles() {
        /**
         * Filters allow disable custom styles
         */
	    if( apply_filters( 'disable_wallkit_paywall_custom_styles', false)
            || apply_filters( 'disable_wallkit_locked_content_paywall', false, get_post() ) ) {
	        return;
        }

	    $wk_paywall_styles = base64_decode($this->wallkit_Wp_Collection->get_settings()->get_option("wk_paywall_styles"));
	    if ($wk_paywall_styles) {
	        echo "<style>{$wk_paywall_styles}</style>";
	    }
    }

    /**
     *  Add my_account custom styles to head
     *
     */
	public function enqueue_my_account_styles() {
        /**
         * Filters allow disable my account custom styles
         */
	    if( apply_filters( 'disable_wallkit_my_account_custom_styles', false) ) {
	        return;
        }

	    $wk_my_account_styles = base64_decode($this->wallkit_Wp_Collection->get_settings()->get_option("wk_my_account_styles"));
	    if ($wk_my_account_styles) {
	        echo "<style>{$wk_my_account_styles}</style>";
	    }
    }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.1.17
	 */
	public function enqueue_scripts() {

        switch($this->wallkit_Wp_Collection->get_settings()->get_option("wk_wilmode", "prod")) {
            case "prod" :
                wp_enqueue_script('wallkit-integration', 'https://cdn1.wallkit.net/js/integration/latest/wallkit-integration-library.min.js', array(), $this->version, true);
                break;
            case "dev" :
                wp_enqueue_script('wallkit-integration', 'https://cdn1.dev.wallkit.net/js/integration/latest/wallkit-integration-library.js', array(), $this->version, true);
                break;
            default :
                throw new Wallkit_Wp_SDK_Exception("Unknown server configuration");
                break;
        }

        if( !apply_filters( 'disable_wallkit_default_setup_integration', false) ) {
            wp_enqueue_script($this->plugin_name . '-setup', WPWKP_plugin_url() . '/public/js/wallkit-setup.min.js', array('wallkit-integration'), $this->version, true);

            if($this->wallkit_Wp_Collection->get_settings()->get_option("wk_additional_script", null)) {
                $scriptPlace = (bool) $this->wallkit_Wp_Collection->get_settings()->get_option("wk_additional_script_place", false);
                if($scriptPlace) {
                    add_action( 'wp_head', [$this, 'enqueue_additional_script']);
                } else {
                    add_action( 'wp_footer', [$this, 'enqueue_additional_script']);
                }
            }
        }

        // Adjust script tag attributes.
        add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
            $script_handles = array( 'wallkit-integration', 'wallkit-wp-setup');
            if ( in_array( $handle, $script_handles ) ) {
                // Add defer attribute to the script tags with the src attribute.
                $tag = preg_replace( '/ src=/', ' defer src=', $tag, 1 );
            }
            return $tag;
        }, 10, 3 );
    }

    public function enqueue_additional_script() {
	    ?>
            <script type="text/javascript" id="<?php echo $this->plugin_name . '-setup'; ?>-additional-js">
                let wkSettings = window["wallkitSettings"] || <?php echo json_encode($this->wallkit_Wp_Collection->get_settings()->get_integration_settings() ); ?>;
                let wkTranslations = window["wallkitTranslations"] || <?php echo json_encode($this->wallkit_Wp_Collection->get_settings()->get_script_translations() ); ?>;
                <?php echo base64_decode($this->wallkit_Wp_Collection->get_settings()->get_option('wk_additional_script', null)); ?>
            </script>
        <?php
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_settings() {

        if( !apply_filters( 'disable_wallkit_default_setup_integration', false) ) {
            wp_localize_script($this->plugin_name . '-setup', 'wallkitSettings', $this->wallkit_Wp_Collection->get_settings()->get_integration_settings());
            wp_localize_script($this->plugin_name . '-setup', 'wallkitTranslations', $this->wallkit_Wp_Collection->get_settings()->get_script_translations());
        }
    }

    /**
     * print current Post Data for js
     */
    public function print_post_data() {

        global $post;

        $postData = [];

        if(isset($post->ID) && is_singular())
        {
            $postData = array(
                'data' => array(
                    'id'         => $this->content_key_prefix . $post->ID,
                    'title'      => $post->post_title,
                    'type'       => get_post_type(),
                    'image'      => get_the_post_thumbnail_url(),
                    'taxonomies' => $this->get_post_taxonomies( $post )
                ),
                'config' => array(
                    'check_post'    => false,
                    'show_blur'     => false
                )
            );

            if( !apply_filters('disable_wallkit_locked_content_paywall', false, $post)
                && !$this->wallkit_Wp_Access->check_post_access( $post ) ) {
                $postData['config']['check_post'] = true;

                if($this->wallkit_Wp_Collection->get_settings()->get_option("wk_show_blur")) {
                    $postData['config']['show_blur'] = true;
                }

                $postData['config']['wk_paywall_display_type'] = intval($this->wallkit_Wp_Collection->get_settings()->get_option("wk_paywall_display_type"));
            }

        }

        $postData = apply_filters( 'wallkit_override_global_post_data', $postData);
        ?>
        <script type="text/javascript">
            var wallkitPostData = <?php echo json_encode( $postData ); ?>;
        </script>
        <?php
    }

    public function add_body_class( $classes ) {
        $classes[] = 'wkwp-user-hide';
        if( $this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_use', true) ) {
            if ($this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_status_body', false)) {
                $classes[] = $this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_status', 'wk-call-status-user');
            }

            if ($this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_plans_body', false)) {
                $classes[] = $this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_plans', 'wk-call-status-plans');
            }

            if ($this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_events_body', false)) {
                $classes[] = $this->wallkit_Wp_Collection->get_settings()->get_option('wk_calls_users_events', 'wk-call-status-events');
            }
        }

        return $classes;
    }
    /**
     * Get post taxonomies
     *
     * @param int $post
     * @return array|string
     */
    public function get_post_taxonomies( $post = 0 ) {
        $post = get_post( $post );
        if ( empty( $post ) ) {
            return '';
        }

        $availableTax = $this->wallkit_Wp_Collection->get_settings()->getOptionSyncTaxonomies();

        $taxonomies = $tax_names = [];
        $tax = get_object_taxonomies($post,'object');

        if ( is_countable($tax) ) {
            foreach ($tax as $tax_item) {
                if(!isset($availableTax[$tax_item->name]) || !$availableTax[$tax_item->name]) continue;
                $taxonomies[$tax_item->name] = [
                    'label' => $tax_item->label,
                    'items' => []
                ];
                $tax_names[] = $tax_item->name;
            }
        }

        $terms = wp_get_post_terms($post->ID, $tax_names);

        if ( is_countable($terms) ) {
            foreach ($terms as $term_item) {
                $taxonomies[$term_item->taxonomy]['items'][] = [
                    "term_id" => $this->content_key_prefix . $term_item->term_id,
                    "name" => $term_item->name,
                    "slug" => $term_item->slug,
                ];
            }
        }

        if ( is_countable($taxonomies) ) {
            $taxonomies = array_filter($taxonomies, function($item) {
                return count($item['items']);
            });
        }

        return apply_filters('wallkit_override_post_taxonomies_sync', $taxonomies, $post);
    }

    /**
     * Default login template
     *
     */
    public function add_default_login_part() {
        if( !apply_filters( 'disable_wallkit_default_setup_integration', false) ) {
            echo do_shortcode('[wk_my_account]');
        }
    }

    /**
     * Add nav menu item Sign In button
     *
     * @param $items
     * @param $args
     * @return string
     */
    public function filter_wp_nav_menu_items($items, $args) {
        if( !apply_filters( 'disable_wallkit_default_setup_integration', false) ) {
            $menus = $this->wallkit_Wp_Collection->get_settings()->get_option("wk_nav_menu_sign_in_button");

            $menus = apply_filters('wallkit_nav_menus_sign_in_button', $menus);

            if (isset($args->menu->slug)
                && array_key_exists($args->menu->slug, $menus)
                && $menus[$args->menu->slug]) {
                $accountItem = apply_filters('wallkit_nav_menu_sign_in_button_html', '<li class="wkwp-nav-login-button"><a href="#" class="wkwp-user-my-account-button">Sign In</a></li>');
                $items .= $accountItem;
            }
        }

        return $items;
    }

    /**
     *  replace short code on empty template
     * @return string
     */
    public function empty_box() {
        return "";
    }

    /**
     * replace short code on template my account
     * @return string
     */
    public function my_account() {
        return '<div class="wkwp-login-block wk-call-status-user">' . do_shortcode(base64_decode($this->wallkit_Wp_Collection->get_settings()->get_option("wk_my_account_html"))) . '</div>';
    }

    /**
     * replace short code on template my account
     * @return string
     */
    public function my_account_page($attrs) {
        $args = shortcode_atts(array(
            'modal' => '',
            'id'    => '',
            'class' => ''
        ), $attrs);

        $parts = [];
        if(isset($args['modal']) && !empty($args['modal'])) {
            $parts[] = 'data-modal="' . $args['modal'] . '"';
        }

        if(isset($args['id']) && !empty($args['id'])) {
            $parts[] = 'id="' . $args['id'] . '"';
        }

        if(isset($args['class']) && !empty($args['class'])) {
            $parts[] = 'class="' . $args['id'] . '"';
        }

        return sprintf('<div %1$s></div>', implode(' ', $parts) );
    }

    /**
     * replace short code on template site logo
     *
     * @param $attrs
     * @return string
     */
    public function wk_site_logo($attrs) {
        $args = shortcode_atts(array(
            'class' => ''
        ), $attrs);

        $args['class'] .= ' wkwp-site-logo';
        $siteLogo = '';

        try {
            $resourceLogo = $this->wallkit_Wp_Collection->get_settings()->get_resource_settings()->get_logo();
            if(!empty($resourceLogo)) {
                $siteLogo = sprintf('<img class="%1$s" src="%2$s" alt="Site Logo" width="100" height="30" />', $args['class'], $resourceLogo);
            }
        } catch (\Exception $exception) {
        }

        return apply_filters( 'wallkit_customize_site_logo', $siteLogo);
    }

    /**
     * replace short code on template my account button
     *
     * @param $attrs
     * @return string
     */
    public function my_account_button($attrs) {
        $args = shortcode_atts(array(
            'text' => __('Sign&nbsp;in', 'wallkit'),
            'class' => ''
        ), $attrs);

        $args['class'] .= ' wkwp-user-my-account-button wk-call wk–sign-in';

        $signinButton = sprintf('<a href="#" class="%1$s">%2$s</a>', $args['class'], $args['text']);

        return apply_filters( 'wallkit_customize_my_account_button', $signinButton);
    }

    /**
     * replace short code on template my account image
     *
     * @param $attrs
     * @return string
     */
    public function my_account_img($attrs) {
        $args = shortcode_atts(array(
            'class' => ''
        ), $attrs);

        $args['class'] .= ' wkwp-user-my-account-img wk-call wk–sign-in';

        $logo = sprintf('<img class="%1$s" src="https://www.gravatar.com/avatar/?d=mp" alt="user" />', $args['class']);

        return apply_filters( 'wallkit_customize_my_account_img', $logo);
    }

    /**
     * replace short code on template wallkit
     * @return string
     */
    public function get_user_full_name() {
        $user = $this->wallkit_Wp_Collection->get_settings()->get_sdk()->getUser();
        if($user)
        {
            return $user->get("first_name")." ".$user->get("last_name");
        }
        return "guest";
    }

    /**
     * @param $name
     * @param null $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments = NULL) {
        $name = str_replace("get_user_", "", $name);
        $user = $this->wallkit_Wp_Collection->get_settings()->get_sdk()->getUser();
        if($name && $user)
        {
            return $user->get($name);
        }
        return null;
    }
}
