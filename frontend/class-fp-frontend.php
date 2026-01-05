<?php
/**
 * Frontend functionality.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FP_Frontend' ) ) {

	class FP_Frontend {

		/**
		 * Instance.
		 *
		 * @var FP_Frontend
		 */
		private static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return FP_Frontend
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_shortcode( 'custom_frontend_form', array( $this, 'render_form' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			add_action( 'wp_ajax_fp_submit_form', array( $this, 'handle_form_submit' ) );
			add_action( 'wp_ajax_nopriv_fp_submit_form', array( $this, 'handle_form_submit' ) );

			add_action( 'wp_ajax_fp_load_roles', array( $this, 'load_roles' ) );
			add_action( 'wp_ajax_nopriv_fp_load_roles', array( $this, 'load_roles' ) );
		}

		/**
		 * Render frontend form.
		 *
		 * @return string
		 */
		public function render_form() {
			$nonce = wp_create_nonce( 'fp_form_nonce' );

			ob_start();
			?>
			<form id="fp-frontend-form">
				<p>
					<label for="fp-name"><?php esc_html_e( 'Name', 'frontend-plugin' ); ?></label>
					<input type="text" name="name" id="fp-name" required>
				</p>

				<p>
					<label for="fp-email"><?php esc_html_e( 'Email', 'frontend-plugin' ); ?></label>
					<input type="email" name="email" id="fp-email" required>
				</p>

				<p>
					<label for="fp-roles"><?php esc_html_e( 'Select Roles', 'frontend-plugin' ); ?></label>
					<select name="roles[]" id="fp-roles" multiple></select>
				</p>

				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">

				<button type="submit">
					<?php esc_html_e( 'Submit', 'frontend-plugin' ); ?>
				</button>
			</form>

			<div id="fp-message"></div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Enqueue frontend assets.
		 */
        public function enqueue_assets() {

	        // Frontend CSS.
	            wp_enqueue_style(
		            'fp-frontend-css',
		            plugin_dir_url( __FILE__ ) . 'css/fp-frontend.css',
		            array(),
		            '1.0'
	            );

	// Select2 CSS.
	            wp_enqueue_style(
		            'select2-css',
		            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
		            array(),
		            null
	            );

	            // Select2 JS.
	            wp_enqueue_script(
		            'select2',
		            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
		            array( 'jquery' ),
		            null,
		            true
	            );

	        // SweetAlert2 (IMPORTANT).
	            wp_enqueue_script(
		            'sweetalert2',
		            'https://cdn.jsdelivr.net/npm/sweetalert2@11',
		            array(),
		            null,
		            true
	            );

	            // Frontend JS (DEPENDENCIES MATTER).
	            wp_enqueue_script(
		            'fp-frontend-js',
		            plugin_dir_url( __FILE__ ) . 'js/fp-frontend.js',
		            array( 'jquery', 'select2', 'sweetalert2' ),
		            '1.0',
		            true
	            );

	            // Localize script.
	            wp_localize_script(
		            'fp-frontend-js',
		            'fpAjax',
		            array(
			            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			            'nonce'   => wp_create_nonce( 'fp_ajax_nonce' ),
		            )
	            );
}

		/**
		 * Load roles via AJAX.
		 */
		public function load_roles() {
			check_ajax_referer( 'fp_ajax_nonce', 'nonce' );

			$file = plugin_dir_path( __FILE__ ) . 'data.json';

			if ( ! file_exists( $file ) ) {
				wp_send_json( array() );
			}

			$data = json_decode( file_get_contents( $file ), true );

			if ( ! is_array( $data ) ) {
				wp_send_json( array() );
			}

			$results = array();

			foreach ( $data as $item ) {
				$results[] = array(
					'id'   => sanitize_text_field( $item['id'] ),
					'text' => sanitize_text_field( $item['text'] ),
				);
			}

			wp_send_json( $results );
		}

		/**
		 * Handle form submission.
		 */
		public function handle_form_submit() {
			check_ajax_referer( 'fp_form_nonce', 'nonce' );

			$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
			$roles = array_map( 'sanitize_text_field', (array) ( $_POST['roles'] ?? array() ) );

			if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
				wp_send_json_error( __( 'Invalid input.', 'frontend-plugin' ) );
			}

			global $wpdb;

			$inserted = $wpdb->insert(
				$wpdb->prefix . 'custom_form_entries',
				array(
					'name'  => $name,
					'email' => $email,
					'roles' => maybe_serialize( $roles ),
				),
				array( '%s', '%s', '%s' )
			);

			if ( $inserted ) {
				wp_send_json_success( __( 'Form submitted successfully.', 'frontend-plugin' ) );
			}

			wp_send_json_error( __( 'Database error.', 'frontend-plugin' ) );
		}
	}
}
