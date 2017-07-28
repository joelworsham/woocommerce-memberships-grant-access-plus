<?php
/**
 * The primary tool for the plugin.
 *
 * @since {{VERSION}}
 */

defined( 'ABSPATH' ) || die();

class WCM_GAP_Tool {

	/**
	 * WCM_GAP_Tool constructor.
	 *
	 * @since {{VERSION}}
	 */
	function __construct() {

		add_action( 'current_screen', array( $this, 'screen_actions' ) );
		add_action( 'wp_ajax_wcm_gap_grant_access_run', array( $this, 'ajax_batch_run' ) );
	}

	/**
	 * Fires actions only a given screen.
	 *
	 * @since {{VERSION}}
	 * @access private
	 *
	 * @param WP_Screen $screen
	 */
	function screen_actions( $screen ) {

		if ( $screen->id === 'wc_membership_plan' ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
			add_action( 'admin_footer', array( $this, 'output_modal' ) );
		}
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function enqueue_scripts() {

		wp_enqueue_style( 'wcm-gap' );
		wp_enqueue_script( 'wcm-gap' );
	}

	/**
	 * Runs a batch import.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function ajax_batch_run() {

		$limit  = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : 100;
		$offset = isset( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;

		require_once WCM_GAP_DIR . 'core/wcm-gap-mock.php';

		$grant_count = wcm_gap_mock_grant_access_to_membership( $limit, $offset );

		wp_send_json_success( array(
			'grant_count' => $grant_count,
		) );
	}

	/**
	 * Retrieves number of rows in orders table to know how long this will take.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	public static function get_total_orders() {

		global $wpdb;

		$count = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}woocommerce_order_items" );

		return $count;
	}

	/**
	 * Outputs the modal HTML.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function output_modal() {

		$redirect_to = get_edit_post_link( $_REQUEST['post'], 'redirect' );

		include_once WCM_GAP_DIR . 'core/views/loading-modal.php';
	}
}