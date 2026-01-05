<?php
/**
 * Plugin activation logic.
 * 
 * @package FrontendPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FP_Register_Hook' ) ) {

	class FP_Register_Hook {

		/**
		 * Instance.
		 *
		 * @var FP_Register_Hook
		 */
		private static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return FP_Register_Hook
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Plugin activation.
		 */
		public function activate() {
			global $wpdb;

			$table_name      = $wpdb->prefix . 'custom_form_entries';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$table_name} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				email varchar(100) NOT NULL,
				roles longtext NOT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) {$charset_collate};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			error_log( 'Frontend Plugin activated: table created/updated.' );
		}
	}
}
