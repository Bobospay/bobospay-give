<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://bobospay.com/
 * @since             1.0.0
 * @package           Bobospay_Give
 *
 * @wordpress-plugin
 * Plugin Name:       Bobospay Give
 * Plugin URI:        https://https://github.com/Bobospay/bobospay-give.git
 * Description:       This Add-on allows Give to accept donations via the Bobospay payment gateway.
 * Version:           1.0.0
 * Author:            Bobospay
 * Author URI:        https://https://bobospay.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bobospay-give
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BOBOSPAY_GIVE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bobospay-give-activator.php
 */
function activate_bobospay_give() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bobospay-give-activator.php';
	Bobospay_Give_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bobospay-give-deactivator.php
 */
function deactivate_bobospay_give() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bobospay-give-deactivator.php';
	Bobospay_Give_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bobospay_give' );
register_deactivation_hook( __FILE__, 'deactivate_bobospay_give' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bobospay-give.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bobospay_give() {

	$plugin = new Bobospay_Give();
	$plugin->run();

}
run_bobospay_give();
