<?php
/**
 * Plugin Name: Frontend Plugin
 * Description: A plugin to handle frontend form submissions.
 * Version: 1.0.0
 * Author: Vishal
 * License: GPL2
 * Text Domain: frontend-plugin
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * 
 * @package FrontendPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin activation callback.
 */
function fp_activate_plugin() {
	require_once FP_PLUGIN_DIR . 'includes/class-fp-register-hook.php';
	FP_Register_Hook::get_instance()->activate();
}

register_activation_hook( __FILE__, 'fp_activate_plugin' );

// Load frontend logic.
require_once FP_PLUGIN_DIR . 'frontend/class-fp-frontend.php';

FP_Frontend::get_instance();
