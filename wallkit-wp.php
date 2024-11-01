<?php

/**
 * @link              https://wallkit.net/
 * @since             0.1.0
 * @package           Wallkit_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Wallkit
 * Plugin URI:        https://wallkit.net
 * Description:       A Plug & Play paid-content system to manage subscribers, gather fees and drive additional content sales.
 * Version:           3.3.8
 * Author:            Wallkit <dev@wallkit.net>
 * Author URI:        https://wallkit.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.1.17 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPWKP_VERSION', '3.3.8' );

/**
 * @return string
 */
function WPWKP_plugin_url() {
    return plugins_url( '', __FILE__ );
}

/**
 * @var $WallkitPhpSdk \WallkitSDK\WallkitSDK
 */
$WallkitPhpSdk = null;

define( 'WPWKP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'WPWKP_PLUGIN_MODULES_DIR', WPWKP_PLUGIN_DIR . 'modules' );

define( 'WPWKP_INCLUDE_DIR', WPWKP_PLUGIN_DIR . 'includes' );

define( 'WPWKP_TEMPLATE_DIR', WPWKP_PLUGIN_DIR . 'admin/partials' );

define( 'WPWKP_PUBLIC_TEMPLATE_DIR', WPWKP_PLUGIN_DIR . 'public/templates' );

// Deprecated, not used in the plugin core. Use WPWKP_plugin_url() instead.
//define( 'WPWKP_PLUGIN_URL', untrailingslashit( plugins_url( '', WPWKP_PLUGIN ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wallkit-wp-activator.php
 */
function activate_wallkit_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallkit-wp-activator.php';
	Wallkit_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wallkit-wp-deactivator.php
 */
function deactivate_wallkit_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallkit-wp-deactivator.php';
	Wallkit_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wallkit_wp' );
register_deactivation_hook( __FILE__, 'deactivate_wallkit_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wallkit-wp.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.17
 */
function run_wallkit_wp(&$WallkitPhpSdk) {

	$plugin = new Wallkit_Wp();
	$plugin->run();

    $WallkitPhpSdk = $plugin->get_wallkit_sdk();
}

run_wallkit_wp($WallkitPhpSdk);

