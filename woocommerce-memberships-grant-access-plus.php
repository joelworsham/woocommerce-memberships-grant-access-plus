<?php
/**
 * Plugin Name: WooCommerce Memberships Grant Access Plus
 * Description: Improves the Grant Access import feature to handle large loads.
 * Version 1.0.0
 * Author: Joel Worsham
 * Author URI: http://joelworsham.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-memberships-grant-access-plus
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'WCM_GAP' ) ) {

	define( 'WCM_GAP_VERSION', '1.0.0' );
	define( 'WCM_GAP_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WCM_GAP_URI', plugins_url( '', __FILE__ ) );

	/**
	 * Class WCM_GAP
	 *
	 * The main plugin class for WooCommerce Memberships Grant Access Plus.
	 *
	 * @since {{VERSION}}
	 * @access public
	 *
	 * @package WCM_GAP
	 */
	final class WCM_GAP {

		protected function __clone() {
		}

		protected function __wakeup() {
		}

		/**
		 * Call this method to get singleton
		 *
		 * @since 1.0.0
		 *
		 * @return WCM_GAP()
		 */
		public static function instance() {

			static $instance = null;

			if ( $instance === null ) {

				$instance = new WCM_GAP();
			}

			return $instance;
		}

		/**
		 * WCM_GAP constructor.
		 *
		 * @since {{VERSION}}
		 * @access private
		 */
		private function __construct() {

			$this->includes();

			add_action( 'init', array( $this, 'register_assets' ) );
		}

		/**
		 * Includes all required files.
		 *
		 * @since {{VERSION}}
		 * @access private
		 */
		private function includes() {

			require_once WCM_GAP_DIR . 'core/class-wcm-gap-tool.php';
			new WCM_GAP_Tool();
		}

		/**
		 * Registers all plugin assets.
		 *
		 * @since {{VERSION}}
		 * @access private
		 */
		function register_assets() {

			// Admin
			wp_register_style(
				'wcm-gap',
				WCM_GAP_URI . '/assets/dist/css/wcm-gap.min.css',
				array(),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : WCM_GAP_VERSION
			);

			wp_register_script(
				'wcm-gap',
				WCM_GAP_URI . '/assets/dist/js/wcm-gap.min.js',
				array(),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : WCM_GAP_VERSION,
				true
			);

			wp_localize_script( 'wcm-gap', 'WCM_GAP', array(
				'l10n'        => array(
					'confirmGrantAccess' => __( "This action creates a membership for users who have previously purchased one of the products that grants access to the plan. If the user already has access to this plan, the original membership status and dates are preserved.\r\n\r\nSubscriptions: Only active subscribers will gain a membership.", 'woocommerce-memberships-grant-access-plus' )
				),
				'totalOrders' => WCM_GAP_Tool::get_total_orders(),
				'post'        => isset( $_GET['post'] ) ? $_GET['post'] : 0,
			) );
		}
	}

	// Bootstrap the plugin
	require_once WCM_GAP_DIR . 'woocommerce-memberships-grant-access-plus-bootstrapper.php';
	new WCM_GAP_Bootstrapper();
}