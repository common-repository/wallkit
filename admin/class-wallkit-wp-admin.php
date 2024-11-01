<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Admin extends Wallkit_Wp_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

    /**
     * @var
     */
	private $plugin_title;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.17
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * @var Wallkit_Wp_Loader
     */
	protected $loader;

    /**
     * @var Wallkit_Wp_Templates
     */
	protected $templates;

    /**
     * @var \WallkitSDK\WallkitSDK
     */
	public $wallkitSDK;

    /**
     * @var Wallkit_Wp_Admin_Posts
     */
	protected $Wallkit_Wp_Admin_Posts;

    /**
     * @var Wallkit_Wp_Charts
     */
	protected $Wallkit_Wp_Charts;

    /**
     * @var Wallkit_Wp_Collection
     */
	protected $collection;

    /**
     * @var null|Wallkit_Wp_Access
     */
	protected $wallkit_Wp_Access = null;

    /**
     * Wallkit_Wp_Admin constructor.
     *
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     */
	public function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection ) {

        $this->plugin_name      = $wallkit_Wp_Collection->get_plugin_name();

        $this->plugin_title     = $wallkit_Wp_Collection->get_plugin_title();

        $this->version          = $wallkit_Wp_Collection->get_version();

        $this->collection       = $wallkit_Wp_Collection;

        $this->set_loader($wallkit_Wp_Collection->get_loader());

        $this->set_wallkit_sdk($this->collection->get_settings()->get_sdk());

        $this->set_templates(new Wallkit_Wp_Templates($this->collection ));

        $this->Wallkit_Wp_Admin_Posts = new Wallkit_Wp_Admin_Posts($this->collection);

        $this->Wallkit_Wp_Charts = new Wallkit_Wp_Charts($this->collection);

        $this->wallkit_Wp_Access = Wallkit_Wp_Access::getInstance($this->collection);

	}

    /**
     * @param Wallkit_Wp_Loader $loader
     */
	public function set_loader( Wallkit_Wp_Loader $loader = NULL) {
        $this->loader = $loader;
    }

    /**
     * @param Wallkit_Wp_Templates|NULL $templates
     */
    public function set_templates( Wallkit_Wp_Templates $templates = NULL) {
        $this->templates = $templates;
    }

    /**
     * @param \WallkitSDK\WallkitSDK|NULL $wallkitSDK
     */
    public function set_wallkit_sdk( \WallkitSDK\WallkitSDK $wallkitSDK = NULL) {

        if($wallkitSDK instanceof \WallkitSDK\WallkitSDK)
        {
            $this->wallkitSDK = $wallkitSDK;
        }
    }

    /**
     * @return null|\WallkitSDK\WallkitSDK
     */
    public function get_wallkit_sdk( ) {

        if($this->wallkitSDK instanceof \WallkitSDK\WallkitSDK)
        {
            return $this->wallkitSDK;
        }
        return NULL;
    }

    /**
     *
     */
    public function load_admin_dependencies() {

        $this->admin_updates();
    }


    /**
     *
     */
    public function admin_hooks() {}

	/**
	 * Register the stylesheets for the admin area.
	 ** This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallkit_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallkit_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
     **
	 * @since    1.1.17
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '/css/wallkit-wp-admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.1.17
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallkit_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallkit_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script('jquery-ui-core', array( 'jquery' ));// enqueue jQuery UI Core
        wp_enqueue_script('jquery-ui-tabs', array( 'jquery' ));// enqueue jQuery UI Tabs

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '/js/wallkit-wp-admin.min.js', array( 'jquery' ), $this->version, false );

		if( $hook === 'wallkit_page_wallkit-Appearance') {
            wp_localize_script($this->plugin_name, 'codemirror_paywall_styles', ['codeEditor' => wp_enqueue_code_editor( array('type' => 'text/css') ) ]);
        }
		elseif ( $hook === 'wallkit_page_wallkit-settings') {
		    if( isset($_GET['tab']) ) {
		        switch($_GET['tab']) {
                    case 'integration-settings':
                        wp_localize_script($this->plugin_name, 'codemirror_additional_options', ['codeEditor' => wp_enqueue_code_editor( array('type' => 'application/json') ) ]);
                        break;
                    case 'additional-script':
                        wp_localize_script($this->plugin_name, 'codemirror_additional_options', ['codeEditor' => wp_enqueue_code_editor( array('type' => 'text/javascript') ) ]);
                        break;
                }
            }
        }

    }

    /**
     *
     */
	protected function admin_updates() {}

    /**
     *
     */
    public function admin_menu() {

	    $this->loader->add_menu($this->plugin_title, $this->plugin_title, 'manage_options',  __FILE__, [$this->templates, Wallkit_Wp_Templates::WALLKIT_PAGES["main"]], plugin_dir_url(__FILE__).'images/WK-Icon.png');

        $this->loader->add_sub_menu(__FILE__, $this->plugin_title.' - Settings', 'Settings', 'manage_options', 'wallkit-settings', [$this->templates,  Wallkit_Wp_Templates::WALLKIT_PAGES["wallkit-settings"]]);

        $this->loader->add_sub_menu(__FILE__, $this->plugin_title.'- Appearance', 'Appearance', 'manage_options', 'wallkit-Appearance', [$this->templates,  Wallkit_Wp_Templates::WALLKIT_PAGES["wallkit-Appearance"]]);

        if( !$this->collection->get_settings()->get_option("wk_custom_integration", false) ) {
            $this->loader->add_sub_menu(__FILE__, $this->plugin_title . '- Advanced', 'Advanced', 'manage_options', 'wallkit-advanced', [$this->templates, Wallkit_Wp_Templates::WALLKIT_PAGES["wallkit-advanced"]]);
        }
    }

    /**
     *
     */
    public function wpwkp_chart_analytic() {
        wp_send_json($this->Wallkit_Wp_Charts->get_activity());
        wp_die();
    }

    /**
     * @param $post_ID
     * @param $post
     * @param $update
     * @throws Exception
     * @throws Wallkit_Wp_Content_Exception
     */
    public function action_post_save($post_ID, $post, $update)
    {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_autosave($post_ID) || $post->post_status !== "publish") {
            return;
        }

        $wallkit_content = $this->Wallkit_Wp_Admin_Posts->get_post($post);
        if (!empty($wallkit_content['error'])) {
	        $update = false;
        }
        else
        {
            $update = true;
        }

        if($update)
        {
            try {
                $this->Wallkit_Wp_Admin_Posts->updatedPost($post_ID, $post);
            }
             catch (\WallkitSDK\Exceptions\WallkitApiException $exception)
             {
                 if(false !== (strpos($exception->getMessage(), 'not found')))
                 {
                     return $this->action_post_save($post_ID, $post, false);
                 }
             }
             catch (\Exception $exception)
             {
                 //Deprecated log statement
             }
        }
        else
        {
            $this->Wallkit_Wp_Admin_Posts->createPost($post_ID, $post);
        }

    }

    /**
     * @param $post_ID
     */
    public function action_post_delete($post_ID)
    {
        $this->Wallkit_Wp_Admin_Posts->deletePost($post_ID);
    }

    /**
     *
     */
    public function action_create_task() {

        $this->disable_cache();

        $this->set_task([
            "status" => "queued"
        ]);

        try
        {
            $this->Wallkit_Wp_Admin_Posts->run_sync_posts();
        }
        catch (\Exception $exception)
        {
            $this->update_task([
                "status" => "exception"
            ]);
        }
    }

    /**
     *
     */
    public function action_continue_task() {

        $this->disable_cache();

        $this->update_task([
            "status" => "continue"
        ]);

        try
        {
            $this->Wallkit_Wp_Admin_Posts->run_sync_posts();
        }
        catch (\Exception $exception)
        {
            $this->update_task([
                "status" => "exception"
            ]);
        }
    }

    /**
     *
     */
    public function wpwkp_run_sync_task() {

        $this->disable_cache();

        try
        {
            $status = wp_schedule_single_event( time() + 1, 'wpwkp_task_create' );
        }
        catch (\Exception $exception)
        {
            //Deprecated log statement
        }

        $task_body = $this->collection->get_settings()->set_task([
            "status" => "queued"
        ]);

        wp_send_json($task_body);
        wp_die();
    }

    /**
     *
     */
    public function wpwkp_continue_sync_task() {

        $this->disable_cache();

        try
        {
            $status = wp_schedule_single_event( time() + 1, 'wpwkp_task_continue' );
        }
        catch (\Exception $exception)
        {
            //Deprecated log statement
        }

        $task_body = $this->collection->get_settings()->update_task([
            "status" => "continue",
            "last_time" => time()
        ]);

        wp_send_json($task_body);
        wp_die();
    }

    /**
     *
     */
    public function wpwkp_stop_sync_task() {

        $this->disable_cache();

        $task_body = $this->collection->get_settings()->update_task([
            "status" => "stop",
            "last_time" => time()
        ]);

        wp_send_json($task_body);
        wp_die();
    }

    /**
     *
     */
    public function wpwkp_pause_sync_task() {

        $this->disable_cache();

        $task_body = $this->collection->get_settings()->update_task([
            "status" => "pause",
            "last_time" => time()
        ]);

        wp_send_json($task_body);
        wp_die();
    }

    /**
     *
     */
    public function wpwkp_check_sync_task() {
        wp_send_json(array_merge($this->collection->get_settings()->get_task(), []));
        wp_die();
    }

    /**
     * @param $content
     * @return string
     */
    public function filter_content($content) {

        if(!$this->wallkit_Wp_Access->check_post_access(get_post())) {
            $paywallType = intval($this->collection->get_settings()->get_option("wk_paywall_display_type"));
            /**
             * Filter to disable modal on certain posts
             */
            if (apply_filters('disable_wallkit_locked_content_paywall', false, get_post())) {
                return $content;
            }

            // access deny
            if(is_singular()) {
                switch ($paywallType) {
                    case 0: $content = $this->get_hard_css_paywalled($content); break;
                    case 1: $content = $this->get_frontend_paywalled($content); break;
                    case 3: $content = $this->get_backend_paywalled($content); break;
                }
            }
        }

        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    public function has_inline_popup($content) {
        if(has_shortcode( $content, 'wk-account-page' )) {
            add_filter('wallkit_override_integration_settings', array($this, 'update_wallkit_popup_settings'), 11, 1);
        }

        return $content;
    }

    public function update_wallkit_popup_settings($settings) {
        $settings['integration']['ui'] = [
                'type' => 'inline',
                'selector' => '#inline-user-popup-modal',
        ];
        return $settings;
    }

    /**
     * Get not locked preview content displayed for user
     *
     * @param $content
     * @param int $cut_paragraph_count
     * @return string
     */
    private function get_content_intro_paragraph($content, $cut_paragraph_count = 1) {
        if($cut_paragraph_count < 0) {
            return force_balance_tags( apply_filters('wallkit_customize_post_preview_content', $content) );
        }

        $parts = explode("</p>", $content);
        $output = '';
        foreach($parts AS $k => $paragraph)
        {
            if($k >= $cut_paragraph_count) break;

            $output .= $paragraph.'</p>';
        }
        unset($parts);

        return force_balance_tags( apply_filters('wallkit_customize_post_preview_content', $output) );
    }

    /**
     * Get locked content not displayed for user
     *
     * @param $content
     * @param int $cut_paragraph_count
     * @return string
     */
    public static function get_content_body_paragraph($content, $cut_paragraph_count = 1) {
        if($cut_paragraph_count < 0) {
            return force_balance_tags( apply_filters('wallkit_customize_post_locked_content', '') );
        }

        $parts = explode("</p>", $content);
        $output = '';
        foreach($parts AS $k => $paragraph)
        {
            if($k >= $cut_paragraph_count) {
                $output .= $paragraph . '</p>';
            }
        }
        unset($parts);

        return force_balance_tags( apply_filters('wallkit_customize_post_locked_content', $output) );
    }

    /**
     * Get locked content not displayed for user
     *
     * @since 3.3.7
     * @param $content
     * @param int $cut_paragraph_count
     * @return string
     */
    private function get_content_body_paragraphs_count($content, $cut_paragraph_count = 1) {
        if($cut_paragraph_count < 0) {
            return force_balance_tags( apply_filters('wallkit_customize_post_locked_content', '') );
        }

        $parts = count(explode("</p>", $content));

        return force_balance_tags( apply_filters('wallkit_customize_post_locked_paragraphs_count', $parts-1) );
    }

    /**
     * @param $content
     * @return int
     */
    private function get_count_paragraphs($content) {
        return count(explode("</p>", strip_shortcodes(strip_shortcodes($content))));
    }

    /**
     * Paywalled and blocked content on backend
     * @param $content
     * @return string
     */
    private function get_hard_css_paywalled($content) {
        $cut_paragraph_count = $this->collection->get_settings()
            ->get_option("wk_free_paragraph", 1);

        $source_content = $this->get_formatted_content($content);
        $content = '<div class="wpwp-non-paywall">' . $this->get_content_intro_paragraph($source_content, $cut_paragraph_count) . '</div>';

        $content .= '<div class="wkwp-paywall">';
        $content .= '<div class="wkwp-paywall-block">';
        $content .= force_balance_tags(wpautop(base64_decode($this->collection->get_settings()
            ->get_option("wk_content_access_html"))));
        $content .= '</div>';

        if ($this->collection->get_settings()
            ->get_option("wk_show_blur")) {
            $content .= '<div class="wkwp-content-inner wkwp-content-blured">';
        } else {
            $content .= '<div class="wkwp-content-inner">';
        }

        $content .= self::get_content_body_paragraph($source_content, $cut_paragraph_count);

        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Paywalled and blocked content on backend
     *
     * @since 3.3.7
     * @param $content
     * @return string
     */
    private function get_backend_paywalled($content) {
        $cut_paragraph_count = $this->collection->get_settings()
            ->get_option("wk_free_paragraph", 1);

        $source_content = $this->get_formatted_content($content);
        $content = '<div class="wpwp-non-paywall">' . $this->get_content_intro_paragraph($source_content, $cut_paragraph_count) . '</div>';

        $content .= '<div class="wkwp-paywall">';
        $content .= '<div class="wkwp-paywall-block">';
        $content .= force_balance_tags(wpautop(base64_decode($this->collection->get_settings()
            ->get_option("wk_content_access_html"))));
        $content .= '</div>';

        if ($this->collection->get_settings()
            ->get_option("wk_show_blur")) {
            $count_paragraps = $this->get_content_body_paragraphs_count($source_content, $cut_paragraph_count);
            $content .= '<div class="wkwp-content-inner wkwp-content-blured" data-paragraphs="' . $count_paragraps . '">';
        } else {
            $content .= '<div class="wkwp-content-inner">';
        }

        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Paywalled and blocked content on frontend
     *
     * @param $content
     * @return string
     */
    private function get_frontend_paywalled($content) {
        $result_content = $content;
        if( empty($this->collection->get_settings()->get_custom_content_selector()) ) {
            $result_content = '<div class="' . $this->collection->get_settings()->get_content_class_selector() . '">' . PHP_EOL . PHP_EOL
                . $content . PHP_EOL. PHP_EOL
                . '</div>';
        }

        return force_balance_tags( apply_filters('wallkit_customize_post_frontend_wrapping', $result_content) );
    }

    /**
     * Formatting content before split on parts
     *
     * @since   3.3.4
     *
     * @param   string  $content    The text which has to be formatted.
     * @return  string
     */
    public static function get_formatted_content($content) {
        $blocks = parse_blocks( $content );
        $output = '';

        foreach ( $blocks as $block ) {
            $output .= render_block( $block );
        }

        // Returning formatted content if it contains gutenberg blocks
        if( has_blocks( $content ) ) {
            return $output;
        }

        //If content do not contains gutenberg blocks format it with wpautop function.
        return wpautop($content);
    }


    /**
     * @param int $post_id
     */
    public function wk_post_reports_file_download_start($post_id = 0) {
        $WkPostActions = new WkPostActions();
        $WkPostActions->downloadPost($post_id);
    }

    /**
     *
     */
    public function action_add_meta_box( ) {

        $selectedPostTypes = [];
        $registeredPostTypes = $this->collection->get_settings()->get_option("wk_check_post_type_access");
        if( !empty($registeredPostTypes) && $this->collection->get_settings()->get_option("wk_check_post_access") )
        {
            $selectedPostTypes = array_keys( array_filter($registeredPostTypes, function($post_type) { return $post_type; }) );
        }

        if(!$selectedPostTypes) {
            return;
        }

        add_meta_box( "wallkit-post-settings", "Wallkit Content", function(WP_Post $WP_Post) {
            wp_nonce_field( basename( __FILE__ ), 'wkwp_meta_box_nonce' );
            ?>
            <table>
            <?php
            $Content = $this->Wallkit_Wp_Admin_Posts->get_post($WP_Post);
            $Statistic = $this->Wallkit_Wp_Admin_Posts->get_post_statistic($WP_Post);

            if(isset($Content["error"]) && $Content["error"])
            {
                ?>
                <tr>
                    <td colspan="2">
                        <h4 class="warning"> <?php echo esc_html($Content["message"]); ?></h4>
                    </td>
                </tr>
                <?php
            }
            else {
                ?>

                <tr>
                    <td>Content Created:</td>
                    <td><b><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $Content["created_at"] ) ) ); ?></b></td>
                </tr>
                <tr>
                    <td>Content Updated:</td>
                    <td><b><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $Content["updated_at"] ) ) ); ?></b></td>
                </tr>

                <?php
            }

            $disablePaywall = get_post_meta($WP_Post->ID, 'disable_paywall_on_post', true);
            ?>

                <tr>
                    <td>Disable paywall:</td>
                    <td>
                        <input type="hidden" name="disable_paywall_on_post" value="0" />
                        <input type="checkbox" name="disable_paywall_on_post" value="1" <?php checked( $disablePaywall, '1' ); ?>>
                    </td>
                </tr>

            </table>
            <?php

        }, $selectedPostTypes, "side", "high", NULL );
    }

    public function wkwp_meta_box_save( $post_id ) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_autosave($post_id) ) {
            return;
        }

        // verify nonce
        if (!isset($_POST['wkwp_meta_box_nonce']) || !wp_verify_nonce($_POST['wkwp_meta_box_nonce'], basename(__FILE__))) {
            return;
        }

        if ( isset($_POST['disable_paywall_on_post']) ) {
            update_post_meta( $post_id, 'disable_paywall_on_post', $_POST['disable_paywall_on_post'] );
        }
    }

    /**
     *
     */
    public function add_editor_style() {

        if($this->templates->isWallkitPage())
        {
            add_action("admin_print_footer_scripts", function() {
                if(wp_script_is("quicktags"))
                {
                    ?>
                    <script type="text/javascript">
                    <?php

                        $template_list = $this->collection->get_helper()->get_popups();

                        foreach($template_list AS $key => $template) {

                    ?>
                        let wk_key = decodeURIComponent( '<?php echo rawurlencode( (string) $key ); ?>' );
                        let template = decodeURIComponent( '<?php echo rawurlencode( (string) $template ); ?>' );
                        QTags.addButton(
                          "wk-" + wk_key,
                          "Button " + template,
                          function () {
                            QTags.insertContent('<button class="btn-access-request" onclick="wk.modal('+ wk_key +');">Subscribe</button>');
                          }
                        );

                    <?php
                        }
                    ?>


                    </script>
                    <?php
                }
            });

            add_filter( 'tiny_mce_before_init', function($in) {

                $in['valid_children']="+a[div|p|ul|ol|li|h1|span|h2|h3|h4|h5|h5|h6]";
                $in[ 'force_p_newlines' ] = FALSE;
                $in[ 'remove_linebreaks' ] = FALSE;
                $in[ 'force_br_newlines' ] = FALSE;
                $in[ 'remove_trailing_nbsp' ] = FALSE;
                $in[ 'apply_source_formatting' ] = FALSE;
                $in[ 'convert_newlines_to_brs' ] = FALSE;
                $in[ 'verify_html' ] = FALSE;
                $in[ 'remove_redundant_brs' ] = FALSE;
                $in[ 'validate_children' ] = FALSE;
                $in[ 'forced_root_block' ]= FALSE;

                return $in;
            } );
        }

    }

    /**
     *
     */
    public function global_init() {

        /**
         * Define textdomain to load translation files
         */
        load_plugin_textdomain( 'wallkit', false, '/wallkit/languages' );

        if(defined( 'DOING_CRON' ) && DOING_CRON ){

        }
        else
        {
            $wk_action = filter_input( INPUT_GET, 'wk-action', FILTER_SANITIZE_STRING);
        }



    }

}
