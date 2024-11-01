<?php
/**
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Access {


    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var null|Wallkit_Wp_Collection
     */
    private static $wallkit_Wp_Collection = null;

	/**
	 * Content Key prefix
	 */
	private $content_key_prefix='';

    /**
     * Wallkit_Wp_Access constructor.
     *
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     */
    private function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection) {
        static::$wallkit_Wp_Collection = $wallkit_Wp_Collection;
        $this->setContentKeyPrefix();
        add_filter('wallkit_check_post_access', array($this, 'check_post_access_wk_request'), 10, 2);
    }

	/**
	 * Set Content Key Prefix
	 */
	private function setContentKeyPrefix() {
		if (is_multisite()) {
			global $wpdb;
			$this->content_key_prefix = $wpdb->prefix;
		}

        if( !empty(self::$wallkit_Wp_Collection->get_settings()->get_option("wk_content_key_prefix")) ) {
            $this->content_key_prefix = self::$wallkit_Wp_Collection->get_settings()->get_option("wk_content_key_prefix") . '_' . $this->content_key_prefix;
        }
	}

    /**
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     * @return null|Wallkit_Wp_Access
     */
    public static function getInstance(Wallkit_Wp_Collection $wallkit_Wp_Collection)
    {
        if (null === self::$instance)
        {
            self::$instance = new self($wallkit_Wp_Collection);
        }
        return self::$instance;
    }
    
    /**
     * @return \Wallkit_Wp_Admin_Posts
     */
    public function get_wk_posts() {
        return  new Wallkit_Wp_Admin_Posts(static::$wallkit_Wp_Collection);
    }

    /**
     *
     */
    private function __clone () {}

    /**
     * @param \WP_Post|null $WP_Post
     * @param bool $autoCreate
     * @return bool
     */
    public function check_post_access(WP_Post $WP_Post = null) {

        if(!$WP_Post instanceof WP_Post || !isset($WP_Post->ID))
        {
            return true;
        }

        if(!isset($WP_Post->post_type) || empty($WP_Post->post_type))
        {
            return true;
        }

        $disablePaywallOnPost = get_post_meta($WP_Post->ID, 'disable_paywall_on_post', true);
        if($disablePaywallOnPost === '1')
        {
            return true;
        }

        try {

            /**
             * Disable locked content if user logged in admin area
             */
            if(is_user_logged_in() && !self::$wallkit_Wp_Collection->get_settings()->get_option("wk_admin_paywall"))
            {
                return true;
            }

            if(!self::$wallkit_Wp_Collection->get_settings()->get_option("wk_check_post_access"))
            {
                return true;
            }

            /**
             * Locked content for checked post types
             */
            $registeredPostTypes = self::$wallkit_Wp_Collection->get_settings()->get_option("wk_check_post_type_access");

            return !(!empty($registeredPostTypes) && array_key_exists($WP_Post->post_type, $registeredPostTypes) && $registeredPostTypes[$WP_Post->post_type]);

        }
        catch (\Exception $exception)
        {
            return false;
        }

    }

    /**
     * Check post access in wallkit
     *
     * @since 3.3.7
     * @param \WP_Post|null $WP_Post
     * @param bool $autoCreate
     * @return array|bool
     */
    public function check_post_access_wk_request(WP_Post $WP_Post = null, $autoCreate = true) {

        if(!$WP_Post instanceof WP_Post || !isset($WP_Post->ID))
        {
            return [
                'allow' => true,
                'message' => 'Post ID not exist'
            ];
        }

        if(!isset($WP_Post->post_type) || empty($WP_Post->post_type))
        {
            return [
                'allow' => true,
                'message' => 'Post type not exist'
            ];
        }

        $disablePaywallOnPost = get_post_meta($WP_Post->ID, 'disable_paywall_on_post', true);
        if($disablePaywallOnPost === '1')
        {
            return [
                'allow' => true,
                'message' => 'Paywall disabled on this post'
            ];
        }

        /**
         * Disable locked content if user logged in admin area
         */
        if(is_user_logged_in() && !self::$wallkit_Wp_Collection->get_settings()->get_option("wk_admin_paywall"))
        {
            return [
                'allow' => true,
                'message' => 'Paywall disabled for admin users'
            ];
        }

        try {

            $Sdk = static::$wallkit_Wp_Collection->get_settings()->get_sdk();

            if(!$Sdk instanceof \WallkitSDK\WallkitSDK)
            {
                return [
                    'allow' => true,
                    'message' => 'Wallkit sdk not initialised'
                ];
            }

            /**
             * Locked content for checked post types
             */
            $registeredPostTypes = self::$wallkit_Wp_Collection->get_settings()->get_option("wk_check_post_type_access");
            if( !(!empty($registeredPostTypes) && array_key_exists($WP_Post->post_type, $registeredPostTypes) && $registeredPostTypes[$WP_Post->post_type]) ) {
                return [
                    'allow' => true,
                    'message' => 'Post type allowed'
                ];
            }

            $access =  $Sdk
                ->get("/user/content/" . $this->content_key_prefix . $WP_Post->ID)
                ->toArray();

            return $access;
        }
        catch(\WallkitSDK\Exceptions\WallkitException $exception)
        {
            if($exception->getMessage() !== 'Content not exist') {
                return [
                    'allow' => false,
                    'reason' => $exception->getMessage(),
                    'message' => 'Content check access error'
                ];
            }

            if(self::$wallkit_Wp_Collection->get_settings()->get_option("wk_is_auto_sync") && $autoCreate) {
                try {
                    $this->get_wk_posts()->createPost($WP_Post->ID, $WP_Post);

                    return $this->check_post_access_wk_request($WP_Post, false);
                }
                catch(\Exception $exception) {
                    return [
                        'allow' => false,
                        'reason' => $exception->getMessage(),
                        'message' => 'Sync content error'
                    ];
                }
            }
        }
        catch (\Exception $exception)
        {
            return [
                'allow' => false,
                'reason' => $exception->getMessage(),
                'message' => 'Exception check access error'
            ];
        }
    }
}
