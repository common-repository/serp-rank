<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Serp_rank
 *
 * @wordpress-plugin
 * Plugin Name:       Keyword Rank Tracker
 * Plugin URI:        https://sichtbar.ag/keyword-rank-tracker-wordpress-plugin
 * Description:       Keyword Rank Tracker
 * Version:           1.1.1
 * Author:            Sichtbar
 * Author URI:        http://sichtbar.ag/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       serp-rank
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
define( 'SERP_RANK_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-serp-rank-activator.php
 */
function activate_serp_rank() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-serp-rank-activator.php';
	Serp_rank_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-serp-rank-deactivator.php
 */
function deactivate_serp_rank() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-serp-rank-deactivator.php';
	Serp_rank_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_serp_rank' );
register_deactivation_hook( __FILE__, 'deactivate_serp_rank' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-serp-rank.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_serp_rank() {

	$plugin = new Serp_rank();
	$plugin->run();

}
run_serp_rank();
