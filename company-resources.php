<?php
/*
 * Plugin Name:       Company Resources
 * Author:            Daniel Martinez
* Text Domain:        company-resources-plugin
 * Plugin URI:        https://danmtzbandera.com
 * Description:       Manage Company Resources.
 * Version:           1.0.0
 * Author URI:        https://danmtzbandera.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_path = plugin_dir_path( __FILE__ );

require_once $plugin_path . 'includes/post-types.php';
require_once $plugin_path . 'includes/taxonomies.php';
require_once $plugin_path . 'includes/actions-filters.php';
require_once $plugin_path . 'includes/custom-fields.php';
require_once $plugin_path . 'blocks/simple-cta-block/simple-cta-block.php';

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'company-resources-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Make sure rewrite rules are ready right after activation / cleaned on deactivation.
register_activation_hook( __FILE__, function () {
	create_company_resources_post_type();
	register_company_resources_taxonomy();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );