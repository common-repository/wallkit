<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.1.17
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $shortcode;

    /**
     * @var array
     */
	protected $menu;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.1.17
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		$this->shortcode = array();
		$this->menu = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.1.17
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
        public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

    /**
     * Add a short code to the collection to be registered with WordPress.
     *
     * @since    1.1.17
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     */
	public function add_shortcode( $hook, $component, $callback ) {
		$this->shortcode = $this->add( $this->shortcode, $hook, $component, $callback, null,null );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.1.17
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}


    /**
     * Add immediately filter.
     *
     * @since   3.3.4
     *
     * @param   string               $hook             The name of the WordPress filter that is being registered.
     * @param   object               $component        A reference to the instance of the object on which the filter is defined.
     * @param   string               $callback         The name of the function definition on the $component.
     * @return  void
     */
    public function add_immediately_filter( $hook, $component, $callback ) {
        if($hook = $this->search_filter($hook, $component, $callback ) ) {
            add_filter( ...$hook );
        }
    }

    /**
	 * Remove registered filter.
	 *
	 * @since    3.3.4
     *
	 * @param   string               $hook             The name of the WordPress filter that is being registered.
	 * @param   object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param   string               $callback         The name of the function definition on the $component.
     * @return  void
	 */
	public function remove_filter( $hook, $component, $callback ) {
        if($hook = $this->search_filter($hook, $component, $callback ) ) {
            remove_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority']);
        }
    }

    /**
     * Search registered filter.
     *
     * @since    3.3.4
     *
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @return bool|mixed
     *
     */
	public function search_filter( $hook, $component, $callback ) {
        foreach ($this->filters as $filter) {
            if($filter['hook'] === $hook
                && $filter['component'] === $component
                && $filter['callback'] === $callback) {
                return $filter;
            }

        }

        return false;
    }

    /**
     * @param $page_title
     * @param $menu_title
     * @param $capability
     * @param $menu_slug
     * @param string $function
     * @param string $icon_url
     * @param null $position
     */
	public function add_menu($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    }

    /**
     * @param $parent_slug
     * @param $page_title
     * @param $menu_title
     * @param $capability
     * @param $menu_slug
     * @param string $function
     */
    public function add_sub_menu($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.1.17
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.1.17
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

        foreach ( $this->shortcode as $hook ) {
            add_shortcode( $hook['hook'], array($hook['component'], $hook['callback']) );
        }

    }

}
