<?php
/**
 * Bootstrapper for the plugin WooCommerce Memberships Grant Access Plus.
 *
 * @since {{VERSION}}
 *
 * @package WCM_GAP
 */

defined( 'ABSPATH' ) || die();

/**
 * Class WCM_GAP_Bootstrapper
 *
 * Bootstrapper for the plugin WooCommerce Memberships Grant Access Plus.
 *
 * @since {{VERSION}}
 *
 * @package WCM_GAP
 */
class WCM_GAP_Bootstrapper {

	/**
	 * Notices to show if cannot load.
	 *
	 * @since {{VERSION}}
	 * @access private
	 *
	 * @var array
	 */
	private $notices = array();

	/**
	 * WCM_GAP_Bootstrapper constructor.
	 *
	 * @since {{VERSION}}
	 */
	function __construct() {

		add_action( 'plugins_loaded', array( $this, 'maybe_load' ), 20 );
	}

	/**
	 * Maybe loads the plugin.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function maybe_load() {

		$php_version = phpversion();
		$wp_version  = get_bloginfo( 'version' );

		// Minimum PHP version
		if ( version_compare( $php_version, '5.3.0' ) === - 1 ) {

			$this->notices[] = sprintf(
				__( 'Minimum PHP version of 5.3.0 required. Current version is %s. Please contact your system administrator to upgrade PHP to its latest version.', 'woocommerce-memberships-grant-access-plus' ),
				$php_version
			);
		}

		// Minimum WordPress version
		if ( version_compare( $wp_version, '4.0.0' ) === - 1 ) {

			$this->notices[] = sprintf(
				__( 'Minimum WordPress version of 4.0.0 required. Current version is %s. Please contact your system administrator to upgrade WordPress to its latest version.', 'woocommerce-memberships-grant-access-plus' ),
				$wp_version
			);
		}

		// Required plugin
        if ( !class_exists('WC_Memberships')) {
	        $this->notices[] = sprintf(
		        __( 'The plugin WooCommerce Memberships is required.', 'woocommerce-memberships-grant-access-plus' ),
		        $wp_version
	        );
        }

		// Don't load and show errors if incompatible environment.
		if ( ! empty( $this->notices ) ) {

			add_action( 'admin_notices', array( $this, 'notices' ) );

			return;
		}

		$this->load();
	}

	/**
	 * Loads the plugin.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	private function load() {

		WCM_GAP();
	}

	/**
	 * Shows notices on failure to load.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function notices() {
		?>
		<div class="notice error">
			<p>
				<?php
				printf(
					__( '%sWooCommerce Memberships Grant Access Plus%s could not load because of the following errors:', 'wcm-gap' ),
					'<strong>',
					'</strong>'
				);
				?>
			</p>

			<ul>
				<?php foreach ( $this->notices as $notice ) : ?>
					<li>
						<?php echo $notice; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

/**
 * Gets the main plugin file instance.
 *
 * @since {{VERSION}}
 *
 * @return WCM_GAP
 */
function WCM_GAP() {

	return WCM_GAP::instance();
}